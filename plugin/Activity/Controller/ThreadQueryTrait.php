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

namespace Plugin\Activity\Controller;


use App\Common\Platform;
use App\Models\DenyUser;
use App\Models\Order;
use App\Models\Post;
use App\Models\Sequence;
use App\Models\Thread;
use Carbon\Carbon;
use Discuz\Common\Utils;

trait ThreadQueryTrait
{
    /**
     * @desc 普通筛选SQL
     * @param $filter
     * @param bool $withLoginUser
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildFilterThreads($filter, &$withLoginUser = false)
    {
        list($essence, $types, $sort, $attention, $search, $complex, $categoryids) = $this->initFilter($filter);
        $loginUserId = $this->user->id;
        $administrator = $this->user->isAdmin();
        $threads = $this->getBaseThreadsBuilder();
        if (!empty($complex)) {
            \Discuz\Common\Utils::setAppKey('thread_complex', $complex);
            switch ($complex) {
                case Thread::MY_DRAFT_THREAD:
                    $threads = $this->getBaseThreadsBuilder(Thread::IS_DRAFT, false)
                        ->where('th.user_id', $loginUserId)
                        ->orderByDesc('th.id');
                    $threads->join('posts as post', 'post.thread_id', '=', 'th.id');
                    break;
                case Thread::MY_LIKE_THREAD:
                    empty($filter['toUserId']) ? $userId = $loginUserId : $userId = intval($filter['toUserId']);
                    $threads->leftJoin('posts as post', 'post.thread_id', '=', 'th.id')
                        ->where(['post.is_first' => Post::FIRST_YES, 'post.is_approved' => Post::APPROVED_YES])
                        ->leftJoin('post_user as postu', 'postu.post_id', '=', 'post.id')
                        ->where(['postu.user_id' => $userId])
                        ->orderByDesc('postu.created_at');
                    break;
                case Thread::MY_COLLECT_THREAD:
                    $threads->leftJoin('thread_user as thu', 'thu.thread_id', '=', 'th.id')
                        ->where(['thu.user_id' => $loginUserId])
                        ->orderByDesc('thu.created_at');
                    break;
                case Thread::MY_BUY_THREAD:
                    $threads->leftJoin('orders as order', 'order.thread_id', '=', 'th.id')
                        ->whereIn('order.type', [Order::ORDER_TYPE_THREAD, Order::ORDER_TYPE_ATTACHMENT])
                        ->where(['order.user_id' => $loginUserId, 'order.status' => Order::ORDER_STATUS_PAID])
                        ->orderByDesc('order.updated_at');
                    break;
                case Thread::MY_OR_HIS_THREAD:
                    if (empty($filter['toUserId']) || $filter['toUserId'] == $loginUserId || $administrator) {
                        $threads = $this->getBaseThreadsBuilder(Thread::BOOL_NO, false);
                    } else {
                        $threads = $threads->where('th.is_anonymous', Thread::IS_NOT_ANONYMOUS);
                    }
                    empty($filter['toUserId']) ? $userId = $loginUserId : $userId = intval($filter['toUserId']);
                    //个人中心置顶
                    $threads->leftJoin('thread_sticks as thstick', function ($thjoin) {
                        $thjoin->on('th.id', '=', 'thstick.thread_id')->on('th.user_id', '=', 'thstick.user_id');
                    })->addSelect('thstick.status')->orderByDesc('thstick.status');  //置顶，该sql放第一位
                    $threads->where('th.user_id', $userId)->orderByDesc('th.id');
                    break;
            }
            $withLoginUser = true;
        }
        !empty($essence) && $threads = $threads->where('is_essence', $essence);
        if (!empty($types)) {
            $threads = $threads->leftJoin('thread_tag as tag', 'tag.thread_id', '=', 'th.id')
                ->whereIn('tag', $types);
        }
        if (!empty($search)) {
            $threads = $threads->leftJoin('posts as post', 'th.id', '=', 'post.thread_id')
                ->addSelect('post.content')
                ->where(['post.is_first' => Post::FIRST_YES, 'post.is_approved' => Post::APPROVED_YES])
                ->whereNull('post.deleted_at')
                ->where(function ($threads) use ($search) {
                    $threads->where('th.title', 'like', '%' . $search . '%');
                    $threads->orWhere('post.content', 'like', '%' . $search . '%');
                });
        }
        $this->buildThreadSort($threads, $sort);
        //关注
        if ($attention == 1 && !empty($this->user)) {
            $threads->leftJoin('user_follow as follow', 'follow.to_user_id', '=', 'th.user_id')
                ->where('th.is_anonymous', Thread::BOOL_NO)
                ->where('follow.from_user_id', $this->user->id);
            $withLoginUser = true;
        }
        //deny用户
        if (!empty($loginUserId)) {
            $denyUserIds = DenyUser::query()->where('user_id', $loginUserId)->get()->pluck('deny_user_id')->toArray();
            if (!empty($denyUserIds)) {
                $threads = $threads->whereNotIn('th.user_id', $denyUserIds);
                $withLoginUser = true;
            }
        }
        if (!empty($exclusiveIds)) {
            $threads = $threads->whereNotIn('th.id', $exclusiveIds);
        }
        !empty($categoryids) && $threads->whereIn('th.category_id', $categoryids);
        return $threads;
    }

    /**
     * @desc 智能排序SQL
     * @param $filter
     * @return bool|\Illuminate\Database\Eloquent\Builder
     */
    private function buildSequenceThreads($filter)
    {
        $sequence = Sequence::getSequence();
        if (empty($sequence)) {
            return $this->buildFilterThreads($filter);
        }
        list($essence, $types, $sort, $attention, $search, $complex, $categoryids0) = $this->initFilter($filter);
        $categoryids = !empty($sequence['category_ids']) ? explode(',', $sequence['category_ids']) : [];
        $groupIds = !empty($sequence['group_ids']) ? explode(',', $sequence['group_ids']) : [];
        $userIds = !empty($sequence['user_ids']) ? explode(',', $sequence['user_ids']) : [];
        $topicIds = !empty($sequence['topic_ids']) ? explode(',', $sequence['topic_ids']) : [];
        $threadIds = !empty($sequence['thread_ids']) ? explode(',', $sequence['thread_ids']) : [];
        $blockUserIds = !empty($sequence['block_user_ids']) ? explode(',', $sequence['block_user_ids']) : [];
        $blockTopicIds = !empty($sequence['block_topic_ids']) ? explode(',', $sequence['block_topic_ids']) : [];
        $blockThreadIds = !empty($sequence['block_thread_ids']) ? explode(',', $sequence['block_thread_ids']) : [];
        $threads = $this->getBaseThreadsBuilder();
        $threads->leftJoin('group_user as g1', 'g1.user_id', '=', 'th.user_id');
        $threads->leftJoin('thread_topic as topic', 'topic.thread_id', '=', 'th.id');
        !empty($categoryids0) && $threads->whereIn('th.category_id', $categoryids0);
        if (!empty($types)) {
            $threads->leftJoin('thread_tag as tag', 'tag.thread_id', '=', 'th.id')
                ->whereIn('tag.tag', $types);
        }
        $threads->where(function (\Illuminate\Database\Eloquent\Builder $threads) use ($categoryids, $groupIds, $userIds, $topicIds, $threadIds) {
            !empty($categoryids) && $threads->orWhereIn('th.category_id', $categoryids);
            !empty($groupIds) && $threads->orWhereIn('g1.group_id', $groupIds);
            !empty($userIds) && $threads->orWhereIn('th.user_id', $userIds);
            !empty($topicIds) && $threads->orWhereIn('topic.topic_id', $topicIds);
            !empty($threadIds) && $threads->orWhereIn('th.id', $threadIds);
        });
        if (!empty($blockUserIds)) {
            $threads->whereNotIn('th.user_id', $blockUserIds);
        }
        if (!empty($blockThreadIds)) {
            $threads->whereNotIn('th.id', $blockThreadIds);
        }
        if (!empty($blockTopicIds)) {
            $threads->where(function (\Illuminate\Database\Eloquent\Builder $threads) use ($blockTopicIds) {
                $threads->orWhereNull('topic.topic_id')->orWhereNotIn('topic.topic_id', $blockTopicIds);
            });
        }
        $this->buildThreadSort($threads, $sort);
        $threads->distinct('th.id');
        return $threads;
    }


    /**
     * @desc 发现页搜索结果数据
     * @param $filter
     * @param bool $withLoginUser
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildSearchThreads($filter, &$withLoginUser = false)
    {
        list($essence, $types, $sort, $attention, $search, $complex, $categoryids) = $this->initFilter($filter);
        $loginUserId = $this->user->id;
        $threadsByHot = $this->getBaseThreadsBuilder();
        if (!empty($search)) {
            $threadsByHot->leftJoin('posts as post', 'th.id', '=', 'post.thread_id')
                ->addSelect('post.content')
                ->where(['post.is_first' => Post::FIRST_YES, 'post.is_approved' => Post::APPROVED_YES])
                ->whereNull('post.deleted_at')
                ->where(function ($threads) use ($search) {
                    $threads->where('th.title', 'like', '%' . $search . '%');
                    $threads->orWhere('post.content', 'like', '%' . $search . '%');
                });
        }
        if (!empty($loginUserId)) {
            $denyUserIds = DenyUser::query()->where('user_id', $loginUserId)->get()->pluck('deny_user_id')->toArray();
            if (!empty($denyUserIds)) {
                $threadsByHot->whereNotIn('th.user_id', $denyUserIds);
                $withLoginUser = true;
            }
        }
        !empty($categoryids) && $threadsByHot->whereIn('th.category_id', $categoryids);

        $threadsByHot->whereBetween('th.created_at', [Carbon::parse('-7 days'), Carbon::now()])
            ->orderByDesc('th.view_count')->limit(10)->offset(0);
        $threadsByHotIds = $threadsByHot->get()->pluck('id');
        $threadsByUpdate = $this->getBaseThreadsBuilder();
        if (!empty($search)) {
            $threadsByUpdate->leftJoin('posts as post', 'th.id', '=', 'post.thread_id')
                ->addSelect('post.content')
                ->where(['post.is_first' => Post::FIRST_YES, 'post.is_approved' => Post::APPROVED_YES])
                ->whereNull('post.deleted_at')
                ->where(function ($threads) use ($search) {
                    $threads->where('th.title', 'like', '%' . $search . '%');
                    $threads->orWhere('post.content', 'like', '%' . $search . '%');
                });
        }
        if (!empty($loginUserId)) {
            $denyUserIds = DenyUser::query()->where('user_id', $loginUserId)->get()->pluck('deny_user_id')->toArray();
            if (!empty($denyUserIds)) {
                $threadsByUpdate->whereNotIn('th.user_id', $denyUserIds);
                $withLoginUser = true;
            }
        }
        $threadsByUpdate->whereNotIn('th.id', $threadsByHotIds);
        !empty($categoryids) && $threadsByUpdate->whereIn('th.category_id', $categoryids);
        $threadsByUpdate->orderByDesc('th.updated_at')->limit(9999999999);
        return $threadsByHot->unionAll($threadsByUpdate->getQuery());
    }


    /**
     * @desc 付费站首页帖子数据,最多显示10条
     */
    private function buildPaidHomePageThreads()
    {
        $maxCount = 10;
        $threadsBySite = $this->getBaseThreadsBuilder();
        $threadsBySite->where('th.is_site', Thread::IS_SITE);
        $threadsBySite->orderByDesc('th.view_count');
        if ($threadsBySite->count() >= $maxCount) {
            return $threadsBySite;
        }
        $threadsBySiteIds = $threadsBySite->get()->pluck('id');
        $threadsByHot = $this->getBaseThreadsBuilder();
        $threadsByHot->whereBetween('th.created_at', [Carbon::parse('-7 days'), Carbon::now()])
            ->whereNotIn('id', $threadsBySiteIds)
            ->orderByDesc('th.view_count')
            ->limit($maxCount)->offset(0);
        $threadsBySite->unionAll($threadsByHot->getQuery());
        return $threadsBySite;
    }

    private function getBaseThreadsBuilder($isDraft = Thread::BOOL_NO, $filterApprove = true)
    {
        $threads = Thread::query()
            ->select('th.*')
            ->from('threads as th')
            ->whereNull('th.deleted_at')
            ->whereNotNull('th.user_id')
            ->where('th.is_draft', $isDraft);
        if (Utils::requestFrom() == Platform::MinProgram) {
            $threads->where('th.is_display', Thread::BOOL_YES);
        }
        if ($filterApprove) {
            $threads->where('th.is_approved', Thread::BOOL_YES);
        }
        return $threads;
    }

    /**
     * @desc 筛选变量
     * @param $filter
     * @return array
     */
    private function initFilter($filter)
    {
        empty($filter) && $filter = [];
        $this->dzqValidate($filter, [
            'essence' => 'integer|in:0,1',
            'types' => 'array',
            'sort' => 'integer|in:1,2,3,4',
            'attention' => 'integer|in:0,1',
            'complex' => 'integer|in:1,2,3,4,5',
            'exclusiveIds' => 'array',
            'categoryids' => 'array'
        ]);
        $essence = '';
        $types = [];
        $sort = Thread::SORT_BY_THREAD;
        $attention = 0;
        $search = '';
        $complex = '';
        isset($filter['essence']) && $essence = $filter['essence'];
        isset($filter['types']) && $types = $filter['types'];
        isset($filter['sort']) && $sort = $filter['sort'];
        isset($filter['attention']) && $attention = $filter['attention'];
        isset($filter['search']) && $search = $filter['search'];
        isset($filter['complex']) && $complex = $filter['complex'];
        isset($filter['exclusiveIds']) && $exclusiveIds = $filter['exclusiveIds'];
        $categoryids = $this->categoryIds;
        return [$essence, $types, $sort, $attention, $search, $complex, $categoryids];
    }

    private function buildThreadSort(&$threads, $sort)
    {
        if (!empty($sort)) {
            switch ($sort) {
                case Thread::SORT_BY_THREAD://按照发帖时间排序
//                    $threads->orderByDesc('th.id');
                    $threads->orderByDesc('th.created_at');
                    break;
                case Thread::SORT_BY_POST://按照评论时间排序
                    $threads->orderByDesc('th.posted_at');
                    break;
                case Thread::SORT_BY_HOT://按照热度排序
                    $threads->whereBetween('th.created_at', [Carbon::parse('-7 days'), Carbon::now()]);
                    $threads->orderByDesc('th.view_count');
                    break;
                case Thread::SORT_BY_RENEW://按照更新时间排序
                    $threads->orderByDesc('th.updated_at');
                    break;
                default:
                    $threads->orderByDesc('th.created_at');
                    break;
            }
        }
    }
}

