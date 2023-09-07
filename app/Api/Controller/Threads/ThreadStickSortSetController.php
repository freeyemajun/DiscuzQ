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

namespace App\Api\Controller\Threads;

use App\Common\ResponseCode;
use App\Models\Thread;
use App\Models\ThreadStickSort;
use Discuz\Base\DzqAdminController;

class ThreadStickSortSetController extends DzqAdminController
{
    use ThreadStickTrait;

    public function main()
    {
        $data = $this->inPut('data');
        if (empty($data)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '置顶帖数据不能为空');
        }

        $maxThreadStickNum = ThreadStickSort::THREAD_STICK_COUNT_LIMIT;
        if (count($data) > $maxThreadStickNum) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '置顶帖设置不允许大于'.$maxThreadStickNum.'条');
        }

        $insertData = [];
        foreach ($data as $val) {
            $threadId = $val['id'];
            $sort = $val['sort'];
            array_push($insertData, ['thread_id' => $threadId, 'sort' => $sort]);
        }

        ThreadStickSort::query()->delete();
        ThreadStickSort::query()->insert($insertData);

        $this->outPut(ResponseCode::SUCCESS, '置顶帖设置成功');
    }
}
