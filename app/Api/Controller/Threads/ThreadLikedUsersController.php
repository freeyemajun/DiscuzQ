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

namespace App\Api\Controller\Threads;

#帖子点赞打赏用户列表
use App\Common\Utils;
use App\Common\ResponseCode;
use App\Models\Post;
use App\Models\Thread;
use App\Models\PostUser;
use App\Models\User;
use App\Models\Order;
use App\Repositories\UserRepository;
use Discuz\Base\DzqController;

class ThreadLikedUsersController extends DzqController
{
    private $thread;

    public function checkRequestPermissions(UserRepository $userRepo)
    {
        $this->thread = Thread::query()
            ->where('id', $this->inPut('threadId'))
            ->first(['user_id', 'price', 'category_id']);
        if (!$this->thread) {
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND);
        }
        return $userRepo->canViewUser($this->user);
    }

    public function main()
    {
        $data = [
            'threadId' => $this->inPut('threadId'),
            'perPage' => $this->inPut('perPage') ? $this->inPut('perPage') : 10,
            'page' => $this->inPut('page') ? $this->inPut('page') : 0,
            'type' => $this->inPut('type') ? $this->inPut('type') : 0
        ];

        $this->dzqValidate($data, [
            'threadId' => 'required|int',
            'type' => 'required|integer|in:0,1,2'
        ]);

        $thread = Thread::query()->where('id', $data['threadId'])->first(['price','attachment_price']);

        $post = Post::query()
            ->where(['thread_id' => $data['threadId'], 'is_first' => Post::FIRST_YES])
            ->whereNull('deleted_at')
            ->first();
        if (empty($post)) {
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND, '帖子详情不存在');
        }
        $data['postId'] = $post['id'];


        $postUser = PostUser::query()
            ->where('post_id', $data['postId'])
            ->orderBy('created_at', 'desc')
            ->get(['user_id','created_at'])
            ->map(function ($value) {
                $value->type = 1;
                return $value;
            })
            ->toArray();

        $orderType = [];
        if (in_array($data['type'], [0, 2])) {
            $orderType = [Order::ORDER_TYPE_REWARD];
        }
        if ($thread['price']  > 0) {
            $orderType = array_merge($orderType, [Order::ORDER_TYPE_THREAD]);
        } elseif ($thread['attachment_price']  > 0) {
            $orderType = array_merge($orderType, [Order::ORDER_TYPE_ATTACHMENT]);
        }

        $paidCount = 0;
        $rewardCount = 0;
        $order = Order::query()->where('thread_id', $data['threadId'])
            ->whereIn('type', $orderType)
            ->where('status', Order::ORDER_STATUS_PAID)
            ->orderBy('created_at', 'desc')
            ->get(['user_id','created_at','type'])
            ->map(function ($value) use (&$paidCount, &$rewardCount) {
                if (in_array($value->type, [Order::ORDER_TYPE_THREAD, Order::ORDER_TYPE_ATTACHMENT])) {
                    $value->type = 2;
                    $paidCount++;
                } else {
                    $value->type = 3;
                    $rewardCount++;
                }
                return $value;
            })
            ->toArray();

        if (empty($data['type'])) {
            $postUserAndorder = array_merge($postUser, $order);
        } elseif ($data['type'] == 1) {
            $postUserAndorder = $postUser;
        } else {
            $postUserAndorder = $order;
        }

        $postUserIds = array_column($postUserAndorder, 'user_id');
        $user = User::query()->whereIn('id', array_unique($postUserIds))->get(['id','nickname','avatar'])->toArray();
        $userArr = array_combine(array_column($user, 'id'), $user);
        $likeSort = $this->arraySort($postUserAndorder, 'created_at', 'desc');
        foreach ($likeSort as $k=>$v) {
            if (!empty($v['user_id']) && !empty($userArr[$v['user_id']])) {
                $likeSort[$k]['passed_at'] = Utils::diffTime($v['created_at']);
                $likeSort[$k]['nickname'] = $userArr[$v['user_id']]['nickname'];
                $likeSort[$k]['avatar'] = $userArr[$v['user_id']]['avatar'];
            }
        }

        $pageData = $this->specialPagination($data['page'], $data['perPage'], $likeSort);

        $pageData['pageData'] = [
            'allCount' => count($postUser) + count($order),
            'likeCount' => count($postUser),
            'rewardCount' => $rewardCount,
            'raidCount' => $paidCount,
            'list' => $this->camelData($pageData['pageData'])
        ];

        $this->outPut(ResponseCode::SUCCESS, '', $pageData);
    }

    public function arraySort($arr, $keys, $type = 'asc')
    {
        $keysvalue = $new_array = [];
        foreach ($arr as $k => $v) {
            $keysvalue[$k] = $v[$keys];
        }
        $type == 'asc' ? asort($keysvalue) : arsort($keysvalue);
        reset($keysvalue);
        foreach ($keysvalue as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }
}
