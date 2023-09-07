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
use App\Models\Invite;
use App\Models\Group;
use App\Models\InviteUser;
use Discuz\Base\DzqAdminController;

class ListUserInviteController extends DzqAdminController
{
    public function main()
    {
        $userId = $this->inPut('userId');
        $currentPage = $this->inPut('page');
        $perPage = $this->inPut('perPage');

        $query = InviteUser::query()
            ->leftJoin('users', 'users.id', '=', 'invite_users.to_user_id')
            ->where([
                'invite_users.user_id' => $userId,
            ])
            ->groupBy('invite_users.to_user_id', 'invite_users.updated_at')
            ->orderBy('invite_users.updated_at', 'desc');
        $users = $this->pagination($currentPage, $perPage, $query, false, ['users.id','users.avatar','users.nickname','invite_users.updated_at']);
        $userDatas = $users['pageData']->toArray();
        $result = [];
        foreach ($userDatas as  $value) {
            $result[] = [
                'userId' => $value['id'],
                'nickname' => $value['nickname'],
                'avatarUrl' => $value['avatar'],
                'updatedAt' => $value['updated_at']
            ];
        }

        $users['pageData'] = $result ?? [];

        $this->outPut(ResponseCode::SUCCESS, '', $users);
    }
}
