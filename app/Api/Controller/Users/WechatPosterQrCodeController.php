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
use App\Models\Group;
use App\Models\Invite;
use App\Models\SessionToken;
use App\Repositories\UserRepository;
use App\Settings\SettingsRepository;
use Carbon\Carbon;
use Discuz\Base\DzqLog;
use Discuz\Wechat\EasyWechatTrait;
use Endroid\QrCode\QrCode;
use GuzzleHttp\Client;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Str;

class WechatPosterQrCodeController extends AuthBaseController
{
    protected $settingsRepository;

    protected $httpClient;

    protected $accessToken;

    protected $url;

    public function __construct(SettingsRepository $settingsRepository, UrlGenerator $url)
    {
        $this->settingsRepository   = $settingsRepository;
        $this->url                  = $url;
        $this->httpClient           = new Client();
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        if (!$this->user->isAdmin()) {
            $this->outPut(ResponseCode::UNAUTHORIZED, '非管理员无权限访问海报二维码生成接口');
        }
        return true;
    }

    public function main()
    {
        try {
            $actor = $this->user;
            if (empty($actor->id)) {
                $this->outPut(ResponseCode::JUMP_TO_LOGIN);
            }

            $miniWechat = (bool)$this->settingsRepository->get('miniprogram_close', 'wx_miniprogram');
            $wechat     = (bool)$this->settingsRepository->get('offiaccount_close', 'wx_offiaccount');
            if (!$miniWechat && !$wechat) {
                $this->outPut(ResponseCode::OPEN_H5_MINI_SET);
            }

            $endTime = Carbon::now()->addDays(7);
            $invite = new Invite();
            $invite->group_id = Group::EXPERIENCE_ID;
            $invite->type = Invite::TYPE_GENERAL;
            $invite->code = Str::random(Invite::INVITE_GROUP_LENGTH);
            $invite->dateline = Carbon::now()->timestamp;
            $invite->endtime = $endTime->timestamp;
            $invite->user_id = $actor->id;
            $invite->save();

            $groups = $actor->groups->toArray();
            $timeRange = !empty($groups[0]['time_range']) ? $groups[0]['time_range'] : Group::DEFAULT_TIME_RANGE;
            $inviteData = [
                'timeRange' => $timeRange,
                'endTime' => $endTime->format('Y/m/d H:i:s'),
//                'inviteCode' => $invite->code
            ];

            if ($miniWechat) {
                //获取小程序全局token
                $app = $this->miniProgram();
//                $optional['page'] = 'userPages/user/wx-rebind-action/index';
                $optional['page'] = 'pages/index/index';
                $wxqrcodeResponse = $app->app_code->getUnlimit($invite->code, $optional);
                if (is_array($wxqrcodeResponse) && isset($wxqrcodeResponse['errcode']) && isset($wxqrcodeResponse['errmsg'])) {
                    //todo 日志记录
                    $this->outPut(ResponseCode::MINI_PROGRAM_QR_CODE_ERROR, $wxqrcodeResponse['errmsg']);
                }
                //图片二进制转base64
                $data = [
                    'base64Img' => 'data:image/png;base64,' . base64_encode($wxqrcodeResponse->getBody()->getContents())
                ];
                $data = array_merge($data, $inviteData);
                $this->outPut(ResponseCode::SUCCESS, '', $data);
            }

            if ($wechat) {
                $redirectUri = urldecode($this->inPut('redirectUri'));
                $conData = $this->parseUrlQuery($redirectUri);
                $redirectUri = $conData['url'];
                $locationUrl = $this->url->action(
                    '/apiv3/users/wechat/h5.oauth?redirect='.$redirectUri,
                    ['inviteCode' => $invite->code]
                );
                $locationUrlArr = explode('redirect=', $locationUrl);
                $locationUrl = $locationUrlArr[0].'redirect='.urlencode($locationUrlArr[1]);
                //去掉无参数时最后一个是 ? 的字符
                $locationUrl = rtrim($locationUrl, '?');

                $qrCode = new QrCode($locationUrl);

                $binary = $qrCode->writeString();

                $data = [
                    'base64Img' => 'data:image/png;base64,' . base64_encode($binary),
                ];
                $data = array_merge($data, $inviteData);
            }

            $this->outPut(ResponseCode::SUCCESS, '', $data);
        } catch (\Exception $e) {
            DzqLog::error('WechatPosterQrCodeController', [
                'redirectUri' => $this->inPut('redirectUri')
            ], $e->getMessage());
            $this->outPut(ResponseCode::INTERNAL_ERROR, '手机浏览器海报二维码生成接口异常');
        }
    }

    /**
     *
     * 从url 中分离出uri与参数
     * @param $url
     * @return mixed
     */
    protected function parseUrlQuery($url)
    {
        $urlParse = explode('?', $url);
        $data['url'] = $urlParse[0];
        $data['params'] = [];
        if (isset($urlParse[1]) && !empty($urlParse[1])) {
            $queryParts = explode('&', $urlParse[1]);
            $params = [];
            foreach ($queryParts as $param) {
                $item = explode('=', $param);
                $params[$item[0]] = $item[1];
            }
            $data['params'] = $params;
        }
        return $data;
    }
}
