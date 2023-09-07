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

namespace App\Models;


use App\Common\DzqConst;
use App\Common\PermissionKey;
use Discuz\Base\DzqModel;

class PluginGroupPermission extends DzqModel
{
    protected $table = 'plugin_group_permission';

    protected $fillable = ['group_id','app_id','permission','status'];

    public static function hasPluginPermission($appId, $groupId)
    {
        if ($groupId == Group::ADMINISTRATOR_ID) return true;
        return PluginGroupPermission::query()->where([
            'group_id' => $groupId,
            'app_id' => $appId,
            'status' => DzqConst::BOOL_YES,
            'permission' => PermissionKey::PLUGIN_INSERT_PERMISSION
        ])->exists();
    }
}
