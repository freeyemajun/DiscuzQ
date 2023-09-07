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

use App\Commands\Users\GenJwtToken;
use App\Commands\Users\RegisterQQUser;
use App\Common\ResponseCode;
use App\Events\Users\Logind;
use App\Models\SessionToken;
use App\Models\UserQq;
use App\Settings\SettingsRepository;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Support\Arr;
use Discuz\Contracts\Socialite\Factory;

class QQUserController extends AuthBaseController
{
    protected $socialite;

    protected $bus;

    protected $cache;

    protected $validation;

    protected $events;

    protected $setting;

    public function __construct(Factory $socialite, Dispatcher $bus, Repository $cache, ValidationFactory $validation, Events $events, SettingsRepository $settings)
    {
        $this->socialite = $socialite;
        $this->bus = $bus;
        $this->cache = $cache;
        $this->validation = $validation;
        $this->events = $events;
        $this->setting = $settings;
    }

    /**
     * @inheritDoc
     */
    public function main()
    {
        $sessionId = $this->inPut('sessionId');
        $accessToken = $this->inPut('accessToken');

        $request = $this->request->withAttribute('session', new SessionToken())
            ->withAttribute('sessionId', $sessionId)
            ->withAttribute('access_token', $accessToken);

        $this->validation->make([
            'access_token' => $accessToken,
            'sessionId' => $sessionId,
        ], [
            'access_token' => 'required',
            'sessionId' => 'required'
        ])->validate();
        $this->socialite->setRequest($request);
        $driver = $this->socialite->driver('qq');
        $user = $driver->user();
        $qqUser = UserQq::where('openid', $user->getId())->first();
        if (! $qqUser || ! $qqUser->user) {
            // 站点关闭注册
            if (!(bool)$this->setting->get('register_close')) {
                $this->outPut(ResponseCode::REGISTER_CLOSE);
            }
            //注册
            if (!$qqUser) {
                $preData['openid'] = $user->id;
                $preData['nickname'] = $user->nickname;
                $preData['sex'] = $user->sex;
                $preData['headimgurl'] = $user->avatar;
                $preData['province'] = !empty($user->user['province']) ? $user->user['province'] : '';
                $preData['city'] = !empty($user->user['city']) ? $user->user['city'] : '';

                $qqUser = UserQq::build(Arr::only(
                    $preData,
                    ['openid', 'nickname', 'sex', 'headimgurl', 'province', 'city']
                ));
                $qqUser->save();
            }
            $data['username'] = $qqUser->nickname;
            $data['register_ip'] = ip($this->request->getServerParams());
            $data['register_port'] = Arr::get($this->request->getServerParams(), 'REMOTE_PORT', 0);
            $registerUser = $this->bus->dispatch(
                new RegisterQQUser($this->request->getAttribute('actor'), $data)
            );
            $qqUser->user_id = $registerUser->id;
            $qqUser->save();
        }
        //创建 token
        // qq 登录token 依据
        $params = [
            'openid' => $qqUser->openid,
            'username' => $user->nickname,
            'password' => ''
        ];
        GenJwtToken::setUid($qqUser->user->id);
        $response = $this->bus->dispatch(
            new GenJwtToken($params)
        );
        if ($response->getStatusCode() === 200) {
            $this->events->dispatch(new Logind($qqUser->user));
        }

        $accessToken = json_decode($response->getBody());
        $result = $this->camelData(collect($accessToken));
        $result = $this->addUserInfo($qqUser->user, $result);
        $this->outPut(ResponseCode::SUCCESS, '', $result);
    }
}
