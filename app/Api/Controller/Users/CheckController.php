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

use App\Censor\Censor;
use App\Common\ResponseCode;
use App\Models\User;
use App\Repositories\UserRepository;
use Discuz\Base\DzqController;
use Discuz\Base\DzqLog;
use Discuz\Foundation\EventsDispatchTrait;
use Discuz\Auth\AssertPermissionTrait;

class CheckController extends DzqController
{
    use EventsDispatchTrait;
    use AssertPermissionTrait;

    protected $censor;

    public function __construct(Censor $censor)
    {
        $this->censor = $censor;
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        return true;
    }


    public function main()
    {
        try {
            $username = $this->inPut('username');
            $nickname = $this->inPut('nickname');
            if (!empty($username)) {
                User::checkName('username', $username);
            }
            if (!empty($nickname)) {
                User::checkName('nickname', $nickname);
            }

            $this->outPut(ResponseCode::SUCCESS);
        } catch (\Exception $e) {
            DzqLog::error('username_nickname_check_api_error', [
                'username' => $this->inPut('username'),
                'nickname' => $this->inPut('nickname')
            ], $e->getMessage());
            $this->outPut(ResponseCode::INTERNAL_ERROR, '用户昵称检测接口异常');
        }
    }
}
