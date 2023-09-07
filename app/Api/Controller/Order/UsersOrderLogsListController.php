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
use App\Models\Order;
use App\Models\User;
use App\Models\Thread;
use App\Models\Post;
use Discuz\Base\DzqAdminController;
use Illuminate\Support\Str;

class UsersOrderLogsListController extends DzqAdminController
{
    public function main()
    {
        $currentPage = $this->inPut('page');
        $perPage = $this->inPut('perPage');
        $filter = (array)$this->inPut('filter');

        $query = Order::query();
        $query->select('orders.id as orderId', 'orders.user_id', 'orders.payee_id', 'orders.thread_id', 'users.nickname', 'orders.order_sn', 'orders.type', 'orders.amount', 'orders.status', 'orders.created_at');
        $query->join('users', 'orders.user_id', '=', 'users.id');
        if (isset($filter['orderSn']) && !empty($filter['orderSn'])) {
            $query->where('orders.order_sn', $filter['orderSn']);
        }

        if (isset($filter['type']) && is_numeric($filter['type'])) {
            $query->where('orders.type', $filter['type']);
        }

        if (isset($filter['status']) && is_numeric($filter['status'])) {
            $query->where('orders.status', $filter['status']);
        }

        if (isset($filter['startTime']) && !empty($filter['startTime'])) {
            $query->where('orders.created_at', '>=', $filter['startTime']);
        }

        if (isset($filter['endTime']) && !empty($filter['endTime'])) {
            $query->where('orders.created_at', '<=', $filter['endTime']);
        }

        // 发起方
        if (isset($filter['nickname']) && !empty($filter['nickname'])) {
            $query->where('users.nickname', 'like', '%' . $filter['nickname'] . '%');
        }

        // 收入方
        if (isset($filter['payeeNickname']) && !empty($filter['payeeNickname'])) {
            $payeeIds = User::query()->where('nickname', 'like', '%' . $filter['payeeNickname'] . '%')->pluck('id')->toArray();
            $query->whereIn('orders.payee_id', $payeeIds);
        }

        // 商品
        if (isset($filter['product']) && !empty($filter['product'])) {
            $product = $filter['product'];
            $query->when($product, function ($query, $product) {
                $query->whereIn(
                    'orders.thread_id',
                    Thread::query()
                        ->whereIn(
                            'id',
                            Post::query()->where('is_first', true)->where('content', 'like', "%$product%")->pluck('thread_id')
                        )
                        ->orWhere('threads.title', 'like', "%$product%")
                        ->pluck('id')
                );
            });
        }

        $query->orderByDesc('orders.created_at');
        $usersOrderLogs = $this->pagination($currentPage, $perPage, $query);

        $orders = $usersOrderLogs['pageData'];
        $orderThreadIds = array_column($orders, 'thread_id');
        $payeeUserIds = array_column($orders, 'payee_id');
        $payeeUserDatas = User::instance()->getUsers($payeeUserIds);
        $payeeUserDatas = array_column($payeeUserDatas, null, 'id');
        foreach ($orderThreadIds as $key => $value) {
            if (empty($value)) {
                unset($orderThreadIds[$key]);
            }
        }
        $orderThreadIds = array_merge($orderThreadIds);
        $threadData = $this->getThreadsBuilder($orderThreadIds);
        $threadData = array_column($threadData, null, 'threadId');
        foreach ($orders as $key => $value) {
            $orders[$key]['payeeNickname'] = $payeeUserDatas[$value['payee_id']]['nickname'] ?? '';
            $orders[$key]['thread'] = $threadData[$value['thread_id']] ?? ['title' => '暂无订单商品内容'];
            if (empty($orders[$key]['thread']['title'])) {
                if (isset($orders[$key]['thread']['content']) && !empty($orders[$key]['thread']['content'])) {
                    if (mb_strlen($orders[$key]['thread']['content']) > Thread::ORDER_TITLE_LENGTH) {
                        $orders[$key]['thread']['title'] = Str::substr(strip_tags($orders[$key]['thread']['content']), 0, Thread::ORDER_TITLE_LENGTH) . '...';
                    } else {
                        $orders[$key]['thread']['title'] = strip_tags($orders[$key]['thread']['content']) ?: '暂无订单商品内容';
                    }
                } else {
                    $orders[$key]['thread']['title'] = '暂无订单商品内容';
                }
            }
        }

        $usersOrderLogs['pageData'] = $this->camelData($orders) ?? [];
        $this->outPut(ResponseCode::SUCCESS, '', $usersOrderLogs);
    }

    private function getThreadsBuilder($orderThreadIds)
    {
        return Thread::query()
            ->select('threads.id as threadId', 'threads.user_id', 'threads.title', 'posts.content')
            ->join('posts', 'threads.id', '=', 'posts.thread_id')
            ->where('posts.is_first', 1)
            ->whereIn('threads.id', $orderThreadIds)
            ->get()->toArray();
    }
}
