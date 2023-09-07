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

namespace App\Console;


use Discuz\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('finance:create')->daily();
        $schedule->command('order:query')->everyMinute()->withoutOverlapping();
        $schedule->command('invite:expire')->everyMinute()->withoutOverlapping();
        $schedule->command('reward:expire')->everyMinute()->withoutOverlapping();
        $schedule->command('redPacket:expire')->everyMinute()->withoutOverlapping();
        $schedule->command('abnormalOrder:clear')->everyMinute()->withoutOverlapping();
        $schedule->command('transcode:update')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('attachment:update')->everyFiveMinutes()->withoutOverlapping();
        $schedule->command('sendNotificationsRegularly:send')->everyMinute()->withoutOverlapping();

        // 维护清理
//        $schedule->command('clear:attachment')->daily();
//        $schedule->command('clear:video')->daily();
        $schedule->command('clear:question')->daily();
        $schedule->command('clear:thread_draft')->daily();
        $schedule->command('clear:session_token')->everyMinute();
        $schedule->command('notificationTiming:clear')->daily();

        //监听定时任务
        $schedule->command('task:start')->everyMinute();
        $schedule->command('register:notice')->everyMinute();

        //用户组到期提醒
        $schedule->command('group:expiredNotice')->daily();
        //sitemap
        $schedule->command('add:Sitemap')->at('03:00');

        $schedule->command('ChatGpt')->daily();
    }
}
