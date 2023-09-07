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

use App\Common\ResponseCode;
use App\Models\NotificationTpl;
use Discuz\Base\DzqAdminController;
use Discuz\Wechat\EasyWechatTrait;

class ResourceNotificationTplController extends DzqAdminController
{
    use NotificationTrait;

    use EasyWechatTrait;

    public function main()
    {
        $type_name = $this->inPut('typeName');
        $type = $this->inPut('type');

        $tpl = NotificationTpl::query();

        $tpl->when($type != '', function ($query) use ($type) {
            $query->where('type', $type);
        });

        $typeNames = explode(',', $type_name);

        $query = $tpl->whereIn('type_name', $typeNames)->orderBy('type');

        $data = $query->get();

        /**
         * 检测是否存在小程序通知，查询小程序模板变量 key 值
         *
         * @URL 订阅消息参数值内容限制说明: https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.send.html
         */
        $miniProgram = $data->where('type', NotificationTpl::MINI_PROGRAM_NOTICE);
        if ($miniProgram->isNotEmpty()) {
            $data->map(function ($item) {
                if ($item->type == NotificationTpl::MINI_PROGRAM_NOTICE) {
                    $keys = $this->getMiniProgramKeys($item);
                    $item->keys = $keys;
                }
            });
        }

        $res = $this->getDefaultAttributes($data);
        $arr = [];
        foreach ($res as $k => $v) {
            $arr[$k] = $v['templateVariables'];
        }
        $res = $this->camelData($this->getDefaultAttributes($data));
        foreach ($res as $k => &$v) {
            $v['templateVariables'] = $arr[$k];
        }

        $this->outPut(ResponseCode::SUCCESS, '', $res);
    }
}
