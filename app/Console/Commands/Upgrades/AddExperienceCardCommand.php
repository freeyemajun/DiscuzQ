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

use App\Common\PermissionKey;
use App\Models\Group;
use App\Models\Permission;
use Discuz\Base\DzqLog;
use Discuz\Console\AbstractCommand;

class AddExperienceCardCommand extends AbstractCommand
{
    protected $signature = 'upgrade:addExperienceCard';
    protected $description = '添加免费体验卡用户组及其相关权限';

    public function handle()
    {
        $this->info('脚本执行 [开始]');
        $this->info('');
        try {
            Group::query()->insertOrIgnore([
                [
                    'id' => Group::EXPERIENCE_ID,
                    'name' => '免费体验',
                    'default' => 0,
                    'time_range' => Group::DEFAULT_TIME_RANGE,
                    'content_range' => Group::DEFAULT_CONTENT_RANGE
                ]
            ]);

            Permission::query()->insertOrIgnore(
                [
                    ['group_id' => Group::EXPERIENCE_ID, 'permission' => 'switch.'.PermissionKey::VIEW_THREADS],        // 查看主题列表-左侧勾选按钮
                    ['group_id' => Group::EXPERIENCE_ID, 'permission' => PermissionKey::VIEW_THREADS],                  // 查看主题列表-生效范围-全局
                    ['group_id' => Group::EXPERIENCE_ID, 'permission' => 'switch.'.PermissionKey::THREAD_VIEW_POSTS],   // 查看主题详情-左侧勾选按钮
                    ['group_id' => Group::EXPERIENCE_ID, 'permission' => PermissionKey::THREAD_VIEW_POSTS],             // 查看主题详情-生效范围-全局
                    ['group_id' => Group::EXPERIENCE_ID, 'permission' => PermissionKey::THREAD_VIEW_VIDEO],             // 查看视频
                    ['group_id' => Group::EXPERIENCE_ID, 'permission' => PermissionKey::THREAD_VIEW_ATTACHMENT],        // 查看附件
                    ['group_id' => Group::EXPERIENCE_ID, 'permission' => PermissionKey::THREAD_DOWNLOAD_ATTACHMENT]     // 下载附件
                ]
            );
        } catch (\Exception $e) {
            DzqLog::error('AddExperienceCardCommand', [], $e->getMessage());
            $this->info('脚本执行 [异常]');
        }

        $this->info('');
        $this->info('脚本执行 [完成]');
    }
}
