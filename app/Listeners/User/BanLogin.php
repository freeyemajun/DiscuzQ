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

namespace App\Listeners\User;

use App\Common\ResponseCode;
use Discuz\Common\Utils;
use App\Models\User;

class BanLogin
{
    public function handle($event)
    {
        $user = $event->user;
        switch ($user->status) {
            case 1:
                Utils::outPut(ResponseCode::USER_BAN, '', ['banReason'=>$user->reject_reason]);
                break;
//            case 2:
//                Utils::outPut(ResponseCode::JUMP_TO_AUDIT);
//                break;
            case 3:
                Utils::outPut(ResponseCode::VALIDATE_REJECT, '', User::getUserReject($user->id));
                break;
            case 4:
                Utils::outPut(ResponseCode::VALIDATE_IGNORE);
                break;
        }
    }
}
