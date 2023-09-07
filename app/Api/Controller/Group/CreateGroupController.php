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

use App\Common\ResponseCode;
use App\Commands\Group\CreateGroup;
use App\Models\Group;
use App\Repositories\UserRepository;
use Discuz\Base\DzqAdminController;
use Illuminate\Contracts\Bus\Dispatcher;

class CreateGroupController extends DzqAdminController
{
    protected $bus;

    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        return $userRepo->canCreateGroup($this->user);
    }

    public function main()
    {
        $actor = $this->user;

        $group = [
            'id' => $this->inPut('id'),
            'name' => $this->inPut('name'),
            'type' => $this->inPut('type')  ,
            'color' => $this->inPut('color'),
            'icon' => $this->inPut('icon'),
            'default' => $this->inPut('default'),
            'isDisplay' => $this->inPut('isDisplay'),
            'isPaid' => $this->inPut('isPaid'),
            'fee' => $this->inPut('fee'),
            'days' => $this->inPut('days'),
            'scale' => $this->inPut('scale'),
            'isSubordinate' => $this->inPut('isSubordinate'),
            'isCommission' => $this->inPut('isCommission'),
            'level' => $this->inPut('level'),
            'description' => $this->inPut('description'),
            'notice' => $this->inPut('notice'),
        ];


        if (empty($group['name'])) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '');
        }

        $this->dzqValidate($group, [
            'name'=> 'required_without|max:10',
            'description'=> 'max:20',
            'notice'=> 'max:200',
        ]);

        if (!empty($group['isPaid']) && $group['isPaid']==Group::IS_PAID) {
            if ($group['default'] == 'true') {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '付费组，不可设置为默认组');
            }
            if ($group['fee'] <= 0) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '付费组，费用错误');
            }
            if ($group['days'] <= 0) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '付费组，天数错误');
            }
            //检查level
            if ($group['level'] <= 0 || $group['level'] > 5) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '付费组，级别错误');
            }
            //检查该等级是否存在
            if (Group::query()->where('level', $group['level'])->first()) {
                $this->outPut(ResponseCode::RESOURCE_EXIST, '付费组，级别已存在');
            }

            //检查付费组个数
            $payGroupNum = Group::query()->where('is_paid', Group::IS_PAID)->count();
            if ($payGroupNum >= Group::PAID_GROUPS_NUM) {
                $this->outPut(ResponseCode::RESOURCE_EXIST, '付费组，个数已达上限');
            }
        }

        if (Group::query()->where('name', $group['name'])->first()) {
            $this->outPut(ResponseCode::RESOURCE_EXIST, '');
        }

        $result = $this->bus->dispatch(
            new CreateGroup($actor, $group)
        );
        $data = $this->camelData($result);
        $this->outPut(ResponseCode::SUCCESS, '', $data);
    }
}
