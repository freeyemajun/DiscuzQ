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

class AddGroupUpgradeExpiredCommand extends AbstractCommand
{
    protected $signature = 'upgrade:addGroupUpgradeExpired';
    protected $description = '添加付费用户组升级、付费用户组到期、站点续费通知模板';

    public function handle()
    {
        $this->info('脚本执行 [开始]');
        $this->info('');

        try {
            NotificationTpl::query()->insertOrIgnore(
                [
                    [
                        'status'    => 1,
                        'type'      => 0,
                        'notice_id' => 'system.user.group.upgrade',
                        'type_name' => '用户组升级通知',
                        'title'     => '账号升级通知',
                        'content'   => '【{nickname}】恭喜你，成功升级为【{groupname}】！',
                        'vars'      => serialize([
                            '{nickname}'     => '用户昵称',
                            '{groupname}' => '升级用户组',
                        ]),
                    ],
                    [
                        'status'    => 1,
                        'type'      => 0,
                        'notice_id' => 'system.user.group.expired',
                        'type_name' => '站点续费用户组续费通知',
                        'title'     => '续费通知',
                        'content'   => '【{nickname}】，您购买的【{groupname}】即将过期，过期后将无法享受当前权益，请及时续费。',
                        'vars'      => serialize([
                            '{nickname}'     => '用户昵称',
                            '{groupname}' => '老用户组'
                        ]),
                    ]
                ]
            );
        } catch (\Exception $e) {
            DzqLog::error('add_group_upgrade_expired_command_error', [], $e->getMessage());
            $this->info('脚本执行 [异常]');
        }

        $this->info('');
        $this->info('脚本执行 [完成]');
    }
}
