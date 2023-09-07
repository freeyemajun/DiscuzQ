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
use App\Common\Utils;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Traits\UserTrait;
use Discuz\Base\DzqAdminController;
use Discuz\Foundation\Application;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;

class ExportUserController extends DzqAdminController
{
    use UserTrait;

    protected $bus;

    protected $app;

    public function __construct(BusDispatcher $bus, Application $app)
    {
        $this->bus = $bus;
        $this->app = $app;
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        return $userRepo->canExportUser($this->user);
    }

    public function main()
    {
        $filter = $this->inPut('filter');
        $filters = $filter ?: [];
        $ids = $this->inPut('ids', '');
        $filters['id'] = $ids;
        $data= $this->ExportFilter($filters);
        $time = time();
        $filename = $this->app->config('excel.root') . DIRECTORY_SEPARATOR . "user_excel_{$time}.xlsx";

        $column_map = [
            'id' => '用户ID',
            'username' => '用户名',
            'mobile' => '手机号',
            'originalMobile' => '手机号',
            'status' => '帐号状态',
            'sex' => '性别',
            'groups' => '用户组名',
            'mp_openid' => '微信openid',
            'unionid' => '微信unionID',
            'nickname' => '微信昵称',
            'created_at' => '注册时间',
            'register_ip' => '注册IP',
            'register_port' => '注册端口',
            'login_at' => '最后登录时间',
            'last_login_ip' => '最后登录ip',
        ];

        Utils::localexport($filename, $data, $column_map);

    }

    public function ExportFilter($filters)
    {
        $userField = [
            'id',
            'username',
            'mobile',
            'login_at',
            'last_login_ip',
            'last_login_port',
            'register_ip',
            'register_port',
            'users.status',
            'users.created_at',
            'users.updated_at',
        ];
        $wechatField = [
            'user_id',
            'nickname',
            'sex',
            'mp_openid',
            'unionid',
        ];

        $columnMap = [
            'id',
            'username',
            'originalMobile',
            'status',
            'sex',
            'groups',
            'mp_openid',
            'unionid',
            'nickname',
            'created_at',
            'register_ip',
            'register_port',
            'login_at',
            'last_login_ip',
            'last_login_port',
        ];

        $query = User::query();

        // 拼接条件
        $this->applyFilters($query, $filters);

        $users = $query->with(['wechat' => function ($query) use ($wechatField) {
            $query->select($wechatField);
        }, 'groups' => function ($query) {
            $query->select(['id', 'user_id', 'name']);
        }])->get($userField);

        $sex = ['', '男', '女'];
        return $users->map(function (User $user) use ($columnMap, $sex) {
            // 前面加空格，避免科学计数法
            $user->originalMobile = ' ' . $user->getRawOriginal('mobile');
            $user->sex = $sex[$user->wechat ? $user->wechat->sex : 0];
            $user->status = User::$statusMap[$user->status] ?? '';
            if (!is_null($user->groups)) {
                $user->groups = $user->groups->pluck('name')->implode(',');
            }
            if (!is_null($user->wechat)) {
                $user->nickname = $user->wechat->nickname;
                $user->mp_openid = $user->wechat->mp_openid;
                $user->unionid = $user->wechat->unionid;
            }
            $user->unsetRelation('wechat');
            $user->unsetRelation('groups');
            return $user->only($columnMap);
        })->toArray();
    }

}
