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
use App\Models\ThreadUserStickRecord;
use App\Repositories\UserRepository;
use Discuz\Base\DzqCache;
use Discuz\Base\DzqController;
use Illuminate\Support\Carbon;

class ThreadUserStickController extends DzqController
{
    use ThreadTrait;

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        if ($this->user->isGuest()) {
            $this->outPut(ResponseCode::JUMP_TO_LOGIN);
        }


        $thread_id = $this->inPut('threadId');
        if (empty($thread_id)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }

        $user = $this->user;

        $threadRow = Thread::query()->where('id', $thread_id)->first();
        if (!empty($threadRow)) {
            if ($threadRow->user_id != $user->id) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '主题id' . $thread_id . '没权限操作');
            }
        }

        return true;
    }

    public function main()
    {
        $user = $this->user;
        $thread_id = $this->inPut('threadId');
        $status = $this->inPut('status'); //0 取消 1 置顶

        if (empty($thread_id)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }

        $threadRow = Thread::query()->where('id', $thread_id)->first();
        if (empty($threadRow)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '主题id' . $thread_id . '不存在');
        }

        $userStick = ThreadUserStickRecord::query()->where('user_id', '=', $user->id)->first();
        if (empty($userStick)) {
            if ($status == ThreadUserStickRecord::STATUS_NO) {
                //没有置顶的，不能取消
               // $this->outPut(ResponseCode::INVALID_PARAMETER, "没有置顶，不需要取消");
            } else {
                //插入数据库
                $userStickAdd = new ThreadUserStickRecord();
                $userStickAdd->user_id = $user->id;
                $userStickAdd->thread_id = $thread_id;
                $userStickAdd->status = ThreadUserStickRecord::STATUS_YES;
                $userStickAdd->save();
            }
        } else {
            if ($status == ThreadUserStickRecord::STATUS_NO) {
                if ($thread_id != $userStick->thread_id) {
                    $this->outPut(ResponseCode::INVALID_PARAMETER, '取消置顶与当前置顶的不是同一个');
                } else {
                    ThreadUserStickRecord::query()->where('user_id', '=', $user->id)->delete();
                }
            } else {
                if ($thread_id != $userStick->thread_id) {
                    ThreadUserStickRecord::query()->where('user_id', '=', $user->id)
                        ->update(['thread_id' => $thread_id,
                            'status' => ThreadUserStickRecord::STATUS_YES,
                            'updated_at' => Carbon::now()]);
                }
            }
        }

        $data=[
            'threadId' => $thread_id,
            'status' => $status==0?ThreadUserStickRecord::STATUS_NO:ThreadUserStickRecord::STATUS_YES
        ];

        $this->outPut(ResponseCode::SUCCESS, $status==0?'取消置顶成功':'置顶成功', $data);
    }

    public function suffixClearCache($user)
    {
        DzqCache::delKey(CacheKey::LIST_THREADS_V3_COMPLEX);
    }
}
