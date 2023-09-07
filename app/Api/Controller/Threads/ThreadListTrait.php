<?php
/**
 * Copyright (C) 2021 Tencent Cloud.
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

use App\Common\CacheKey;
use App\Common\Utils;
use App\Models\PostUser;
use App\Models\ThreadUser;
use App\Models\ThreadUserStickRecord;
use Discuz\Base\DzqCache;
use App\Models\Attachment;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Order;
use App\Models\Post;
use App\Models\Thread;
use App\Models\ThreadTag;
use App\Models\ThreadTom;
use App\Models\ThreadVideo;
use App\Models\User;
use App\Modules\ThreadTom\TomConfig;

trait ThreadListTrait
{
    private function getFullThreadData($threads)
    {
        $loginUserId = $this->user->id;
        $userIds = array_unique(array_column($threads, 'user_id'));
        $groupUsers = $this->getGroupUserInfo($userIds);
        $users = DzqCache::hMGet(CacheKey::LIST_THREADS_V3_USERS, $userIds, function ($userIds) {
            return User::instance()->getUsers($userIds);
        }, 'id');
        $threadIds = array_column($threads, 'id');
        $posts = DzqCache::hMGet(CacheKey::LIST_THREADS_V3_POSTS, $threadIds, function ($threadIds) {
            return Post::instance()->getPosts($threadIds, false, false);
        }, 'thread_id');
        $postIds = array_column($posts, 'id');
        $toms = DzqCache::hMGet(CacheKey::LIST_THREADS_V3_TOMS, $threadIds, function ($threadIds) {
            return ThreadTom::query()->whereIn('thread_id', $threadIds)->where('status', ThreadTom::STATUS_ACTIVE)->get()->toArray();
        }, 'thread_id', true);

        $tags = DzqCache::hMGet(CacheKey::LIST_THREADS_V3_TAGS, $threadIds, function ($threadIds) {
            return ThreadTag::query()->whereIn('thread_id', $threadIds)->get()->toArray();
        }, 'thread_id', true);
        $inPutToms = $this->cacheThreadDetail($threadIds, $postIds, $posts, $toms);

        $this->setGlobalCache();
        $result = [];
        $concatString = '';
        $loginUserData = $this->getLoginUserData($loginUserId, $threadIds, $postIds);
        $userStickIds = [];
        if (\Discuz\Common\Utils::getAppKey('thread_complex') == Thread::MY_OR_HIS_THREAD) {
            $userStickIds = ThreadUserStickRecord::query()->whereIn('thread_id', $threadIds)->select('thread_id')->pluck('thread_id')->toArray();
        }
        foreach ($threads as $thread) {
            $threadId = $thread['id'];
            $userId = $thread['user_id'];
            $user = empty($users[$userId]) ? false : $users[$userId];
            $groupUser = empty($groupUsers[$userId]) ? false : $groupUsers[$userId];
            $post = empty($posts[$threadId]) ? false : $posts[$threadId];
            if ($post == false) {
                continue;
            }
            $tomInput = empty($inPutToms[$threadId]) ? false : $inPutToms[$threadId];
            $threadTags = [];
            isset($tags[$threadId]) && $threadTags = $tags[$threadId];
            $concatString .= ($thread['title'] . $post['content']);
            $result[] = $this->packThreadDetail($user, $groupUser, $thread, $post, $tomInput, false, $threadTags, $loginUserData, $userStickIds);
        }
        list($searches, $replaces) = ThreadHelper::getThreadSearchReplace($concatString);
        foreach ($result as &$item) {
            $item['title'] = str_replace($searches, $replaces, $item['title']);
        }
        return $result;
    }

    private function getLoginUserData($loginUserId, $threadIds, $postIds)
    {
        //付费订单
        $payOrders = [];
        //打赏订单
        $rewardOrders = [];
        Order::query()
            ->whereIn('thread_id', $threadIds)
            ->whereIn('type', [Order::ORDER_TYPE_THREAD, Order::ORDER_TYPE_ATTACHMENT, Order::ORDER_TYPE_REWARD])
            ->where([
                'user_id' => $loginUserId,
                'status' => Order::ORDER_STATUS_PAID
            ])->get()->each(function ($item) use (&$rewardOrders, &$payOrders) {
                $item = $item->toArray();
                if ($item['type'] == Order::ORDER_TYPE_REWARD) {
                    $rewardOrders[$item['thread_id']] = $item;
                } else {
                    $payOrders[$item['thread_id']] = $item;
                }
            });
        //我的点赞
        $postUsers = PostUser::query()->whereIn('post_id', $postIds)->where('user_id', $loginUserId)->get()->keyBy('post_id')->toArray();
        //我的收藏
        $threadUsers = ThreadUser::query()->whereIn('thread_id', $threadIds)->where('user_id', $loginUserId)->get()->keyBy('thread_id')->toArray();
        return [
            ThreadHelper::EXIST_PAY_ORDERS => $payOrders,
            ThreadHelper::EXIST_REWARD_ORDERS => $rewardOrders,
            ThreadHelper::EXIST_POST_USERS => $postUsers,
            ThreadHelper::EXIST_THREAD_USERS => $threadUsers
        ];
    }

    private function setGlobalCache()
    {
        $cache = [
            CacheKey::LIST_THREADS_V3_POST_USERS => DzqCache::get(CacheKey::LIST_THREADS_V3_POST_USERS),
            CacheKey::LIST_THREADS_V3_ATTACHMENT => DzqCache::get(CacheKey::LIST_THREADS_V3_ATTACHMENT),
            CacheKey::LIST_THREADS_V3_THREADS => DzqCache::get(CacheKey::LIST_THREADS_V3_THREADS),
            CacheKey::LIST_THREADS_V3_VIDEO => DzqCache::get(CacheKey::LIST_THREADS_V3_VIDEO),
        ];
        \Discuz\Common\Utils::setAppKey(CacheKey::APP_CACHE, $cache);
    }

    private function getGroupUserInfo($userIds)
    {
        $groups = array_column(Group::getGroups(), null, 'id');
        $groupUsers = DzqCache::hMGet(CacheKey::LIST_THREADS_V3_GROUP_USER, $userIds, function ($userIds) {
            return GroupUser::query()->whereIn('user_id', $userIds)->get()->toArray();
        }, 'user_id');
        foreach ($groupUsers as &$groupUser) {
            $groupUser['groups'] = $groups[$groupUser['group_id']];
        }
        return $groupUsers;
    }

    /**
     * @desc 未查询到的数据添加默认空值
     * @param $ids
     * @param $array
     * @param null $value
     * @return mixed
     */
    private function appendDefaultEmpty($ids, &$array, $value = null)
    {
//        foreach ($ids as $id) {
//            if (!isset($array[$id])) {
//                $array[$id] = $value;
//            }
//        }
//        return $array;
    }

    /**
     * @desc 公共数据
     * @param $threadsList
     */
    private function initDzqGlobalData($threadsList)
    {
        $threads = $this->getThreadsList($threadsList);
        $threadIds = array_column($threads, 'id');
        $posts = $this->cachePosts($threadIds);
        $postIds = array_column($posts, 'id');
        $userIds = array_unique(array_column($threads, 'user_id'));
        $this->cacheThreads($threadIds, $threads);
        $this->cacheUsers($userIds);
        $this->cacheGroupUser($userIds);
        $this->cacheTags($threadIds);
        $toms = $this->cacheToms($threadIds);
        $this->cacheThreadDetail($threadIds, $postIds, $posts, $toms);
    }

    private function cacheThreadDetail($threadIds, $postIds, $posts, $toms)
    {
        $attachmentIds = [];
        $threadVideoIds = [];
        $inPutToms = $this->buildInputToms($toms, $attachmentIds, $threadVideoIds, true);
        !empty($attachmentIds) && $this->cacheAttachment($attachmentIds);
        !empty($threadVideoIds) && $this->cacheVideo($threadVideoIds);
        $this->cachePostUsers($threadIds, $postIds, $posts);
        return $inPutToms;
    }

    /**
     * @desc 当前登录用户个性化数据
     * @param $loginUserId
     * @param $cacheKey
     * @param $filterKey
     * @param $preloadCount
     */
    private function initDzqUserData($loginUserId, $cacheKey, $filterKey, $preloadCount)
    {
//        $data = DzqCache::hGet($cacheKey, $filterKey);
//        if (!$data) {
//            return;
//        }
//        $pages = array_column($data, 'pageData');
//        $threadIds = [];
//        foreach ($pages as $ids) {
//            $threadIds = array_merge($threadIds, $ids);
//        }
//        $this->cacheUserOrders($loginUserId, $threadIds, $preloadCount);
//        $this->cachePostLikedAndFavor($loginUserId, $threadIds, $preloadCount);
    }

    private function getThreadsList($threadsByPage)
    {
        $threads = [];
        foreach ($threadsByPage as $listItems) {
            $pageData = $listItems['pageData'];
            foreach ($pageData as $thread) {
                $threads[] = $thread;
            }
        }
        return $threads;
    }

    private function buildInputToms($tomData, &$attachmentIds = [], &$threadVideoIds = [], $withIds = false)
    {
        $inPutToms = [];
        foreach ($tomData as $threadId => $toms) {
            foreach ($toms as $tom) {
                $value = json_decode($tom['value'], true);
                if ($withIds) {
                    switch ($tom['tom_type']) {
                        case TomConfig::TOM_IMAGE:
                            isset($value['imageIds']) && $attachmentIds = array_merge($attachmentIds, $value['imageIds']);
                            break;
                        case TomConfig::TOM_DOC:
                            isset($value['docIds']) && $attachmentIds = array_merge($attachmentIds, $value['docIds']);
                            break;
                        case TomConfig::TOM_VIDEO:
                            isset($value['videoId']) && $threadVideoIds[] = $value['videoId'];
                            break;
                        case TomConfig::TOM_AUDIO:
                            isset($value['audioId']) && $threadVideoIds[] = $value['audioId'];
                            break;
                    }
                }
                //如果是部分付费的话，将 price_ids 放进 body
                $priceIds = json_decode($tom['price_ids'], true);
                if ($tom['price_type'] && !empty($priceIds)) {
                    $value += ['priceIds' => $priceIds];
                }
                $inPutToms[$tom['thread_id']][$tom['key']] = $this->buildTomJson($tom['thread_id'], $tom['tom_type'], $this->SELECT_FUNC, $value);
            }
        }
        if ($withIds) {
            $attachmentIds = array_values(array_unique($attachmentIds));
            $threadVideoIds = array_values(array_unique($threadVideoIds));
        }
        return $inPutToms;
    }

    private function cacheUsers($userIds)
    {
        return DzqCache::hMGet(CacheKey::LIST_THREADS_V3_USERS, $userIds, function ($userIds) {
            return User::instance()->getUsers($userIds);
        }, 'id');
    }

    private function cacheGroupUser($userIds)
    {
        return DzqCache::hMGet(CacheKey::LIST_THREADS_V3_GROUP_USER, $userIds, function ($userIds) {
            return GroupUser::query()->whereIn('user_id', $userIds)->get()->toArray();
        }, 'user_id');
    }

    private function cacheTags($threadIds)
    {
        return DzqCache::hMGet(CacheKey::LIST_THREADS_V3_TAGS, $threadIds, function ($threadIds) {
            return ThreadTag::query()->whereIn('thread_id', $threadIds)->get()->toArray();
        }, 'thread_id');
    }

    private function cacheThreads($threadIds, $threads)
    {
        return DzqCache::hMGet(CacheKey::LIST_THREADS_V3_THREADS, $threadIds, function () use ($threads) {
            return $threads;
        }, 'id');
    }

    private function cachePosts($threadIds)
    {
        return DzqCache::hMGet(CacheKey::LIST_THREADS_V3_POSTS, $threadIds, function ($threadIds) {
            return Post::instance()->getPosts($threadIds);
        }, 'thread_id');
    }

    private function cacheToms($threadIds)
    {
        return DzqCache::hMGet(CacheKey::LIST_THREADS_V3_TOMS, $threadIds, function ($threadIds) {
            return ThreadTom::query()->whereIn('thread_id', $threadIds)->where('status', ThreadTom::STATUS_ACTIVE)->get()->toArray();
        }, 'thread_id', true);
    }

    private function cacheSearchReplace($threads, $posts)
    {
//        $linkString = '';
//        foreach ($threads as $thread) {
//            $threadId = $thread['id'];
//            $post = $posts[$threadId] ?? '';
//            $linkString .= ($thread['title'] . (!empty($post) ? $post['content'] : ''));
//        }
//        return Thread::instance()->getReplaceStringV3($linkString);
    }

    private function cacheAttachment($attachmentIds)
    {
//        $attachments = DzqCache::hMGet(CacheKey::LIST_THREADS_V3_ATTACHMENT,$attachmentIds,function ($attachmentIds){
//            $attachments = Attachment::query()->whereIn('id', $attachmentIds)->get()->keyBy('id')->toArray();
//            $attachments = $this->appendDefaultEmpty($attachmentIds, $attachments, null);
//            return $attachments;
//        });
        return DzqCache::hMGet(CacheKey::LIST_THREADS_V3_ATTACHMENT, $attachmentIds, function ($attachmentIds) {
            return Attachment::query()->whereIn('id', $attachmentIds)->get()->toArray();
        }, 'id');
//        $attachments = Attachment::query()->whereIn('id', $attachmentIds)->get()->keyBy('id')->toArray();
//        $attachments = $this->appendDefaultEmpty($attachmentIds, $attachments, null);
//        app('cache')->put(CacheKey::LIST_THREADS_V3_ATTACHMENT, $attachments);
//        return $attachments;
    }

    private function cacheVideo($threadVideoIds)
    {
        return DzqCache::hMGet(CacheKey::LIST_THREADS_V3_VIDEO, $threadVideoIds, function ($threadVideoIds) {
            return ThreadVideo::query()->whereIn('id', $threadVideoIds)->get()->toArray();
        }, 'id');
//        $threadVideos = ThreadVideo::query()->whereIn('id', $threadVideoIds)->get()->toArray();
//        $threadVideos = DzqCache::hMSet(CacheKey::LIST_THREADS_V3_VIDEO, $threadVideos, 'id', false, $threadVideoIds, null);
//        return $threadVideos;
    }


    private function cachePostUsers($threadIds, $postIds, $posts)
    {
        return DzqCache::hMGet(CacheKey::LIST_THREADS_V3_POST_USERS, $threadIds, function () use ($threadIds, $postIds, $posts) {
            return ThreadHelper::getThreadLikedDetail($threadIds, $postIds, $posts);
        });
//        $likedUsers = ThreadHelper::getThreadLikedDetail($threadIds, $postIds, $posts);
//        DzqCache::hMSet(CacheKey::LIST_THREADS_V3_POST_USERS, $likedUsers);
//        return $likedUsers;
    }

    private function cacheUserOrders($userId, $threadIds, $preloadCount)
    {
//        $key1 = CacheKey::LIST_THREADS_V3_USER_PAY_ORDERS . $userId;
//        $key2 = CacheKey::LIST_THREADS_V3_USER_REWARD_ORDERS . $userId;
//        $orders = Order::query()
//            ->where([
//                'user_id' => $userId,
//                'status' => Order::ORDER_STATUS_PAID
//            ])->whereIn('type', [Order::ORDER_TYPE_THREAD, Order::ORDER_TYPE_ATTACHMENT, Order::ORDER_TYPE_REWARD])
//            ->whereIn('thread_id', $threadIds)->get()->toArray();
//        $orderPay = [];
//        $orderReward = [];
//        foreach ($orders as $order) {
//            if ($order['type'] == Order::ORDER_TYPE_THREAD || $order['type'] == Order::ORDER_TYPE_ATTACHMENT) {
//                $orderPay[] = $order;
//            } else if ($order['type'] == Order::ORDER_TYPE_REWARD) {
//                $orderReward[] = $order;
//            }
//        }
//        DzqCache::hMGet($key1,$threadIds,function ()use($orderPay){
//            return $orderPay;
//        },'thread_id');
//        DzqCache::hMGet($key2,$threadIds,function ()use($orderReward){
//            return $orderReward;
//        },'thread_id');
//        DzqCache::hMSet($key1, $orderPay, 'thread_id', false, $threadIds, null);
//        DzqCache::hMSet($key2, $orderReward, 'thread_id', false, $threadIds, null);
    }

    //点赞收藏
    private function cachePostLikedAndFavor($userId, $threadIds)
    {
//        $key1 = CacheKey::LIST_THREADS_V3_POST_LIKED . $userId;
//        $key2 = CacheKey::LIST_THREADS_V3_THREAD_USERS . $userId;
//        $posts = Post::instance()->getPosts($threadIds);
//        $postIds = array_column($posts, 'id');
//
//        DzqCache::hMGet($key1,$postIds,function ()use($userId,$postIds){
//            return PostUser::query()->where('user_id', $userId)->whereIn('post_id', $postIds)->get()->toArray();
//        });
//        DzqCache::hMGet($key2,$threadIds,function ()use($userId,$threadIds){
//            return ThreadUser::query()->whereIn('thread_id', $threadIds)->where('user_id', $userId)->get()->toArray();
//        });

//        $postUsers = PostUser::query()->where('user_id', $userId)->whereIn('post_id', $postIds)->get()->toArray();
//        $favorite = ThreadUser::query()->whereIn('thread_id', $threadIds)->where('user_id', $userId)->get()->toArray();
//        DzqCache::hMSet($key1, $postUsers, 'post_id', false, $postIds, null);
//        DzqCache::hMSet($key2, $favorite, 'thread_id', false, $threadIds, null);
    }
}
