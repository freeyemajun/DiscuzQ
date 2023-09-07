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

namespace App\Listeners\Setting;

use App\Api\Controller\Attachment\AttachmentTrait;
use App\Common\ResponseCode;
use App\Events\Setting\Saving;
use Discuz\Common\Utils;
use Discuz\Contracts\Setting\SettingsRepository;
use Discuz\Wechat\EasyWechatTrait;
use Illuminate\Support\Arr;
use Illuminate\Validation\Factory as Validator;
use Illuminate\Validation\ValidationException;

class CheckAttachment
{
    use AttachmentTrait;

    /**
     * @var SettingsRepository
     */
    public $settings;

    /**
     * @var Validator
     */
    public $validator;

    /**
     * @param SettingsRepository $settings
     * @param Validator $validator
     */
    public function __construct(SettingsRepository $settings, Validator $validator)
    {
        $this->settings = $settings;
        $this->validator = $validator;
    }

    /**
     * @param Saving $event
     * @throws ValidationException
     */
    public function handle(Saving $event)
    {
        $settings = $event->settings->where('tag', 'default')->pluck('value', 'key')->toArray();

        if (Arr::hasAny($settings, [
            'support_max_upload_attachment_num'
        ])) {
            // 合并原配置与新配置（新值覆盖旧值）
            $settings = array_merge((array) $this->settings->tag('default'), $settings);

            $this->validator->make($settings, [
                'support_max_upload_attachment_num' => 'required|string'
            ])->validate();

            $num = (int)$settings['support_max_upload_attachment_num'];
            if ($num < 0 || $num > $this->supportMaxUploadAttachmentNum) {
                Utils::outPut(ResponseCode::INVALID_PARAMETER, '最大上传附件数取值范围：0～'.$this->supportMaxUploadAttachmentNum);
            }

            $event->settings->transform(function ($settings) use ($num) {
                if (Arr::get($settings, 'key') == 'support_max_upload_attachment_num') {
                    Arr::set($settings, 'value', (string)$num);
                }
                return $settings;
            });
        }
    }
}
