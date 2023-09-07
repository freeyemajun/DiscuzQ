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

use App\Common\CacheKey;
use App\Common\ResponseCode;
use App\Models\Group;
use App\Models\Post;
use App\Models\Thread;
use App\Models\ThreadTom;
use App\Models\ThreadUserStickRecord;
use App\Models\User;
use App\Repositories\UserRepository;
use Discuz\Base\DzqCache;
use Discuz\Base\DzqController;

class ThreadDetailController extends DzqController
{
    use ThreadTrait;

    protected $thread;

    public function prefixClearCache($user)
    {
        DzqCache::delHashKey(CacheKey::LIST_THREADS_V3_THREADS, $this->inPut('threadId'));
        DzqCache::delKey(CacheKey::LIST_THREADS_V3_VIEW_COUNT);
        DzqCache::delHashKey(CacheKey::LIST_THREADS_V3_POST_USERS, $this->inPut('threadId'));
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        $this->thread = Thread::query()
            ->where(['id' => $this->inPut('threadId')])
            ->first();
        if (empty($this->thread)) {
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND);
        }
        $hasPermission = $userRepo->canViewThreadDetail($this->user, $this->thread);
        if (! $hasPermission && $this->user->isGuest()) {
            $this->outPut(ResponseCode::JUMP_TO_LOGIN);
        }
        return $hasPermission;
    }

    public function main()
    {
        $threadId = $this->inPut('threadId');
        $thread = $this->thread;
        $post = Post::query()
            ->where(['thread_id' => $threadId, 'is_first' => Post::FIRST_YES])
            ->whereNull('deleted_at')
            ->first();
        if (!$thread || !$post) {
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND);
        }
        $user = User::query()->where('id', $thread['user_id'])->first();
        if (empty($user)) {
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND, '用户不存在');
        }

        if ($this->user->isGuest()) {
            $loginUserData = [];
        } elseif ($this->user->id == $thread['user_id']) {
            $loginUserData = $user;
        } else {
            $loginUserData = User::query()->where('id', $this->user->id)->first();
        }

        $group = Group::getGroup($user['id']);

        $tomInputIndexes = $this->getTomContent($thread);

        $userStickRecord = ThreadUserStickRecord::query()->where(['thread_id' => $threadId, 'user_id' => $user->id])->first();
        $userStickIds = $userStickRecord ? [$threadId] : [];

        $result = $this->packThreadDetail($user, $group, $thread, $post, $tomInputIndexes['tomContent'], true, $tomInputIndexes['tags'], $loginUserData, $userStickIds);
        $result['orderInfo'] = [];
        if (
            $this->needPay($tomInputIndexes['tomContent'])
            && ($order = $this->getOrderInfo($thread))
        ) {
            $result['orderInfo'] = $this->camelData($order);
        }

        $this->outPut(ResponseCode::SUCCESS, '', $result);
    }

    private function getTomContent($thread)
    {
        $threadId = $thread->id;
        $threadTom = ThreadTom::query()
            ->where([
                'thread_id' => $threadId,
                'status' => ThreadTom::STATUS_ACTIVE
            ])->orderBy('key')->get()->toArray();
        $tomContent = $tags = [];
        foreach ($threadTom as $item) {
            //如果是部分付费的话，将 price_ids 放进 body
            $tom_value = json_decode($item['value'], true);
            $priceIds = json_decode($item['price_ids'], true);
            if ($item['price_type'] && !empty($priceIds)) {
                $tom_value += ['priceIds' => $priceIds];
            }
            $tomContent[$item['key']] = $this->buildTomJson($threadId, $item['tom_type'], $this->SELECT_FUNC, $tom_value);
            $tags[$item['key']]['tag'] = $item['tom_type'];
        }
        return ['tomContent' => $tomContent, 'tags' => $tags];
    }
}
