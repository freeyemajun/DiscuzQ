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

namespace Plugin\Activity;

use App\Common\CacheKey;
use App\Common\DzqConst;
use App\Common\ResponseCode;
use App\Models\User;
use App\Modules\ThreadTom\TomBaseBusi;
use Discuz\Base\DzqCache;
use Illuminate\Validation\Rule;
use Plugin\Activity\Model\ActivityUser;
use Plugin\Activity\Model\ThreadActivity;

class ActivityBusi extends TomBaseBusi
{
    public function select()
    {
        $activityId = $this->getParams('activityId');
        $activity = ThreadActivity::query()->where([
            'id' => $activityId,
            'status' => DzqConst::BOOL_YES
        ])->first();
        if (empty($activity)) {
            return false;
        }
        $result = $this->getActivityDetail($activity);
        return $this->jsonReturn($result);
    }

    public function create()
    {
        $this->activityValidate();
        $activity = new ThreadActivity();
        $rawAttr = $this->getActivityRawAttr();
        $rawAttr += [
            'user_id' => $this->user->id,
            'thread_id' => $this->threadId
        ];
        $activity->setRawAttributes($rawAttr);
        if ($activity->save()) {
            return $this->jsonReturn(['activityId' => $activity['id']]);
        } else {
            return false;
        }
    }

    public function update()
    {
        $this->activityValidate();
        $activityId = $this->getParams('activityId');
        if (empty($activityId)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '插件参数缺少字段 activityId ');
        }
        $activity = ThreadActivity::query()->where([
            'id' => $activityId,
            'status' => DzqConst::BOOL_YES
        ])->first();
        if (empty($activity)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '活动不存在');
        }
        //如果有人报名了，则不能编辑
        $isRegistered = ActivityUser::query()->where(['activity_id' => $activityId, 'status' => DzqConst::BOOL_YES])->exists();
        if ($isRegistered) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '已有人报名，不可编辑活动内容');
        }
        $rawAttr = $this->getActivityRawAttr();
        $activity->setRawAttributes($rawAttr);
        if ($activity->save()) {
            return $this->jsonReturn(['activityId' => $activityId]);
        } else {
            return false;
        }
    }

    public function delete()
    {
        $activityId = $this->getParams('activityId');
        $activity = ThreadActivity::query()->where([
            'id' => $activityId,
            'status' => DzqConst::BOOL_YES
        ])->first();
        if (empty($activity)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '活动不存在');
        }
        $activity->status = DzqConst::BOOL_NO;
        $activity->save();
        return true;
    }

    private function activityValidate()
    {
        $userId = $this->user->id;
        $threadId = $this->threadId;
        empty($userId) && $this->outPut(ResponseCode::INVALID_PARAMETER, '用户id无效');
        empty($threadId) && $this->outPut(ResponseCode::INVALID_PARAMETER, '帖子id无效');
        $position = $this->getParams('position');
        if (!empty($position)) {
            $this->dzqValidate($position, ['address' => 'string|max:100', 'location' => 'string|max:200', 'longitude' => 'numeric', 'latitude' => 'numeric']);
        }
        $this->dzqValidate(
            $this->body,
            [
                'title' => 'string|max:50',
                'content' => 'string|max:200',
                'activityStartTime' => 'required|date',
                'activityEndTime' => 'required|date|after_or_equal:' . $this->getParams('activityStartTime'),
                'registerStartTime' => 'date',
                'registerEndTime' => 'date|after_or_equal:' . $this->getParams('registerStartTime'),
                'totalNumber' => 'integer|min:0|max:10000',
                'additionalInfoType.*' => Rule::in(ThreadActivity::allowInfoType())
            ]
        );
    }

    private function getActivityRawAttr()
    {
        $activityStartTime = $this->getParams('activityStartTime');
        $activityEndTime = $this->getParams('activityEndTime');
        $registerStartTime = $this->getParams('registerStartTime');
        $registerEndTime = $this->getParams('registerEndTime');
        empty($registerStartTime) && $registerStartTime = date('Y-m-d H:i:s');
        empty($registerEndTime) && $registerEndTime = $activityStartTime;
        $totalNumber = $this->getParams('totalNumber');
        empty($totalNumber) && $totalNumber = 0;
        $data = [
            'title' => $this->getParams('title'),
            'content' => $this->getParams('content'),
            'activity_start_time' => $activityStartTime,
            'activity_end_time' => $activityEndTime,
            'register_start_time' => $registerStartTime,
            'register_end_time' => $registerEndTime,
            'total_number' => $totalNumber,
            'additional_info_type'  =>  json_encode($this->getParams('additionalInfoType'), JSON_UNESCAPED_UNICODE)

        ];
        $position = $this->getParams('position');
        if (!empty($position)) {
            $data += [
                'address' => $position['address'],
                'location' => $position['location'],
                'longitude' => $position['longitude'] ?: 0,
                'latitude' => $position['latitude'] ?: 0
            ];
        }
        return $data;
    }

    private function getActivityDetail($activity)
    {
        $activityId = $this->getParams('activityId');
        $activityUser = ActivityUser::query()->where([
            'activity_id' => $activityId,
            'status' => DzqConst::BOOL_YES
        ]);
        $currentNumber = $activityUser->count();
        $userIds = $activityUser->orderByDesc('id')->limit(3)->select('user_id')->pluck('user_id')->toArray();
        $users = DzqCache::hMGet(CacheKey::LIST_THREADS_V3_USERS, $userIds, function ($userIds) {
            return User::instance()->getUsers($userIds);
        }, 'id');
        $registerUsers = [];
        foreach ($users as $user) {
            $registerUsers[] = [
                'userId' => $user['id'],
                'avatar' => $user['avatar'],
                'nickname' => $user['nickname']
            ];
        }

        $isRegistered = $activityUser->where('user_id', $this->user->id)->exists();
        return [
            'activityId' => $activityId,
            'title' => $activity['title'],
            'content' => $activity['content'],
            'activityStartTime' => $activity['activity_start_time'],
            'activityEndTime' => $activity['activity_end_time'],
            'registerStartTime' => $activity['register_start_time'],
            'registerEndTime' => $activity['register_end_time'],
            'totalNumber' => $activity['total_number'],
            'currentNumber' => $currentNumber,
            'position' => [
                'address' => $activity['address'],
                'location' => $activity['location'],
                'longitude' => $activity['longitude'],
                'latitude' => $activity['latitude']
            ],
            'isRegistered' => $isRegistered,
            'isExpired' => time() > strtotime($activity['register_end_time']),
            'isMemberFull' => $activity['total_number'] == 0 ? false : $activity['total_number'] <= $currentNumber,
            'createdAt' => date('Y-m-d H:i:s', strtotime($activity['created_at'])),
            'updatedAt' => date('Y-m-d H:i:s', strtotime($activity['updated_at'])),
            'registerUsers' => $registerUsers,
            'additionalInfoType' => json_decode($activity['additional_info_type'], 1)
        ];
    }
}
