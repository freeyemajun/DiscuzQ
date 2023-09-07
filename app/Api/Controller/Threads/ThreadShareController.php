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
use App\Models\Thread;
use App\Repositories\UserRepository;
use Discuz\Base\DzqCache;
use Discuz\Base\DzqController;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class ThreadShareController extends DzqController
{
    /**
     * @var Thread
     */
    protected $thread;

    public function prefixClearCache($user)
    {
        $threadId = $this->inPut('threadId');
        DzqCache::delHashKey(CacheKey::LIST_THREADS_V3_THREADS, $threadId);
    }

    public function checkRequestPermissions(UserRepository $userRepo)
    {
        $this->thread = Thread::getOneActiveThread($this->inPut('threadId'));
        if (!$this->thread) {
            $this->outPut(ResponseCode::RESOURCE_IS_REVIEW, '审核中的帖子不可分享');
            throw new NotFoundResourceException();
        }

        return $userRepo->canViewThreads($this->user, $this->thread->category_id)
            || $userRepo->canViewThreadDetail($this->user, $this->thread);
    }

    public function main()
    {
        $this->thread->increment('share_count');
        $this->outPut(ResponseCode::SUCCESS, '');
    }
}
