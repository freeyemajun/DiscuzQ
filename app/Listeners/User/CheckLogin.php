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

namespace App\Listeners\User;

use App\Common\ResponseCode;
use App\Events\Users\Logining;
use App\Models\UserLoginFailLog;
use App\Repositories\UserLoginFailLogRepository;
use Carbon\Carbon;
use Discuz\Auth\Exception\LoginFailedException;
use Discuz\Auth\Exception\LoginFailuresTimesToplimitException;
use Discuz\Common\Utils;
use Discuz\Foundation\Application;
use Psr\Http\Message\ServerRequestInterface;

class CheckLogin
{
    protected $userLoginFailLog;

    protected $app;

    const FAIL_NUM = 5;

    const LIMIT_TIME = 15;

    protected $ip;

    protected $userLoginFailCount;

    public function __construct(UserLoginFailLogRepository $userLoginFailLog, Application $app)
    {
        $this->userLoginFailLog = $userLoginFailLog;
        $this->app = $app;

        $request = $this->app->make(ServerRequestInterface::class);
        $this->ip = ip($request->getServerParams());
    }

    /**
     * @param Logining $event
     * @throws LoginFailuresTimesToplimitException
     * @throws LoginFailedException
     */
    public function handle(Logining $event)
    {
        $this->checkLoginFailuresTimes($event->user->username);

        //password not match
        if ($event->password !== '' && !$event->user->checkPassword($event->password)) {
            $this->handleLoginFailuresTimes($event->user->id, $event->user->username);
        }
    }

    public function checkLoginFailuresTimes($username)
    {
        $this->userLoginFailCount = $this->userLoginFailLog->getCount($this->ip, $username);
        $maxTime = $this->userLoginFailLog->getLastFailTime($this->ip, $username);

        //set current count
        ++$this->userLoginFailCount;

        $expire = Carbon::parse($maxTime)->addMinutes(self::LIMIT_TIME);
        if ($this->userLoginFailCount > self::FAIL_NUM) {
            if ($expire > Carbon::now()) {
                Utils::outPut(ResponseCode::LOGIN_FAILED, '登录错误次数超出限制');
            } else {
                //reset fail count
                $this->userLoginFailCount = 1;
                UserLoginFailLog::reSetFailCountByIp($this->ip);
            }
        }
    }

    public function handleLoginFailuresTimes($userId, $username)
    {
        if ($this->userLoginFailCount == 1) {
            //first time set fail log
            UserLoginFailLog::writeLog($this->ip, $userId, $username);
        } else {
            //fail count +1
            UserLoginFailLog::setFailCountByIp($this->ip, $userId, $username);

            if ($this->userLoginFailCount == self::FAIL_NUM) {
                Utils::outPut(ResponseCode::LOGIN_FAILED, '登录错误次数超出限制');
            }
        }

        Utils::outPut(ResponseCode::LOGIN_FAILED, '登录失败，您还可以尝试'.(self::FAIL_NUM-$this->userLoginFailCount).'次');
    }
}
