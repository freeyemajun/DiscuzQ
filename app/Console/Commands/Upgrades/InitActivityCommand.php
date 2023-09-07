<?php
/**
 * Copyright (C) 2021 Tencent Cloud.
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


use App\Common\DzqConst;
use App\Models\Group;
use App\Models\PluginGroupPermission;
use Discuz\Common\Utils;
use Discuz\Console\AbstractCommand;

class InitActivityCommand extends AbstractCommand
{
    protected $signature = 'init:activity';
    protected $description='初始化报名帖数据';
    protected function handle()
    {
        $outPut = Utils::runConsoleCmd('migrate:plugin', ['--force' => true,'--name'=>'activity']);
        $this->info($outPut);
        $groupIds = Group::all()->pluck('id')->toArray();
        foreach ($groupIds as $groupId) {
            if($groupId != Group::GUEST_ID && $groupId!=Group::UNPAID){
                $attr = [
                    'group_id'=>$groupId,
                    'app_id'=>'612f4217ae890',
                    'permission'=>'canInsert'
                ];
                PluginGroupPermission::query()->updateOrInsert($attr,$attr+['status'=>DzqConst::BOOL_YES]);
                $this->info('添加用户组'.$groupId.' 插件使用权限成功。');
            }
        }
    }
}
