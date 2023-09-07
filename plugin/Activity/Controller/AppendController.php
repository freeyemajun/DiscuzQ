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
use App\Common\ResponseCode;
use App\Repositories\UserRepository;
use Discuz\Base\DzqController;
use Plugin\Activity\Model\ActivityUser;
use Plugin\Activity\Model\ThreadActivity;

class AppendController extends DzqController
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
            $this->outPut(ResponseCode::INVALID_PARAMETER, '报名未开始或已结束，报名失败');
        }
        $totalNumber = $activity['total_number'];
        $activityUserBuilder = ActivityUser::query()->where([
            'activity_id'=>$activity->id,
            'status'=>DzqConst::BOOL_YES
        ]);
        if ($totalNumber != 0) {
            $activityUserBuilder->count() >= $totalNumber && $this->outPut(ResponseCode::INVALID_PARAMETER, '人数已满，报名失败');
        }
        $activityUser = $activityUserBuilder->where('user_id', $this->user->id)->first();
        if (!empty($activityUser)) {
            $this->outPut(ResponseCode::RESOURCE_IN_USE, '您已经报名，不能重复报名');
        }
        $additional_info = $this->inPut('additionalInfo') ?? [];
        $judge_additional_info = [];
        if (!empty($additional_info)) {
            foreach ($additional_info as $key => $val) {
                $judge_additional_info[] = ThreadActivity::$addition_info_map[$key];
            }
        }
        if (!empty($this->activity->additional_info_type)) {
            $additional_info_type = json_decode($this->activity->additional_info_type, 1);
            $error_judge = array_diff($additional_info_type, $judge_additional_info);
            $error_msg = '';
            if (!empty($error_judge)) {
                foreach ($error_judge as $val) {
                    $error_msg .= ThreadActivity::$addition_map[$val].' ';
                }
            }
            if (!empty($error_msg)) {
                $this->outPut(ResponseCode::RESOURCE_IN_USE, '缺少必填信息：'.$error_msg);
            }
        }

        $activityUser = new ActivityUser();
        $activityUser->thread_id = $activity->thread_id;
        $activityUser->activity_id = $activity->id;
        $activityUser->user_id = $this->user->id;
        $activityUser->status = DzqConst::BOOL_YES;
        $activityUser->additional_info = json_encode($additional_info, JSON_UNESCAPED_UNICODE);
        if (!$activityUser->save()) {
            $this->outPut(ResponseCode::DB_ERROR);
        }
        $this->outPut(0, '报名成功');
    }
}
