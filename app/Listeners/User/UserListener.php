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

namespace App\Listeners\User;

use App\Common\Platform;
use App\Events\Users\AdminLogind;
use App\Events\Users\ChangeUserStatus;
use App\Events\Users\Forum;
use App\Events\Users\PayPasswordChanged;
use App\Events\Users\UserFollowCreated;
use App\Models\SessionToken;
use App\Models\SiteInfoDaily;
use App\Notifications\Messages\Database\StatusMessage;
use App\Notifications\System;
use Illuminate\Contracts\Events\Dispatcher;

class UserListener
{
    public function subscribe(Dispatcher $events)
    {
        // 刷新用户关注数粉丝数
        $events->listen(UserFollowCreated::class, [$this, 'refreshFollowCount']);

        // 通知
        $events->listen(ChangeUserStatus::class, [$this, 'notifications']);

        // 修改支付密码
        $events->listen(PayPasswordChanged::class, [$this, 'payPasswordChanged']);

//        $events->listen(AdminLogind::class, QcloudDaily::class);
//        $events->listen(AdminLogind::class, QcloudSiteInfoDaily::class);

        $events->listen(Forum::class, [$this, 'activeUsersStatistics']);
        $events->listen(Forum::class, [$this, 'startStatistics']);
    }

    public function refreshFollowCount(UserFollowCreated $event)
    {
        //关注人的 关注数
        $event->fromUser->refreshUserFollow();
        $event->fromUser->save();

        //被关注人的 粉丝数
        $event->toUser->refreshUserFans();
        $event->toUser->save();
    }

    /**
     * @param ChangeUserStatus $event
     */
    public function notifications(ChangeUserStatus $event)
    {
        $user = $event->user;

        // Tag 发送通知
        $user->notify(new System(StatusMessage::class, $user, ['refuse' => $event->refuse]));
    }

    public function payPasswordChanged(PayPasswordChanged $event)
    {
        // 修改支付密码后，清除用于修改支付密码的 session_token
        SessionToken::query()
            ->where('scope', 'reset_pay_password')
            ->where('user_id', $event->user->id)
            ->delete();
    }

    public function activeUsersStatistics(Forum $event)
    {
        $get_query_params = $event->request->getQueryParams();
        if (empty($get_query_params['dzqPf'])) {
            return;
        }
        $active_users = app('cache')->get('active_users');
        if (empty($active_users)) {
            $active_users = [];
        }
        //如果没有 user_id，就不统计活跃用户
        if ($event->user->id && !in_array($event->user->id, $active_users)) {
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $cache_time = strtotime($tomorrow) - time();

            $today = date('Y-m-d', time());
            $site_info_daily = SiteInfoDaily::query()->where('date', $today)->first();
            if (empty($site_info_daily)) {
                $site_info_daily = new SiteInfoDaily();
                $site_info_daily->date = $today;
                $site_info_daily->mini_active_users = 0;
                $site_info_daily->pc_active_users = 0;
                $site_info_daily->h5_active_users = 0;
                $site_info_daily->new_users = 0;
            }
            //根据url上dzqPf参数判断来自哪个端
            switch ($get_query_params['dzqPf']) {
                case Platform::FROM_WEAPP:
                    $site_info_daily->mini_active_users += 1;
                    break;
                case Platform::FROM_H5:
                    $site_info_daily->h5_active_users += 1;
                    break;
                case Platform::FROM_PC:
                    $site_info_daily->pc_active_users += 1;
                    break;
            }
            //判断改用户的 created_at 时间，如果是今天则表示新注册的用户
            if ($event->user->created_at < $tomorrow && $event->user->created_at > $today) {
                $site_info_daily->new_users += 1;
            }
            $site_info_daily->save();
            array_push($active_users, $event->user->id);
            app('cache')->put('active_users', $active_users, $cache_time);
        }
    }

    //启动数统计
    public function startStatistics(Forum $event)
    {
        $get_query_params = $event->request->getQueryParams();
        if (empty($get_query_params['dzqPf'])) {
            return;
        }
//        $start_peoples_dzqSid = app('cache')->get('start_peoples_dzqSid');
//        if(empty($start_peoples_dzqSid))    $start_peoples_dzqSid = [];
        $start_peoples_uid = app('cache')->get('start_peoples_uid');
        if (empty($start_peoples_uid)) {
            $start_peoples_uid = [];
        }
        $today = date('Y-m-d', time());
        $site_info_daily = SiteInfoDaily::query()->where('date', $today)->first();
        if (empty($site_info_daily)) {
            $site_info_daily = new SiteInfoDaily();
            $site_info_daily->date = $today;
            $site_info_daily->pc_start_count = 0;
            $site_info_daily->h5_start_count = 0;
            $site_info_daily->mini_start_count = 0;
            $site_info_daily->start_count = 0;
            $site_info_daily->pc_start_peoples = 0;
            $site_info_daily->h5_start_peoples = 0;
            $site_info_daily->mini_start_peoples = 0;
            $site_info_daily->start_peoples = 0;
        }
        //根据url上dzqPf参数判断来自哪个端
        switch ($get_query_params['dzqPf']) {
            case Platform::FROM_WEAPP:
                $site_info_daily->mini_start_count += 1;
                break;
            case Platform::FROM_H5:
                $site_info_daily->h5_start_count += 1;
                break;
            case Platform::FROM_PC:
                $site_info_daily->pc_start_count += 1;
                break;
        }
        $site_info_daily->start_count += 1;
        if ($event->user->id  && !in_array($event->user->id, $start_peoples_uid)) {
            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $cache_time = strtotime($tomorrow) - time();
            switch ($get_query_params['dzqPf']) {
                case Platform::FROM_WEAPP:
                    $site_info_daily->mini_start_peoples += 1;
                    break;
                case Platform::FROM_H5:
                    $site_info_daily->h5_start_peoples += 1;
                    break;
                case Platform::FROM_PC:
                    $site_info_daily->pc_start_peoples += 1;
                    break;
            }
            $site_info_daily->start_peoples += 1;
        }
        $site_info_daily->save();
        if ($event->user->id && !in_array($event->user->id, $start_peoples_uid) && !empty($cache_time)) {
            array_push($start_peoples_uid, $event->user->id);
            app('cache')->put('start_peoples_uid', $start_peoples_uid, $cache_time);
        }
    }
}
