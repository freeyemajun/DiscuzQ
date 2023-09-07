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

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = new Setting();
        //$settings->truncate();
        $settings->insert([
            [
                'key' => 'site_rewards',          // 站点开关：0 开启站点，1 关闭站点
                'value' => '[{"key":1,"desc":"PC端","value":true},{"key":2,"desc":"H5端","value":true},{"key":3,"desc":"小程序端","value":true}]',                 // 默认开启
                'tag' => 'default',
            ],
            [
                'key' => 'site_areward',          // 站点开关：0 开启站点，1 关闭站点
                'value' => '[{"key":1,"desc":"PC端","value":true},{"key":2,"desc":"H5端","value":true},{"key":3,"desc":"小程序端","value":true}]',                 // 默认开启
                'tag' => 'default',
            ],
            [
                'key' => 'site_redpacket',          // 站点开关：0 开启站点，1 关闭站点
                'value' => '[{"key":1,"desc":"PC端","value":true},{"key":2,"desc":"H5端","value":true},{"key":3,"desc":"小程序端","value":true}]',                 // 默认开启
                'tag' => 'default',
            ],
            [
                'key' => 'site_anonymous',          // 站点开关：0 开启站点，1 关闭站点
                'value' => '[{"key":1,"desc":"PC端","value":true},{"key":2,"desc":"H5端","value":true},{"key":3,"desc":"小程序端","value":true}]',                 // 默认开启
                'tag' => 'default',
            ],
            [
                'key' => 'site_personalletter',          // 站点开关：0 开启站点，1 关闭站点
                'value' => '[{"key":1,"desc":"PC端","value":true},{"key":2,"desc":"H5端","value":true},{"key":3,"desc":"小程序端","value":true}]',                 // 默认开启
                'tag' => 'default',
            ],
            [
                'key' => 'site_shop',          // 站点开关：0 开启站点，1 关闭站点
                'value' => '[{"key":1,"desc":"PC端","value":true},{"key":2,"desc":"H5端","value":true},{"key":3,"desc":"小程序端","value":true}]',                 // 默认开启
                'tag' => 'default',
            ],
            [
                'key' => 'site_pay',          // 站点开关：0 开启站点，1 关闭站点
                'value' => '[{"key":1,"desc":"PC端","value":true},{"key":2,"desc":"H5端","value":true},{"key":3,"desc":"小程序端","value":true}]',                 // 默认开启
                'tag' => 'default',
            ],
            [
                'key' => 'site_usergroup',          // 站点开关：0 开启站点，1 关闭站点
                'value' => '[{"key":1,"desc":"PC端","value":true},{"key":2,"desc":"H5端","value":true},{"key":3,"desc":"小程序端","value":true}]',                 // 默认开启
                'tag' => 'default',
            ],
            [
                'key' => 'site_recharges',          // 站点开关：0 开启站点，1 关闭站点
                'value' => '[{"key":1,"desc":"PC端","value":true},{"key":2,"desc":"H5端","value":true},{"key":3,"desc":"小程序端","value":true}]',                 // 默认开启
                'tag' => 'default',
            ],
            [
                'key' => 'site_withdrawal',          // 站点开关：0 开启站点，1 关闭站点
                'value' => '[{"key":1,"desc":"PC端","value":true},{"key":2,"desc":"H5端","value":true},{"key":3,"desc":"小程序端","value":true}]',                 // 默认开启
                'tag' => 'default',
            ],
            [
                'key' => 'site_comment',          // 站点开关：0 开启站点，1 关闭站点
                'value' => '[{"key":1,"desc":"PC端","value":true},{"key":2,"desc":"H5端","value":true},{"key":3,"desc":"小程序端","value":true}]',                 // 默认开启
                'tag' => 'default',
            ]
        ]);
    }
}
