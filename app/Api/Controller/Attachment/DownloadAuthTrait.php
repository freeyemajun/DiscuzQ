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

namespace App\Api\Controller\Attachment;

use App\Common\ResponseCode;
use App\Models\Order;
use App\Models\Thread;

trait DownloadAuthTrait
{
    public function checkDownloadAttachment($thread, $user, $userRepo)
    {

        //如果帖子还在审核和草稿当中，只能当前用户下载
        if ($user->id !== $thread->user_id && ($thread->is_draft == Thread::IS_DRAFT || $thread->is_approved !== Thread::BOOL_YES)) {
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND);
        }
        //是否有帖子详情查看权限
        if (!$userRepo->canViewThreadDetail($user, $thread)) {
            $this->outPut(ResponseCode::UNAUTHORIZED);
        }
        //是否付费帖
        if ($thread->price > 0 || $thread->attachment_price > 0) {
            //免费查看付费帖权限
            if (!$userRepo->canFreeViewPosts($user, $thread)) {
                if ($this->user->isGuest()) {
                    $this->outPut(ResponseCode::UNAUTHORIZED);
                }

                $isPay = Order::query()
                    ->whereIn('type', [Order::ORDER_TYPE_THREAD, Order::ORDER_TYPE_ATTACHMENT])
                    ->where([ 'thread_id' => $thread->id, 'status' => Order::ORDER_STATUS_PAID])
                    ->where('user_id', $user->id)
                    ->exists();
                if (!$isPay) {
                    $this->outPut(ResponseCode::UNAUTHORIZED);
                }
            }
        }
    }
}
