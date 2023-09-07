<?php

/**
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Commands\Thread;

use App\Api\Controller\Threads\ThreadStickTrait;
use App\Events\Thread\Saving;
use App\Events\Thread\ThreadWasApproved;
use App\Models\Thread;
use App\Models\ThreadStickSort;
use App\Models\User;
use App\Models\Category;
use App\Models\AdminActionLog;
use App\Repositories\SequenceRepository;
use App\Repositories\ThreadRepository;
use App\Traits\ThreadNoticesTrait;
use Discuz\Foundation\EventsDispatchTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;

class AdminBatchEditThreads
{
    use EventsDispatchTrait;

    use ThreadNoticesTrait;

    use ThreadStickTrait;

    /**
     * The user performing the action.
     *
     * @var User
     */
    public $actor;

    /**
     * The attributes to update on the threads.
     *
     * @var array
     */
    public $data;

    /**
     * @param User $actor
     * @param array $data
     */
    public function __construct(User $actor, array $data)
    {
        $this->actor = $actor;
        $this->data = $data;
    }

    /**
     * @param Dispatcher $events
     * @param ThreadRepository $threads
     * @return object
     */
    public function handle(Dispatcher $events, ThreadRepository $threads)
    {
        $this->events = $events;

        $result = ['data' => [], 'meta' => []];
        $titles = [];
        foreach ($this->data as $data) {
            if (isset($data['id'])) {
                $id = $data['id'];
            } else {
                continue;
            }

            /** @var Thread $thread */
            $thread = $threads->query()->find($id);

            if ($thread) {
                $thread->timestamps = false;
            } else {
                continue;
            }

            $attributes = Arr::get($data, 'attributes', []);
            $relationships = Arr::get($data, 'relationships', []);
            $category_id = '';
            if (!empty($relationships['category']['data']['id'])) {
                $category_id = $relationships['category']['data']['id'];
            }

            if ($thread->title !== '') {
                $titles[] = '【'. $thread->title .'】';
            } else {
                $titles[] = '【无标题，ID为'. $id .'】';
            }

            if (isset($attributes['isApproved']) && $attributes['isApproved'] < 3) {
                if ($thread->is_approved != $attributes['isApproved']) {
                    $thread->is_approved = $attributes['isApproved'];
                    $thread->raise(
                        new ThreadWasApproved($thread, $this->actor, ['message' => $attributes['message'] ?? ''])
                    );
                }
            }

            if (isset($attributes['isSticky'])) {
                if ($thread->is_sticky != $attributes['isSticky']) {
                    $thread->is_sticky = $attributes['isSticky'];
                    if ($thread->is_sticky) {
                        $this->updateOrCreateThreadStick($thread->id);
                        $this->threadNotices($thread, $this->actor, 'isSticky', $attributes['message'] ?? '');
                    } else {
                        ThreadStickSort::deleteThreadStick($thread->id);
                    }
                }
            }

            if (isset($attributes['isSite'])) {
                if ($thread->is_site != $attributes['isSite']) {
                    $thread->is_site = $attributes['isSite'];
                }
            }
            if (isset($attributes['isEssence'])) {
                if ($thread->is_essence != $attributes['isEssence']) {
                    $thread->is_essence = $attributes['isEssence'];

                    if ($thread->is_essence) {
                        $this->threadNotices($thread, $this->actor, 'isEssence', $attributes['message'] ?? '');
                    }
                }
            }

            if (isset($attributes['isDeleted'])) {
                $message = $attributes['message'] ?? '';
                if ($attributes['isDeleted']) {
                    $thread->hide($this->actor, ['message' => $message]);
                } else {
                    $thread->restore($this->actor, ['message' => $message]);
                }
            }

            try {
                $this->events->dispatch(
                    new Saving($thread, $this->actor, $data)
                );
            } catch (\Exception $e) {
                continue;
            }

            $thread->save();

            if (isset($attributes['isFavorite']) && $attributes['isFavorite'] == false) {
                app(SequenceRepository::class)->updateSequenceCache($id, 'edit');
            }

            $result['data'][] = $thread;

            try {
                $this->dispatchEventsFor($thread, $this->actor);
            } catch (\Exception $e) {
                continue;
            }
        }

        $titles = implode('、', $titles);
        $actionDesc = '';
        if (isset($attributes['isApproved'])) {
            if ($attributes['isApproved'] == Thread::APPROVED) {
                $actionDesc = '用户主题帖'. $titles .'通过审核';
            }
            if ($attributes['isApproved'] == Thread::UNAPPROVED) {
                $actionDesc = '用户主题帖'. $titles .'暂被设为非法';
            }
            if ($attributes['isApproved'] == Thread::IGNORED) {
                $actionDesc = '用户主题帖'. $titles .'被忽略';
            }
        }

        if (isset($attributes['isSticky'])) {
            if ($attributes['isSticky'] == true) {
                $actionDesc = '批量置顶用户主题帖'. $titles;
            } else {
                $actionDesc = '批量取消用户主题帖'. $titles .'的置顶';
            }
        }

        if (isset($attributes['isDeleted'])) {
            if ($attributes['isDeleted'] == true) {
                $actionDesc = '批量删除用户主题帖'. $titles;
            } else {
                $actionDesc = '批量还原用户主题帖'. $titles;
            }
        }

        if (isset($attributes['isEssence'])) {
            if ($attributes['isEssence'] == true) {
                $actionDesc = '批量设置用户主题帖'. $titles .'为精华';
            } else {
                $actionDesc = '批量取消用户主题帖'. $titles .'的精华标志';
            }
        }

        if (isset($attributes['isSite'])) {
            if ($thread->is_site == true) {
                $actionDesc = '批量推荐用户主题帖'. $titles .'至付费首页';
            } else {
                $actionDesc = '批量取消用户主题帖'. $titles .'的付费首页推荐';
            }
        }

        if ($actionDesc !== '' && !empty($actionDesc)) {
            AdminActionLog::createAdminActionLog(
                $this->actor->id,
                AdminActionLog::ACTION_OF_THREAD,
                $actionDesc
            );
        }

        if ($category_id !== '' && !empty($category_id)) {
            $categoryDetail = Category::query()->where('id', $category_id)->first();
            $actionDesc = '批量转移用户主题帖'. $titles .'至【'. $categoryDetail['name'] .'】分类';
            AdminActionLog::createAdminActionLog(
                $this->actor->id,
                AdminActionLog::ACTION_OF_THREAD,
                $actionDesc
            );
        }
        return $thread;
    }
}
