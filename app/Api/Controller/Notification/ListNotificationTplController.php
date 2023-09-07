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
use Illuminate\Support\Collection;

class ListNotificationTplController extends DzqAdminController
{
    public function main()
    {
        $page = $this->inPut('page');
        $perPage = $this->inPut('perPage');

        $tpl = NotificationTpl::all(['id', 'status', 'type', 'type_name', 'is_error', 'error_msg'])
            ->groupBy('type_name');

        $pageData = $this->specialPagination($page, $perPage, $tpl, false);

        $pageData['pageData'] = $this->build($pageData['pageData']);

        $this->outPut(ResponseCode::SUCCESS, '', $this->camelData($pageData));
    }

    private function build(Collection $data)
    {
        return $data->map(function (Collection $item, $index) {
            // Splicing typeName
            $typeStatus = [];
            $errorArr = [];
            $item->each(function ($value) use (&$typeStatus, &$errorArr) {
                /** @var NotificationTpl $value */
                if ($value->status) {
                    $build = [
                        'id' => $value->id,
                        'status' => $value->status,
                        'type' => NotificationTpl::enumTypeName($value->type),
                        'is_error' => $value->is_error,
                        'error_msg' => $value->error_msg,
                    ];
                    array_push($typeStatus, $build);
                }
            });

            return [
                'name' => $index,
                'type_status' => $typeStatus,
            ];
        })->values();
    }
}
