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

use App\Models\NotificationTiming;
use App\Models\User;
use App\Notifications\System;
use Discuz\Console\AbstractCommand;
use Discuz\Foundation\Application;
use Discuz\Notifications\Traits\NotificationTimingTrait;
use Exception;
use Illuminate\Database\ConnectionInterface;

class SendNotificationsRegularlyCommand extends AbstractCommand
{
    use NotificationTimingTrait;

    protected $signature = 'sendNotificationsRegularly:send';

    protected $description = '运行通知定时发送机制';

    protected $app;

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    /**
     * AvatarCleanCommand constructor.
     * @param string|null $name
     * @param ConnectionInterface $connection
     */
    public function __construct(string $name = null, Application $app, ConnectionInterface $connection)
    {
        parent::__construct($name);

        $this->app = $app;
        $this->connection = $connection;
    }

    public function handle()
    {
        $this->info('脚本执行 [开始]');
        $this->info('');

        $notificationTiming = NotificationTiming::query()->whereNull('expired_at')->get();
        app('log')->info('pending_data', ['notificationTiming' => $notificationTiming]);

        if (empty($notificationTiming->count())) {
            $this->info('');
            $this->info('脚本执行 [完成],处理数据：'.$notificationTiming->count().'条');
            return;
        }

        $bar = $this->createProgressBar(count($notificationTiming));
        $bar->start();
        $this->info('');

        $notificationTiming->map(function ($item) use ($bar) {
            // Start Transaction
            $this->connection->beginTransaction();
            try {
                $sendResponse = $this->sendNotification($item->user_id, $item->notice_id, false);
                if ($sendResponse['result'] == false) {
                    $this->connection->commit();
                    return;
                }

                $receiveUser = User::query()->where('id', '=', $item->user_id)->first();
                if (empty($receiveUser)) {
                    app('log')->info('SendNotificationsRegularlyCommand::user_id_not_exist', [
                        'userId' => $item->user_id,
                        'receiveUser' => $receiveUser
                    ]);
                    $this->connection->rollBack();
                    return;
                }

                $noticeData = json_decode($item->data);

                $userId = !empty($noticeData->userId) ? $noticeData->userId : 0;
                $user = User::query()->where('id', '=', $userId)->first();

                $data = collect($noticeData->data)->toArray();
                if (!empty($this->systemMethod[$item->notice_id])) {
                    $receiveUser->notify(new System(($this->systemMethod[$item->notice_id]), $user, $data));
                } elseif (!empty($this->nonSystemMethod[$item->notice_id])) {
                    $contentId = !empty($noticeData->contentData->id) ? $noticeData->contentData->id : 0;
                    $table = !empty($noticeData->contentData->table) ? $noticeData->contentData->table : '';
                    $class = new $table();
                    $contentData = $class->where('id', '=', $contentId)->first();

                    $noticeMethod = '\App\Notifications\\'.$this->nonSystemMethod[$item->notice_id];
                    $receiveUser->notify(new $noticeMethod($user, $contentData, $data));
                } else {
                    app('log')->info('notice_id_is_not_exist', [$item->notice_id]);
                }

                $this->connection->commit();
            } catch (Exception $e) {
                app('log')->info('SendNotificationsRegularlyCommand', $e->getMessage());
                $this->connection->rollBack();
            }

            $bar->advance();
            $this->info('');
        });

        $bar->finish();

        $this->info('');
        $this->info('脚本执行 [完成],处理数据：'.$notificationTiming->count().'条');
    }
}
