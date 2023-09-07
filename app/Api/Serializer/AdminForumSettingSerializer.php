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

use App\Common\SettingCache;
use App\Settings\ForumSettingField;
use App\Repositories\UserRepository;
use App\Traits\ForumSettingSerializerTrait;
use Discuz\Api\Serializer\AbstractSerializer;
use App\Settings\SettingsRepository;
use Psr\Http\Message\ServerRequestInterface as Request;

class AdminForumSettingSerializer extends AbstractSerializer
{
    use ForumSettingSerializerTrait;

    protected $type = 'forums';

    protected $settings;

    protected $forumField;

    protected $userRepo;

    /**
     * @var SettingCache
     */
    protected $settingcache;

    public function __construct(SettingsRepository $settings, ForumSettingField $forumField, SettingCache $settingcache, Request $request, UserRepository $userRepo)
    {
        $this->settings = $settings;
        $this->forumField = $forumField;
        $this->settingcache = $settingcache;
        $this->request = $request;
        $this->userRepo = $userRepo;
    }

    /**
     * @param object $user
     * @return array
     */
    public function getDefaultAttributes($user = null)
    {
        if ($user) {
            $actor = $user;
        } else {
            $actor = $this->getActor();
        }

        $commonAttributes = $this->getCommonAttributes($actor);
        $attributes = [
            // 站点设置
            'set_site' => [],

            // 注册设置
            'set_reg' => [],

            // 第三方登录设置
            'passport' => [],

            // 支付设置
            'paycenter' => [],

            // 附件设置
            'set_attach' => [],

            // 腾讯云设置
            'qcloud' => [
                'qcloud_cdn' => (bool) $this->settings->get('qcloud_cdn', 'qcloud'),
                'qcloud_cdn_speed_domain' => $this->settings->get('qcloud_cdn_speed_domain', 'qcloud'), // 加速域名
                'qcloud_cdn_main_domain' => $this->settings->get('qcloud_cdn_main_domain', 'qcloud'), // 主域名
                'qcloud_cdn_origins' => json_decode($this->settings->get('qcloud_cdn_origins', 'qcloud')), // 源站地址
                'qcloud_cdn_server_name' => $this->settings->get('qcloud_cdn_server_name', 'qcloud'), // 回源HOST

                'qcloud_ssr_region' => $this->settings->get('qcloud_ssr_region', 'qcloud'), // 地区
                'qcloud_ssr_bucket' => $this->settings->get('qcloud_ssr_bucket', 'qcloud'), // 存储桶名称
                'qcloud_ssr_access_path' => $this->settings->get('qcloud_ssr_access_path', 'qcloud'), // 访问地址
            ],

            // 提现设置
            'set_cash' => [],

            // 其它信息(非setting中的信息)
            'other' => [
                'inner_net_ip' => json_decode($this->settings->get('inner_net_ip', 'default'), true)
            ],

            'lbs' => [],

            'ucenter' => []
        ];
        $attributes = array_merge_recursive($attributes, $commonAttributes);

        return $attributes;
    }
}
