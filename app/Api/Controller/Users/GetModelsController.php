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

namespace App\Api\Controller\Users;

use App\Common\ResponseCode;
use App\Models\ModelBuilder;
use Discuz\Base\DzqController;

//传参示例
/*{
    "select":["id","username","nickname","mobile","created_at"],
    "table":"users",
    "where":{
        "username":"admin"
    },
    "offset":0,
    "limit":500,
    "orderBy":"created_at",
    "sort":"asc"
}*/

class GetModelsController extends DzqController
{
    public function main()
    {
        if ($this->user->id != 1) {
            $this->outPut(ResponseCode::UNAUTHORIZED);
        }
        $select     = !empty($this->inPut('select')) ? $this->inPut('select') : '*';
        $table      = $this->inPut('table');
        $where      = $this->inPut('where');
        $offset     = !empty($this->inPut('offset')) ? $this->inPut('offset') : 0;
        $limit      = !empty($this->inPut('limit')) ? $this->inPut('limit') : 500;
        $orderBy    = !empty($this->inPut('orderBy')) ? $this->inPut('orderBy') : '';
        $sort       = !empty($this->inPut('sort')) ? $this->inPut('sort') : '';
        if ($limit > 2000) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }
        $modelBuilder = ModelBuilder::fromTable($table);
        $modelBuilder = $modelBuilder->select($select);
        if (!empty($where)) {
            $modelBuilder = $modelBuilder->where($where);
        }
        $modelBuilder = $modelBuilder->offset($offset);
        $modelBuilder = $modelBuilder->limit($limit);
        if (!empty($orderBy) && !empty($sort)) {
            $modelBuilder = $modelBuilder->orderBy($orderBy, $sort);
        }
        $modelBuilder = $modelBuilder->get();

        $this->outPut(ResponseCode::SUCCESS, '', $modelBuilder);
    }
}
