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

namespace App\Api\Controller\Threads\Notify;

use App\Commands\Thread\Notify\ThreadVideoNotify;
use App\Common\ResponseCode;
use App\Repositories\UserRepository;
use Discuz\Base\DzqController;
use Illuminate\Contracts\Bus\Dispatcher;
use Psr\Http\Message\ServerRequestInterface;
use Discuz\Contracts\Setting\SettingsRepository;
use Illuminate\Support\Arr;

class ThreadVideoNotifyController extends DzqController
{
    /**
     * @var Dispatcher
    */
    protected $bus;

    protected $settings;

    /**
     * @param Dispatcher $bus
     */
    public function __construct(Dispatcher $bus, SettingsRepository $settings)
    {
        $this->bus = $bus;
        $this->settings = $settings;
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        return true;
    }

    public function main()
    {
        $dbtoken = $this->settings->get('qcloud_vod_token', 'qcloud');
        $inputtoken = Arr::get($this->queryParams, 'qvodtoken');
        if (empty($dbtoken) || (!empty($inputtoken) && strcmp($dbtoken, $inputtoken) === 0)) {
            $this->data($this->request);
            $this->outPut(ResponseCode::SUCCESS);
        } else {
            $this->outPut(ResponseCode::INTERNAL_ERROR, 'Ignored or forbidden.');
        }
    }

    public function data(ServerRequestInterface $request)
    {
        return $this->bus->dispatch(
            new ThreadVideoNotify($request->getParsedBody()->toArray())
        );
    }
}
