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

namespace App\Console\Commands;

use App\Models\Group;
use App\Models\User;
use App\Notifications\Messages\Database\GroupExpiredMessage;
use App\Notifications\System;
use Carbon\Carbon;
use Discuz\Console\AbstractCommand;
use Discuz\Contracts\Setting\SettingsRepository;
use Discuz\Foundation\Application;

class GroupExpiredNoticeCommand extends AbstractCommand
{
    protected $signature = 'group:expiredNotice';

    protected $description = '站点续费和付费用户组还有3天到期通知';

    private $app;

    private $settings;

    public function __construct(Application $app, SettingsRepository $settings)
    {
        $this->app = $app;
        $this->settings = $settings;
        parent::__construct();
    }

    public function handle()
    {
        //如果是付费站点，则查找还有3天到期的用户
        $siteMode = $this->settings->get('site_mode');
        $threeDaysAfter = Carbon::now()->addDays(3);
        $fourDaysAfter = Carbon::now()->addDays(4);
        if ($siteMode == 'pay') {         //如果是付费站点，则找普通用户组3天后到期的用户，进行通知
            $users = User::query()
                ->join('group_user', function ($join) {
                    $join->on('group_user.user_id', '=', 'users.id')
                            ->where('group_id', Group::MEMBER_ID);
                })
                ->whereBetween('users.expired_at', [$threeDaysAfter, $fourDaysAfter])
                ->get();
            $group = Group::query()->find(Group::MEMBER_ID);
            if (!empty($users->toArray())) {
                $notifyData = [
                    'groupname' =>  $group->name,
                    'raw'   =>  [
                        'refeeType' =>  1           //普通用户组续费类型
                    ]
                ];
                foreach ($users as $user) {
                    $user->notify(new System(GroupExpiredMessage::class, $user, $notifyData));
                }
            }
        }
        //针对处于付费用户组的3天后到期的用户，进行通知
        //先找出付费用户组
        $pay_groups = Group::query()->where('is_paid', Group::IS_PAID)->pluck('name', 'id')->toArray();
        if (empty($pay_groups)) {
            $this->info('执行普通用户组通知完成');
            return;
        }
        $pay_group_ids = array_keys($pay_groups);
        $users = User::query()
            ->join('group_user', function ($join) use ($pay_group_ids) {
                $join->on('group_user.user_id', '=', 'users.id')
                    ->whereIn('group_id', $pay_group_ids);
            })
            ->whereBetween('group_user.expiration_time', [$threeDaysAfter, $fourDaysAfter])
            ->get();
        if (!empty($users->toArray())) {
            foreach ($users as $user) {
                $user->notify(new System(GroupExpiredMessage::class, $user, [
                    'groupname' => $pay_groups[$user->group_id],
                    'raw'   =>  [
                        'refeeType' =>  2           //付费用户组续费类型
                    ]
                ]));
            }
        }
        $this->info('执行站点续费和付费用户组还有3天到期通知');
    }
}
