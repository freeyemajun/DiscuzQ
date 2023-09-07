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

namespace App\Api\Controller\Notification;

use App\Models\NotificationTpl;
use App\Notifications\Messages\TemplateVariables;

trait NotificationTrait
{
    use TemplateVariables;

    /**
     * @var string[] 禁止修改的系统通知，只能设置开启关闭
     */
    protected $disabledId = [
        'system.post.replied',
        'system.post.liked',
        'system.post.paid',
        'system.post.reminded',
        'system.withdraw.noticed',
        'system.withdraw.withdraw',
        'system.divide.income',
        'system.question.asked',
        'system.question.answered',
        'system.question.expired',
        'system.red_packet.gotten',
        'system.question.rewarded',
        'system.question.rewarded.expired',
    ];

    /**
     * @param NotificationTpl $model
     * @return array
     */
    protected function getDefaultAttributes($data)
    {
        $result = [];
        $i = 0;
        foreach ($data as $model) {
            $trans = 'template_variables.' . ($this->getTemplateVariables($model->notice_id) ?? '');

            $result[$i] = [
                'tplId'             => $model->id,
                'status'             => $model->status,
                'type'               => $model->type,
                'typeName'          => $model->type_name,
                'title'              => $model->title,
                'content'            => $model->content,
                'templateId'        => $model->template_id,
                'templateVariables' => $trans === 'template_variables.' ? [] : trans($trans),
                'keys'               => $model->keys ?? [],
                'firstData'         => $model->first_data,
                'keywordsData'      => $model->keywords_data ? explode(',', $model->keywords_data) : [],
                'remarkData'        => $model->remark_data,
                'color'              => $model->color ?: [],
                'redirectType'      => (int) $model->redirect_type,
                'redirectUrl'       => (string) $model->redirect_url,
                'pagePath'          => (string) $model->page_path,
                'disabled'           => $model->type === NotificationTpl::SYSTEM_NOTICE && in_array($model->notice_id, $this->disabledId),
                'isError'           => $model->is_error,
                'errorMsg'          => $model->error_msg,
                'pushType'          => $model->push_type,
                'delayTime'         => $model->delay_time,
            ];
            if ($model->type == NotificationTpl::MINI_PROGRAM_NOTICE) {
                // 小程序通知模板统一，触发点的提示语
                $result[$i]['miniProgramPrompt'] = trans('template_variables.notice_prompt.' . $model->type_name);
            }
            $i++;
        }

        return $result;
    }
}
