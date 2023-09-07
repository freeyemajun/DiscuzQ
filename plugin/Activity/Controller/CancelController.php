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

namespace Plugin\Activity\Controller;

use App\Common\DzqConst;
use App\Common\ResponseCode;
use App\Repositories\UserRepository;
use Discuz\Base\DzqController;
use Plugin\Activity\Model\ActivityUser;

class CancelController extends DzqController
{
    use ActivityTrait;

    private $activity = null;

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        return $this->checkPermission($userRepo);
    }

    public function main()
    {
        $activity = $this->activity;
        $t = time();
        if (strtotime($activity['register_start_time']) > $t || $t > strtotime($activity['register_end_time'])) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '报名未开始或已结束，不能取消');
        }
        $activityUser = ActivityUser::query()->where([
            'activity_id' => $this->activity->id,
            'user_id' => $this->user->id,
            'status' => DzqConst::BOOL_YES
        ])->first();
        if (empty($activityUser)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '您还没有报名,不能取消');
        }
        $activityUser->status = DzqConst::BOOL_NO;
        if (!$activityUser->save()) {
            $this->outPut(ResponseCode::DB_ERROR);
        }
        $this->outPut(0, '取消成功');
    }
}
