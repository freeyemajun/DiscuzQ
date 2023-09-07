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
use App\Models\SessionToken;
use App\Repositories\UserRepository;
use App\User\MiniprogramSchemeManage;
use Discuz\Base\DzqLog;
use Discuz\Wechat\EasyWechatTrait;
use GuzzleHttp\Client;

class MiniProgramParamSchemeGenController extends AuthBaseController
{
    /**
     * scheme跳转路由类型与路由映射
     * @var string[]
     */
    //todo 对接前端时更换路由
    public static $schemeTypeAndRouteMap = [
        'bind_mini'     => 'userPages/user/wx-bind/index',      // 绑定
        'share_mini'    => 'pages/index/index'                  // 分享
    ];

    protected $httpClient;

    protected $manage;

    public function __construct(MiniprogramSchemeManage $manage)
    {
        $this->httpClient = new Client();
        $this->manage = $manage;
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        return true;
    }

    public function main()
    {
        try {
            $type = $this->inPut('type');
            $query = !empty($this->inPut('query')) ? $this->inPut('query') : [];
            if (! in_array($type, array_keys(self::$schemeTypeAndRouteMap))) {
                $this->outPut(ResponseCode::GEN_SCHEME_TYPE_ERROR);
            }
            if (empty($query['scene'])) {
                if (! $this->user->isGuest()) {
                    $accessToken = $this->getAccessToken($this->user);
                    $scope = '';
                    if ($type == 'share_mini') {
                        $scope = SessionToken::WECHAT_MINIPROGRAM_SCHEME_SHARE;
                    } elseif ($type == 'bind_mini') {
                        $scope = SessionToken::WECHAT_MINIPROGRAM_SCHEME_BIND;
                    }
                    $token = SessionToken::generate(
                        $scope,
                        $accessToken,
                        $this->user->id
                    );
                    $token->save();
                    $query['scene'] = $token->token;
                } else {
                    $this->outPut(ResponseCode::INVALID_PARAMETER, '用户不存在', ['id' => $this->user->id]);
                }
            }

            //跳转路由选择
            $path = self::$schemeTypeAndRouteMap[$type];
            $app = $this->miniProgram();
            $globalAccessToken = $app->access_token->getToken(true);
            if (! isset($globalAccessToken['access_token'])) {
                //todo 记录错误日志
                DzqLog::error(get_class(), ['globalAccessToken' => $globalAccessToken]);
                $this->outPut(ResponseCode::MINI_PROGRAM_GET_ACCESS_TOKEN_ERROR);
            }

            $miniProgramScheme = $this->manage->getMiniProgramParamSchemeRefresh(
                $type,
                $globalAccessToken['access_token'],
                $path,
                http_build_query($query)
            );

            if ($miniProgramScheme == $type) {
                $code = ResponseCode::GEN_SCHEME_TYPE_ERROR;
                if ($miniProgramScheme == 'share_mini') {
                    $code = ResponseCode::GEN_SHARE_SCHEME_TYPE_ERROR;
                } elseif ($miniProgramScheme == 'bind_mini') {
                    $code = ResponseCode::GEN_BIND_SCHEME_TYPE_ERROR;
                }
                $this->outPut($code, '', [
                    'path'          => $path,
                    'query'         => http_build_query($query)
                ]);
            }

            $data['openLink'] = $miniProgramScheme;
            $this->outPut(ResponseCode::SUCCESS, '', $data);
        } catch (\Exception $e) {
            DzqLog::error(get_class(), [
                'type' => $this->inPut('type'),
                'query' => $this->inPut('query')
            ], $e->getMessage());
            $this->outPut(ResponseCode::INTERNAL_ERROR, '小程序参数SchemeGen接口异常', [$e->getMessage()]);
        }
    }
}
