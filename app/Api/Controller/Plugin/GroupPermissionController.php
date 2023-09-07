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

namespace App\Api\Controller\Plugin;

use App\Common\PermissionKey;
use App\Models\PluginGroupPermission;
use Discuz\Base\DzqAdminController;

class GroupPermissionController extends DzqAdminController
{
    public function main()
    {
        $groupId = $this->inPut('groupId');
        $permissions = $this->inPut('permissions');
        $this->dzqValidate($this->inPut(), [
            'groupId' => 'required|integer',
            'permissions' => 'required|array'
        ]);
        $ret = [
            'groupId' => $groupId,
            'permissions' => []
        ];
        foreach ($permissions as $permission) {
            $this->dzqValidate($permission, ['appId' => 'required|string', 'status' => 'required|integer|in:0,1']);
            $attr = [
                'group_id' => $groupId,
                'app_id' => $permission['appId'],
                'permission' => PermissionKey::PLUGIN_INSERT_PERMISSION
            ];
            PluginGroupPermission::query()->updateOrCreate($attr, ['status' => $permission['status']]);
            $ret['permissions'][] = [
                'appId' => $permission['appId'],
                'status' => $permission['status']
            ];
        }
        $this->outPut(0, '', $ret);
    }
}
