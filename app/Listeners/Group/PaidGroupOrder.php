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

namespace App\Listeners\Group;

use App\Events\Group\PaidGroup;
use App\Models\GroupPaidUser;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\GroupUserMq;
use App\Notifications\Messages\Database\GroupUpgradeMessage;
use App\Notifications\System;
use Illuminate\Support\Carbon;

/**
 * Class PaidGroupOrder
 * @package App\Listeners\Group
 * 成功购买用户组事件
 */
class PaidGroupOrder
{
    public function handle(PaidGroup $event)
    {
        if (isset($event->group_id)) {
            $user_group_ids = $event->user->groups()->pluck('id')->all();
            $group_info = Group::findOrFail($event->group_id);
            $db = app('db');
            $log = app('log');
            $db->beginTransaction();
            //已有用户组
            $group_paid_user_info = GroupPaidUser::query()->where('user_id', $event->user->id)
                ->where('delete_type', 0)->first();
            if (isset($group_paid_user_info->expiration_time)) {
                if (!empty($event->operator->id)) {
                    //管理员操作时重新设置过期时间不变
                    $delete_type = GroupPaidUser::DELETE_TYPE_ADMIN;
                } else {
                    //其他情况，到期时间往后顺延
                    $delete_type = GroupPaidUser::DELETE_TYPE_RENEW;
                }
                //软删除原记录
                $group_paid_user_info->update(['delete_type' => $delete_type]);
                $res = $group_paid_user_info->delete();
                if ($res === false) {
                    $db->rollBack();
                    $log->info('软删除 group_paid_user 记录出错', [$event]);
                    return;
                }
            }

            //以前的设计用户--用户组是 一对多 的关系，但是目前业务流程使用的是  一对一，所以这里暂且按照一个用户对应一个二维数组来处理。只有当站长开启付费时， checkoutsite 会判断用户是否过期来考虑是否增加用户未付费用户组身份
            //下面的判断可以理解为：用户续费当前用户组身份
            if (in_array($event->group_id, $user_group_ids)) {
                if (isset($group_paid_user_info->expiration_time)) {
                    $expiration_time = Carbon::parse($group_paid_user_info->expiration_time)->addDays($group_info->days);
                } else {
                    $expiration_time = Carbon::now()->addDays($group_info->days);
                }
                $group_paid_user = GroupPaidUser::creation(
                    $event->user->id,
                    $group_info->id,
                    $expiration_time,
                    isset($event->order->id) ? $event->order->id : 0,
                    isset($event->operator->id) ? $event->operator->id : null
                );
                //添加新记录
                $res = $group_paid_user->save();
                if ($res === false) {
                    $db->rollBack();
                    $log->info('新增 group_paid_user 记录出错', [$event]);
                    return;
                }
                //针对付费站点有到期时间的概念，增加 users 的 expired_at
                if ($event->user->expired_at > Carbon::now()) {
                    $event->user->expired_at = Carbon::parse($event->user->expired_at)->addDays($group_info->days);
                } else {
                    $event->user->expired_at = Carbon::now()->addDays($group_info->days);
                }
                $res = $event->user->save();
                if ($res === false) {
                    $db->rollBack();
                    $log->info('修改 users 的 expired_at 记录出错', [$event]);
                    return;
                }
                //修改用户当前 group_user 中的 expiration_time 记录
                $group_user = GroupUser::query()->where(['group_id' => $event->group_id, 'user_id' => $event->user->id])->first();
                $expiration_time = Carbon::parse($group_user->expiration_time)->addDays($group_info->days);
                $res = GroupUser::query()->where(['user_id' => $event->user->id, 'group_id' => $event->group_id])->update(['expiration_time' => $expiration_time]);
                if ($res === false) {
                    $db->rollBack();
                    $log->info('修改 group_user 的 expiration_time 记录出错', [$event]);
                    return;
                }
            } else {
                //对于没有 group_user 记录的情况有两种：1、完全全新的用户；2、在已有用户组的情况下购买其他用户组的情况
                $remain_days = $group_info->days;
                //增加 users 中 expired_at 的时间
                if ($event->user->expired_at > Carbon::now()) {
                    $event->user->expired_at = Carbon::parse($event->user->expired_at)->addDays($remain_days);
                } else {
                    $event->user->expired_at = Carbon::now()->addDays($remain_days);
                }
                $res = $event->user->save();
                if ($res === false) {
                    $db->rollBack();
                    $log->error('修改 users 的 expired_at 记录出错', [$event]);
                    return;
                }
                //1、先查 group_user_mqs ，看用户是否有对应用户组id记录，有的话还需要取出来，把剩余时间累加上
                $group_user_mqs = GroupUserMq::query()->where(['user_id' => $event->user->id, 'group_id' => $group_info->id])->first();
                if (!empty($group_user_mqs)) {
                    $remain_days += $group_user_mqs->remain_days;
                }
                //如果用户当前身份不是所购买的用户组、免费用户组
                $pay_group_ids = Group::query()->where('is_paid', Group::IS_PAID)->pluck('id')->toArray();
                $old_group_user = GroupUser::query()->where('user_id', $event->user->id)
                    ->where('group_id', '!=', $group_info->id)->whereIn('group_id', $pay_group_ids)->first();
                if (!empty($old_group_user)) {
                    //计算old用户组还剩多久，迁移到 group_user_mqs
                    $old_remain_days = Carbon::parse(Carbon::now())->diffInDays($old_group_user->expiration_time, false);
                    $group_user_mqs = new GroupUserMq();
                    $group_user_mqs->group_id = $old_group_user->group_id;
                    $group_user_mqs->user_id = $event->user->id;
                    $group_user_mqs->remain_days = $old_remain_days;
                    $res = $group_user_mqs->save();
                    if ($res === false) {
                        $db->rollBack();
                        $log->error('新增 group_user_mqs 记录出错', [$event]);
                        return;
                    }
                }
                //先删除老的 group_user
                $res = GroupUser::query()->where('user_id', $event->user->id)->delete();
                if ($res === false) {
                    $db->rollBack();
                    $log->error('删除 老数据 group_user 记录出错', [$event]);
                    return;
                }
                $expiration_time = Carbon::now()->addDays($remain_days);
                $event->user->groups()->attach($group_info->id, ['expiration_time' => $expiration_time]);
                $group_paid_user = GroupPaidUser::creation(
                    $event->user->id,
                    $group_info->id,
                    $expiration_time,
                    isset($event->order->id) ? $event->order->id : 0,
                    isset($event->operator->id) ? $event->operator->id : null
                );
                $res = $group_paid_user->save();
                if ($res === false) {
                    $db->rollBack();
                    $log->error('新增 group_paid_user 记录出错', [$event]);
                    return;
                }
            }
            $db->commit();
            //发送通知
            $notifyData = [
                'new_group'     =>  $group_info->name
            ];
            $event->user->notify(new System(GroupUpgradeMessage::class, $event->user, $notifyData));
        }
    }
}
