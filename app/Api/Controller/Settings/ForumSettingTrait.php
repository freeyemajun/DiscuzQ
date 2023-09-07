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

namespace App\Api\Controller\Settings;

use App\Api\Serializer\AdminForumSettingSerializer;
use App\Api\Serializer\ForumSettingSerializer;
use App\Common\ResponseCode;
use App\Common\Utils;
use App\Events\Users\Forum;
use App\Models\User;

trait ForumSettingTrait
{
    public $settings;

    protected $events;

    protected function forumSettingMain($isAdmin = false)
    {
        $serializer = ForumSettingSerializer::class;
        $isAdmin == true && $serializer = AdminForumSettingSerializer::class;
        $forum_serialize = $this->app->make($serializer);

        $data = $forum_serialize->getDefaultAttributes($this->user);

//        $tag = Str::of($this->inPut('tag'))->replace(' ', '')->explode(',')->filter();
//        if($tag->contains('agreement')){
        $agreement = $this->settings->tag('agreement') ?? [];
        $data['agreement'] = [
            'privacy' => (bool) ($agreement['privacy'] ?? false),
            'privacy_content' => $agreement['privacy_content'] ?? '',
            'register' => (bool) ($agreement['register'] ?? false),
            'register_content' => $agreement['register_content'] ?? '',
        ];

        $this->hideSensitive($data);
        $this->events->dispatch(new Forum($this->request, $this->user));

        return $this->camelData($data);
    }

    protected function hideSensitive(&$data)
    {
        isset($data['qcloud']['qcloud_secret_key']) && $data['qcloud']['qcloud_secret_key'] = Utils::hideStr($data['qcloud']['qcloud_secret_key']);
        isset($data['qcloud']['qcloud_sms_app_key']) && $data['qcloud']['qcloud_sms_app_key'] = Utils::hideStr($data['qcloud']['qcloud_sms_app_key']);
        isset($data['qcloud']['qcloud_captcha_secret_key']) && $data['qcloud']['qcloud_captcha_secret_key'] = Utils::hideStr($data['qcloud']['qcloud_captcha_secret_key']);
        isset($data['qcloud']['qcloud_vod_url_key']) && $data['qcloud']['qcloud_vod_url_key'] = Utils::hideStr($data['qcloud']['qcloud_vod_url_key']);
        isset($data['passport']['offiaccount_app_secret']) && $data['passport']['offiaccount_app_secret'] = Utils::hideStr($data['passport']['offiaccount_app_secret']);
        isset($data['passport']['miniprogram_app_secret']) && $data['passport']['miniprogram_app_secret'] = Utils::hideStr($data['passport']['miniprogram_app_secret']);
        isset($data['paycenter']['api_key']) && $data['paycenter']['api_key'] = Utils::hideStr($data['paycenter']['api_key']);

        if ($this->user->id != User::SUPER_ADMINISTRATOR) {
            isset($data['other']['inner_net_ip']) && $data['other']['inner_net_ip'] = Utils::hideStr($data['other']['inner_net_ip']);
        }
    }
}
