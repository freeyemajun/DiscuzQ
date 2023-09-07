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

namespace App\Console\Commands\Upgrades;

use App\Models\NotificationTpl;
use Discuz\Base\DzqLog;
use Discuz\Console\AbstractCommand;

class UpdateNotificationTplVars extends AbstractCommand
{
    protected $signature = 'upgrade:updateNotificationTplVars';

    protected $description = '更新系统通知模板中的var变量：添加nickname字段';

    public function handle()
    {
        $this->info('脚本执行 [开始]');
        $this->info('');

        try {
            NotificationTpl::query()
                ->where('notice_id', 'like', 'system.%')
                ->whereNotNull('vars')
                ->get()
                ->map(function (NotificationTpl $notificationTpl) {
                    if (!empty($notificationTpl->vars)) {
                        $vars = unserialize($notificationTpl->vars);
                        if (array_key_exists('{username}', $vars) && !array_key_exists('{nickname}', $vars)) {
                            $vars = $this->positionArrayPush($vars, ['{nickname}' => '昵称'], '{username}');
                            $notificationTpl->vars = serialize($vars);
                            $notificationTpl->save();
                        }
                    }
                });
        } catch (\Exception $e) {
            DzqLog::error('update_notification_tpl_vars_error', [], $e->getMessage());
            $this->info('脚本执行 [异常]');
        }

        $this->info('');
        $this->info('脚本执行 [完成]');
    }

    public function positionArrayPush($array, $data=null, $key=false): array
    {
        $data   = (array)$data;
        $offset = ($key === false) ? false : array_search($key, array_keys($array)) + 1;
        if ($offset !== false) {
            return array_merge(
                array_slice($array, 0, $offset),
                $data,
                array_slice($array, $offset)
            );
        } else {
            return array_merge($array, $data);
        }
    }
}
