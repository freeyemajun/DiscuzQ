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

namespace App\Api\Controller\Users;

use App\Common\ResponseCode;
use App\Models\Dialog;
use App\Models\Group;
use App\Models\Order;
use App\Models\Thread;
use App\Models\User;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Discuz\Base\DzqController;

class ProfileController extends DzqController
{
    use ProfileTrait;

    public $providers = [
        \App\Providers\UserServiceProvider::class,
    ];

    public $optionalInclude = ['groups', 'dialog'];

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        return true;
    }

    public function main()
    {
        $user_id = $this->inPut('userId');
        $user = User::find($user_id);
        if (!$user) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }
        $isSelf = $this->user->id === $user->id;
        if ($isSelf || $this->user->isAdmin()) {
            $this->optionalInclude = array_merge($this->optionalInclude, ['wechat']);
        }
        $include = !empty($this->inPut('include'))
            ? array_unique(array_merge($this->include, explode(',', $this->inPut('include'))))
            : $this->include;

        if (!empty($this->inPut('include'))) {
            if (array_diff($this->inPut('include'), $this->optionalInclude)) {
                //如果include 超出optionalinclude 就报错
                $this->outPut(ResponseCode::NET_ERROR);
            }
        }

        // 付费模式是否过期 true：已付费，false：未付费
        $userRepo = app(UserRepository::class);
        $user->paid = $userRepo->isPaid($user);

        //isRenew：表示用户是否续过费，true：已续过费，false：未续过费
        $user->isRenew = false;
        $order = Order::query()->where([
            'status' => Order::ORDER_STATUS_PAID,
            'user_id' => $user->id
        ])  ->whereIn('type', [Order::ORDER_TYPE_REGISTER, Order::ORDER_TYPE_RENEW])
            ->exists();
        if (!empty($order)) {
            $user->isRenew = true;
        }

        $key = array_search('dialog', $include);
        if ($key != false) {
            if (!$isSelf) {
                $actor = $this->user;
                //添加会话关系
                $dialog = Dialog::query()
                    ->where(['sender_user_id' => $actor->id, 'recipient_user_id' => $user->id])
                    ->orWhere(function ($query) use ($actor, $user) {
                        $query->where(['sender_user_id' => $user->id, 'recipient_user_id' => $actor->id]);
                    })
                    ->first();
                $user->setRelation('dialog', $dialog);
            } else {
                unset($include[$key]);
            }
        }
        $user->loadMissing($include);

        // 判断用户是否禁用
        if ($user->status == User::enumStatus('ban')) {
            $user->load(['latelyLog' => function ($query) {
                $query->select()->where('action', 'ban');
            }]);
        }

        //如果是当前用户，计算出审核中和已忽略的数量
        if ($user_id == $this->user->id) {
            $user->thread_count = Thread::query()
                ->where('user_id', $user_id)
                ->whereNull('deleted_at')
                ->where('is_draft', Thread::IS_NOT_DRAFT)
                ->count();
        } else {
            //查看他人信息主题数需排除匿名贴,草稿，且需已审核的帖子
            $user->thread_count = Thread::query()
                ->whereNull('deleted_at')
                ->whereNotNull('user_id')
                ->where('user_id', $user_id)
                ->where('is_draft', Thread::IS_NOT_DRAFT)
                ->where('is_approved', Thread::BOOL_YES)
                ->where('is_anonymous', Thread::IS_NOT_ANONYMOUS)->count();
        }

        $data = $this->getData($user);

        $data['wxNickname'] = !empty($user->wechat) ? $user->wechat->nickname : '';
        $data['wxHeadImgUrl'] = !empty($user->wechat) ? $user->wechat->headimgurl : '';
        $data['expiredDays'] = false;
        if (!empty($data['expiredAt'])) {
            $dateDiff = date_diff(date_create($data['expiredAt']), date_create(date('Y-m-d H:i:s')));
            $data['expiredDays'] = $dateDiff->days;
        }
        $this->outPut(ResponseCode::SUCCESS, '', $data);
    }

    private function getGroupInfo($group)
    {
        //判断用户当前所在用户组是否是最高级别
        $level = $group['groups']['level'];
        $is_top = Group::query()->where('level', '>', $level)->doesntExist();
        $hasPayGroup = Group::query()->where('level', '>', 0)->exists();
        // 计算剩余时间和展示类型
        $type_time = 0;
        $remain_time = 0;
        if (!empty($group['expiration_time'])) {      //天
            $remain_time = Carbon::parse($group['expiration_time'])->diffInDays(Carbon::now());
            if (empty($remain_time)) {                //时
                $type_time = 1;
                $remain_time = Carbon::parse($group['expiration_time'])->diffInHours(Carbon::now());
                if (empty($remain_time)) {            //分
                    $type_time = 2;
                    $remain_time = Carbon::parse($group['expiration_time'])->diffInMinutes(Carbon::now());
                }
            }
        }


        return [
            'pid' => $group['group_id'],
            'groupId' => $group['group_id'],
            'groupName' => $group['groups']['name'],
            'isTop' =>  $is_top,
            'expirationTime'    =>  $group['expiration_time'],
            'color' =>  $group['groups']['color'],
            'amount'   =>  (double)$group['groups']['fee'],
            'level' =>  $group['groups']['level'],
            'remainDays'    =>  $remain_time,
            'remainTime' => $remain_time,
            'typeTime' => $type_time,
            'description' => $group['groups']['description'],
            'hasPayGroup' => $hasPayGroup
        ];
    }
}
