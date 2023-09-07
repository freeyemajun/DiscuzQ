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

namespace App\Traits;

use App\Api\Serializer\DialogSerializer;
use App\Api\Serializer\GroupSerializer;
use App\Api\Serializer\UserSignInSerializer;
use App\Api\Serializer\UserWechatSerializer;
use App\Models\User;
use App\Models\UserQq;
use Tobscure\JsonApi\Relationship;

trait UserSerializerTrait
{
    public function getCommonAttributes($model = null)
    {
        $attributes = [];
        if (empty($model)) {
            return $attributes;
        }

        $attributes = [
            'id'                => (int) $model->id,
            'username'          => '',
            'avatarUrl'         => $model->avatar,
            'status'            => $model->status,
            'loginAt'           => optional($model->login_at)->format('Y-m-d H:i:s'),
//            'joinedAt'          => optional($model->joined_at)->format('Y-m-d H:i:s'),
            'expiredAt'         => optional($model->expired_at)->format('Y-m-d H:i:s'),
//            'createdAt'         => optional($model->created_at)->format('Y-m-d H:i:s'),
            'banReason'         => !empty($model->reject_reason) ? $model->reject_reason : '', // 禁用原因
            'nickname'          => $model->nickname,
        ];

        // 判断禁用原因
//        if ($model->status == 1) {
//            $attributes['banReason'] = !empty($model->latelyLog) ? $model->latelyLog->message : '' ;
//        }

        if ($model->bind_type == 2) {
            $attributes['avatarUrl'] = ! empty($attributes['avatarUrl']) ? $attributes['avatarUrl'] : $this->qqAvatar($model);
        }

        return $attributes;
    }

    /**
     * @param $user
     * @return Relationship
     */
    public function wechat($user)
    {
        return $this->hasOne($user, UserWechatSerializer::class);
    }

    /**
     * @param $user
     * @return Relationship
     */
    public function groups($user)
    {
        return $this->hasMany($user, GroupSerializer::class);
    }

    public function extFields($user)
    {
        return $this->hasMany($user, UserSignInSerializer::class);
    }

    /**
     * @param $user
     * @return Relationship
     */
    public function deny($user)
    {
        return $this->hasMany($user, UserSerializerTrait::class);
    }

    /**
     * @param $user
     * @return Relationship
     */
    public function dialog($user)
    {
        return $this->hasOne($user, DialogSerializer::class);
    }

    /**
     * qq头像
     * @param User $user
     * @return string
     */
    public function qqAvatar(User $user)
    {
        $qqUser = UserQq::where('user_id', $user->id)->first();
        if (! $qqUser) {
            return '';
        }
        return $qqUser->headimgurl;
    }
}
