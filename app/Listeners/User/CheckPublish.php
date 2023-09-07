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

namespace App\Listeners\User;

use App\Events\Post\Saving as PostSaving;
use App\Events\Thread\Saving as ThreadSaving;
use App\Repositories\UserRepository;
use App\Settings\SettingsRepository;
use Discuz\Auth\AssertPermissionTrait;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Database\Eloquent\Model;

class CheckPublish
{
    use AssertPermissionTrait;

    /**
     * @var Factory
     */
    protected $validator;

    /**
     * @param Factory $validator
     */
    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Model $event
     */
    public function handle($event)
    {
        $data = $event->data;
        $captcha = [
            'captchaTicket' => $data['attributes']['captchaTicket'] ?? '',
            'captchaRandStr' => $data['attributes']['captchaRandStr'] ?? '',
            'ip' => $data['attributes']['ip'] ?? ''
        ];
        $settings   = app(SettingsRepository::class);
        $qcloudCaptcha = (bool)$settings->get('qcloud_captcha', 'qcloud');

        $userRepository = app(UserRepository::class);
        // 发布内容是否需要验证
        if ($event instanceof ThreadSaving && ! $event->thread->exists) {
            $needValidate = true;
        } elseif ($event instanceof PostSaving && ! $event->post->exists) {
            $needValidate = true;
        } else {
            $needValidate = false;
        }

        // 验证是否需要实名认证 或 绑定手机号
        if ($needValidate) {
            $rules = [];

            // 发布内容需先绑定手机
            if (! $event->actor->isAdmin() && (
                $event->actor->can('publishNeedBindPhone')
                || $event->actor->can('publishNeedBindWechat')
                || $qcloudCaptcha
            )) {
                $rules['user'][] = function ($attribute, $value, $fail) use ($event, $userRepository, $captcha) {
                    $userRepository->checkPublishPermission($event->actor, $captcha);
                };
            }

            $this->validator->make(['user' => $event->actor], $rules)->validate();
        }
    }
}
