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

namespace Plugin\Activity\Model;

use Discuz\Base\DzqModel;

class ThreadActivity extends DzqModel
{
    protected $table='plugin_activity_thread_activity';

    const ADDITIONAL_INFO_TYPE_NAME = 1;

    const ADDITIONAL_INFO_TYPE_MOBILE = 2;

    const ADDITIONAL_INFO_TYPE_WEIXIN = 3;

    const ADDITIONAL_INFO_TYPE_AD = 4;

    public static function allowInfoType()
    {
        return  [
            self::ADDITIONAL_INFO_TYPE_NAME,
            self::ADDITIONAL_INFO_TYPE_MOBILE,
            self::ADDITIONAL_INFO_TYPE_WEIXIN,
            self::ADDITIONAL_INFO_TYPE_AD
        ];
    }

    public static $addition_map = [
        self::ADDITIONAL_INFO_TYPE_NAME =>  '姓名',
        self::ADDITIONAL_INFO_TYPE_MOBILE => '手机号',
        self::ADDITIONAL_INFO_TYPE_WEIXIN => '微信号',
        self::ADDITIONAL_INFO_TYPE_AD => '联系地址'
    ];

    public static $addition_info_map = [
        'name'  =>  self::ADDITIONAL_INFO_TYPE_NAME,
        'mobile'  =>  self::ADDITIONAL_INFO_TYPE_MOBILE,
        'weixin'  =>  self::ADDITIONAL_INFO_TYPE_WEIXIN,
        'address'  =>  self::ADDITIONAL_INFO_TYPE_AD,
    ];
}
