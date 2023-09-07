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

namespace App\Api\Controller\Order;

use App\Common\ResponseCode;
use App\Repositories\UserRepository;
use App\Models\Order;
use Discuz\Base\DzqController;

class ResourceOrderController extends DzqController
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
        $user = $this->user;
        $order = Order::query()
            ->where([
                        'user_id' => $user->id,
                        'order_sn' => $this->inPut('orderSn'),
                    ])
            ->first();
        if (empty($order)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }
        $order = [
            'id' => $order->id,
            'orderSn' => (string) $order->order_sn,
            'amount' => $order->amount,
            'status' => $order->status,
            'type' => $order->type,
            'threadId' => $order->thread_id,
            'groupId' => $order->group_id,
            'updatedAt' => optional($order->updated_at)->format('Y-m-d H:i:s'),
            'createdAt' => optional($order->created_at)->format('Y-m-d H:i:s'),
        ];
        $this->outPut(ResponseCode::SUCCESS, '', $order);
    }
}
