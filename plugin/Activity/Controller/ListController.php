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

namespace Plugin\Activity\Controller;

use App\Common\DzqConst;
use App\Models\User;
use App\Repositories\UserRepository;
use Discuz\Base\DzqController;
use Plugin\Activity\Model\ActivityUser;
use Plugin\Activity\Model\ThreadActivity;

class ListController extends DzqController
{
    use ActivityTrait;

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        return $this->checkPermission($userRepo, true);
    }

    public function main()
    {
        $activityId = $this->inPut('activityId');
        $perPage = $this->inPut('perPage');
        $page = $this->inPut('page');
        $activity = ThreadActivity::query()->find($activityId);
        $aUsers = ActivityUser::query()
            ->where([
                'activity_id' => $activityId,
                'status' => DzqConst::BOOL_YES
            ])->select('user_id as userId', 'additional_info')->orderByDesc('updated_at');
        $data = $this->pagination($page, $perPage, $aUsers);
        $userIds = array_column($data['pageData'], 'userId');
        $users = User::query()->whereIn('id', $userIds)->get()->keyBy('id');
        foreach ($data['pageData'] as &$item) {
            $userId = $item['userId'];
            $item['avatar'] = '';
            $item['nickname'] = '';
            $item['additionalInfo'] = $activity->user_id == $this->user->id ? json_decode($item['additional_info']) : [];
            if (isset($users[$userId])) {
                $item['avatar'] = $users[$userId]['avatar'];
                $item['nickname'] = $users[$userId]['nickname'];
            }
            unset($item['additional_info']);
        }
        $this->outPut(0, '', $data);
    }
}
