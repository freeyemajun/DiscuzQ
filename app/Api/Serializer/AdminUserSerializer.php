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

namespace App\Api\Serializer;

use App\Models\User;
use App\Traits\UserSerializerTrait;
use Discuz\Api\Serializer\AbstractSerializer;

class AdminUserSerializer extends AbstractSerializer
{
    use UserSerializerTrait;

    /**
     * {@inheritdoc}
     *
     * @param User $model
     */
    public function getDefaultAttributes($model)
    {
        $commonAttributes = $this->getCommonAttributes($model);
        $attributes = [];

        // 限制字段 本人/权限 显示
        $attributes += [
            'originalMobile'    => $model->getRawOriginal('mobile'),
//            'registerIp'        => $model->register_ip,
//            'registerPort'      => $model->register_port,
//            'lastLoginIp'       => $model->last_login_ip,
//            'lastLoginPort'     => $model->last_login_port,
            'identity'          => $model->identity,
            'realname'          => $model->realname,
        ];

        $attributes += [
            'createdAt'     => optional($model->created_at)->format('Y-m-d H:i:s'),
            'registerIp'    => $model->register_ip,
            'lastLoginIp'   => $model->last_login_ip
        ];
        $attributes = array_merge_recursive($attributes, $commonAttributes);

        $attributes['username'] = $model->username;

        return $attributes;
    }
}
