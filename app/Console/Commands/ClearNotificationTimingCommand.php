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

use App\Commands\Wallet\ChangeUserWallet;
use App\Models\NotificationTiming;
use App\Models\Order;
use App\Models\OrderChildren;
use App\Models\UserWallet;
use App\Models\UserWalletLog;
use Discuz\Console\AbstractCommand;
use Discuz\Foundation\Application;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\ConnectionInterface;

class ClearNotificationTimingCommand extends AbstractCommand
{
    protected $signature = 'notificationTiming:clear';

    protected $description = '定期清除维护notification_timing表';

    protected $survivalTime = 180; // 保留 180 天内的通知数据

    public function handle()
    {
        $this->info('脚本执行 [开始]');
        $this->info('');

        $number = NotificationTiming::query()
            ->where('expired_at', '<', Carbon::now()->subDays($this->survivalTime))
            ->delete();

        $this->info('');
        $this->info('脚本执行 [完成],已清除 '.$number.'条数据');
    }
}
