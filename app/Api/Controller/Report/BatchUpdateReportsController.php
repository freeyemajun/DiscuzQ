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

namespace App\Api\Controller\Report;

use App\Models\Report;
use App\Common\ResponseCode;
use Discuz\Base\DzqAdminController;

class BatchUpdateReportsController extends DzqAdminController
{
    public function main()
    {
        $data = $this->inPut('data');
        if (empty($data)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '缺少必要参数', '');
        }

        if (count($data) > 100) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '批量添加超过限制', '');
        }

        foreach ($data as $key => $value) {
            try {
                $this->dzqValidate($value, [
                    'id'       => 'required|int|min:1',
                    'status'   => 'required|int|in:1'
                ]);

                $report = Report::query()->findOrFail($value['id']);
                $report->status = $value['status'];
                $report->save();
            } catch (\Exception $e) {
                app('log')->info('requestId：' . $this->requestId . '-' . '修改举报反馈出错，举报ID为： "' . $value['id'] . '" 。错误信息： ' . $e->getMessage());
                $this->outPut(ResponseCode::INTERNAL_ERROR, '修改出错', [$e->getMessage(), $value]);
            }
        }

        $this->outPut(ResponseCode::SUCCESS, '', '');
    }
}
