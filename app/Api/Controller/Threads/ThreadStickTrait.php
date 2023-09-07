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

namespace App\Api\Controller\Threads;

use App\Common\Platform;
use App\Common\ResponseCode;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Setting;
use App\Models\Thread;
use App\Models\ThreadStickSort;
use Discuz\Common\Utils;

trait ThreadStickTrait
{
    public function getData(): array
    {
        $effectiveStickThread = $this->getStickThread(false);

        $stickThreadList = $effectiveStickThread;

        $lackThreadNum = $this->getLackThreadNum($effectiveStickThread);

        if ($lackThreadNum > 0) {
            $lackStickThread = $this->getStickThread(true, $lackThreadNum);

            $stickThreadList = collect()
                ->merge($effectiveStickThread)
                ->merge($lackStickThread);
        }

        $permissions = Permission::getUserPermissions($this->user);

        $data = [];
        collect($stickThreadList)->map(function ($thread) use ($permissions, &$data) {
            $title = $thread->getContentByType(Thread::CONTENT_LENGTH, true);
            $threadId = $thread->thread_id;
            $categoryId = $thread->category_id;
            $updatedAt = $thread->updated_at;
            $sort = $thread->sort;

            $resultData = [
                'threadId' => $threadId,
                'categoryId' => $categoryId,
                'title' => $title,
                'updatedAt' => date('Y-m-d H:i:s', strtotime($updatedAt)),
                'canViewPosts' => $this->canViewPosts($thread, $permissions),
                'sort' => $sort
            ];
            array_push($data, $resultData);
        });

        return $data;
    }

    private function canViewPosts($thread, $permissions): bool
    {
        if ($this->user->isAdmin() || $this->user->id == $thread['user_id']) {
            return true;
        }
        $viewPostStr = 'category' . $thread['category_id'] . '.thread.viewPosts';
        if (in_array('thread.viewPosts', $permissions) || in_array($viewPostStr, $permissions)) {
            return true;
        }
        return false;
    }

    public function getStickThreadsBuild($thread)
    {
        $thread = $thread
            ->where('is_sticky', Thread::BOOL_YES)
            ->whereNull('deleted_at')
            ->whereNotNull('user_id')
            ->where('is_draft', Thread::BOOL_NO)
            ->where('is_approved', Thread::BOOL_YES);
        if (Utils::requestFrom() == Platform::MinProgram) {
            $thread->where('is_display', Thread::BOOL_YES);
        }
        return $thread;
    }

    public function getStickThread($isLack = false, $lackThreadNum = 0)
    {
        $categoryIds = $this->inPut('categoryIds');

        if (!empty($categoryIds) && !is_array($categoryIds)) {
            $categoryIds = [$categoryIds];
        }
        $categoryIds = Category::instance()->getValidCategoryIds($this->user, $categoryIds);
        if (!$categoryIds) {
            $this->outPut(ResponseCode::SUCCESS, '', []);
        }

        $threadStickSort = ThreadStickSort::query()->select('thread_id', 'sort')->get()->toArray();

        $threadStickIds = [];
        foreach ($threadStickSort as $value) {
            array_push($threadStickIds, $value['thread_id']);
        }

        $threads = Thread::query()
            ->select([
                'threads.id as id',
                'threads.id as thread_id',
                'threads.category_id',
                'threads.title',
                'threads.updated_at',
                'threads.user_id',
                'threads.type',

                'thread_stick_sort.sort'
            ])
            ->leftJoin('thread_stick_sort', 'threads.id', '=', 'thread_stick_sort.thread_id');

        if ($isLack == false) {
            $threads = $threads
                ->orderBy('thread_stick_sort.sort', 'asc')
                ->orderBy('threads.updated_at', 'desc')
                ->whereIn('threads.id', $threadStickIds);
        } else {
            $threads = $threads
                ->orderBy('threads.updated_at', 'desc')
                ->whereNotIn('threads.id', $threadStickIds)
                ->limit($lackThreadNum);
        }

        $isMiniProgramVideoOn = Setting::isMiniProgramVideoOn();
        if (!$isMiniProgramVideoOn) {
            $threads = $threads->where('type', '<>', Thread::TYPE_OF_VIDEO);
        }

        $threads = $threads->whereIn('category_id', $categoryIds);
        $threads = $this->getStickThreadsBuild($threads)->get();

        return $threads;
    }

    public function getLackThreadNum($effectiveStickThread = []): int
    {
        if (empty($effectiveStickThread)) {
            $effectiveStickThread = $this->getStickThread(false);
        }

        return ThreadStickSort::THREAD_STICK_COUNT_LIMIT - count($effectiveStickThread);
    }

    public function isAllowSetThreadStick(): bool
    {
        $lackThreadNum = $this->getLackThreadNum();
        if ($lackThreadNum > 0) {
            return true;
        }
        return false;
    }

    public function updateOrCreateThreadStick($threadId)
    {
        $exists = ThreadStickSort::query()->where('thread_id', '=', $threadId)->exists();
        if ($exists == true) {
            ThreadStickSort::updateThreadStick($threadId);
        } else {
            ThreadStickSort::createThreadStick($threadId);
        }
    }
}
