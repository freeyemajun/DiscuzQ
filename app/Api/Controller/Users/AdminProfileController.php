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

namespace App\Api\Controller\Users;

use App\Common\ResponseCode;
use App\Models\User;
use Discuz\Base\DzqAdminController;

class AdminProfileController extends DzqAdminController
{
    use ProfileTrait;

    public $providers = [
        \App\Providers\UserServiceProvider::class,
    ];

    public $optionalInclude = ['groups', 'dialog', 'wechat'];

    public function main()
    {
        $user_id = $this->inPut('userId');
        $user = User::find($user_id);
        if (!$user) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }

        if (!empty($this->inPut('include'))) {
            if (array_diff($this->inPut('include'), $this->optionalInclude)) {
                //如果include 超出optionalinclude 就报错
                $this->outPut(ResponseCode::NET_ERROR);
            }
        }

        $data = $this->getData($user, true);

        $this->outPut(ResponseCode::SUCCESS, '', $data);
    }
}
