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

namespace App\Api\Controller\Group;

use App\Models\Group;
use App\Common\ResponseCode;
use App\Repositories\UserRepository;
use Discuz\Base\DzqController;

class ListPayGroupsController extends DzqController
{
    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        if ($this->user->isGuest()) {
            $this->outPut(ResponseCode::JUMP_TO_LOGIN);
        }
        return true;
    }

    public function main()
    {
        $userGroup = $this->user->groups;
        $ugroup = $userGroup[0];

        //获取所有付费用户组
        $pay_groups = Group::query()->where('is_paid', 1)->orderBy('level', 'asc')->get();
        $res_groups = [];
        foreach ($pay_groups as $key => $val) {
            $res_groups[$key] = [
                'groupId' => $val->id,
                'name'  =>  $val->name,
                'color' =>  $val->color,
                'icon'  =>  $val->icon,
                'fee'   =>  $val->fee,
                'amount'   =>  (double)$val->fee,
                'level' =>  $val->level,
                'days'  =>  $val->days,
                'description'   =>  $val->description,
                'notice'    =>  $val->notice
            ];
            switch ($val->level) {
                // button ：0：不展示按钮、1：续费、2：升级
                case $val->level == $ugroup['level']:
                    $res_groups[$key]['button'] = 1;
                    break;
                case $val->level > $ugroup['level']:
                    $res_groups[$key]['button'] = 2;
                    break;
                default:
                    $res_groups[$key]['button'] = 0;
                    break;
            }
        }
        $this->outPut(ResponseCode::SUCCESS, '', $res_groups);
    }
}
