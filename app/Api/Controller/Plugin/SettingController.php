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

namespace App\Api\Controller\Plugin;

use App\Common\ResponseCode;
use App\Models\PluginSettings;
use Discuz\Base\DzqAdminController;

class SettingController extends DzqAdminController
{
    use PluginTrait;

    public function main()
    {
        $appId = $this->inPut('appId');
        $name = $this->inPut('appName');
        $type = $this->inPut('type');
        $privateValue = $this->inPut('privateValue');
        $publicValue = $this->inPut('publicValue');
        $this->dzqValidate($this->inPut(), [
            'appId' => 'required|string|max:100',
            'appName' => 'required|string|max:100',
            'type' => 'required|integer',
        ]);

        if (!empty($privateValue) && !is_array($privateValue)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }

        if (!empty($publicValue) && !is_array($publicValue)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }


        $setResult = $this->app->make(PluginSettings::class)->setData($appId, $name, $type, $privateValue, $publicValue);

        if (!$setResult) {
            $this->outPut(ResponseCode::DB_ERROR);
        }

        $groupId = $this->user->groupId;
        $isAdmin = $this->user->isAdmin();
        $result = $this->getAllSettingAndConfig($groupId, $isAdmin, true);

        $this->outPut(ResponseCode::SUCCESS, '', array_values($result));
    }
}
