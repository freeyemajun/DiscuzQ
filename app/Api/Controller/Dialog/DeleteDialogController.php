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

namespace App\Api\Controller\Dialog;

use App\Commands\Dialog\DeleteDialog;
use App\Common\ResponseCode;
use App\Models\Dialog;
use App\Repositories\UserRepository;
use Discuz\Base\DzqController;
use Illuminate\Contracts\Bus\Dispatcher;

class DeleteDialogController extends DzqController
{
    /**
     * @var Dispatcher
     */
    protected $bus;

    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        if ($this->user->isGuest()) {
            $this->outPut(ResponseCode::JUMP_TO_LOGIN);
        }
        return true;
    }

    public function main()
    {
        $user = $this->user;
        $id = $this->inPut('id');
        $dialogData = Dialog::query()->where('id', $id)->first();
        if (empty($dialogData)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }

        try {
            $this->bus->dispatch(
                new DeleteDialog($user, $id)
            );
        } catch (\Exception $e) {
            $this->outPut(ResponseCode::SUCCESS, $e->getMessage());
        }

        $this->outPut(ResponseCode::SUCCESS, '已删除');
    }
}
