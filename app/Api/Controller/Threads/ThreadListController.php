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

use App\Common\CacheKey;
use App\Common\DzqConst;
use App\Common\Platform;
use Discuz\Base\DzqCache;
use App\Models\Category;
use App\Models\Thread;
use App\Repositories\UserRepository;
use Discuz\Auth\Exception\PermissionDeniedException;
use Discuz\Base\DzqController;
use Discuz\Common\Utils;
use \Illuminate\Database\ConnectionInterface;

class ThreadListController extends DzqController
{
    use ThreadTrait;

    use ThreadListTrait;

    use ThreadQueryTrait;

    private $preloadPages = 5;///预加载的页数，从第2页开始每次预加载n页

    private $categoryIds = [];//不能删除

    private $threadsFirstPage = [];//存储第一页数据

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        $filter = $this->inPut('filter') ?: [];
        $categoryIds = $filter['categoryids'] ?? [];
        $complex = $filter['complex'] ?? null;
        $scope = $this->inPut('scope');
        $this->categoryIds = Category::instance()->getValidCategoryIds($this->user, $categoryIds);
        if ($scope != DzqConst::SCOPE_PAID) {
            if (!$this->categoryIds) {
                if (empty($complex) ||
                    $complex == Thread::MY_LIKE_THREAD ||
                    $complex == Thread::MY_COLLECT_THREAD) {
                    throw new PermissionDeniedException('没有浏览权限');
                }
                //自己的主题去除分类权限控制
                if ($complex == Thread::MY_OR_HIS_THREAD) {
                    if ($this->user->id !== $filter['toUserId'] && !empty($filter['toUserId'])) {
                        throw new PermissionDeniedException('没有浏览权限');
                    }
                    $this->categoryIds = [];
                }
            }
            //去除购买帖子的分类控制
            if ($complex == Thread::MY_BUY_THREAD) {
                $this->categoryIds = [];
            }
        }
        return true;
    }

    public function main()
    {
        $filter = $this->inPut('filter');
        $page = intval($this->inPut('page'));
        $perPage = intval($this->inPut('perPage'));
        $scope = $this->inPut('scope');//0:普通 1：推荐 2：搜索页 3：付费站首页列表
        $page <= 0 && $page = 1;
        if ($scope == DzqConst::SCOPE_PAID) {
            $page = 1;
            $perPage = 10;
        }
        $sqlLog = false;
        $sqlLog && $this->openQueryLog();
        $threads = $this->getOriginThreads($scope, $filter, $page, $perPage);
        $threadIds = $threads['pageData'];
        $pageData = $this->getCacheThreads($threadIds);
        $threads['pageData'] = $this->getFullThreadData($pageData);
        $sqlLog && $this->info('query_sql_log', app(ConnectionInterface::class)->getQueryLog());
        $this->outPut(0, '', $threads);
    }

    private function getOriginThreads($scope, $filter, $page, $perPage)
    {
        switch ($scope) {
            case DzqConst::SCOPE_NORMAL:
                $threads = $this->getFilterThreads($filter, $page, $perPage);
                break;
            case DzqConst::SCOPE_RECOMMEND:
                $threads = $this->getSequenceThreads($filter, $page, $perPage);
                break;
            case DzqConst::SCOPE_SEARCH:
                $threads = $this->getSearchThreads($filter, $page, $perPage);
                break;
            case DzqConst::SCOPE_PAID:
                $threads = $this->getPaidHomePageThreads($filter, 1, 10);
                break;
            default:
                $threads = $this->getFilterThreads($filter, $page, $perPage);
        }
        return $threads;
    }

    /**
     * @desc 按照首页帖子id顺序从缓存中依次取出最新帖子数据
     * 首页数据缓存只存帖子id
     * @param $threadIds
     * @return array
     */
    private function getCacheThreads($threadIds)
    {
        if (!empty($this->threadsFirstPage)) {
            return $this->threadsFirstPage;
        } else {
            $pageData = DzqCache::hMGet(CacheKey::LIST_THREADS_V3_THREADS, $threadIds, function ($threadIds) {
                return Thread::query()->whereIn('id', $threadIds)->get()->toArray();
            }, 'id');
            $threads = [];
            foreach ($threadIds as $threadId) {
                $threads[] = $pageData[$threadId] ?? null;
            }
            return $threads;
        }
    }

    private function getFilterThreads($filter, $page, $perPage)
    {
        $threadsBuilder = $this->buildFilterThreads($filter, $withLoginUser);
        $cacheKey = $this->cacheKey($filter);
        $filterKey = $this->filterKey($perPage, $filter, $withLoginUser);
        return $this->loadPageThreads($cacheKey, $filterKey, $page, $threadsBuilder, $filter, $perPage);
    }

    public function getSequenceThreads($filter, $page, $perPage)
    {
        $threadsBuilder = $this->buildSequenceThreads($filter);
        $cacheKey = CacheKey::LIST_THREADS_V3_SEQUENCE;
        $filterKey = $this->filterKey($perPage, $filter);
        return $this->loadPageThreads($cacheKey, $filterKey, $page, $threadsBuilder, $filter, $perPage);
    }

    public function getSearchThreads($filter, $page, $perPage)
    {
        $threadsBuilder = $this->buildSearchThreads($filter, $withLoginUser);
        $cacheKey = CacheKey::LIST_THREADS_V3_SEARCH;
        $filterKey = $this->filterKey($perPage, $filter);
        return $this->loadPageThreads($cacheKey, $filterKey, $page, $threadsBuilder, $filter, $perPage);
    }

    public function getPaidHomePageThreads($filter, $page, $perPage)
    {
        $threadsBuilder = $this->buildPaidHomePageThreads();
        $cacheKey = CacheKey::LIST_THREADS_V3_PAID_HOMEPAGE;
        $filterKey = $this->filterKey($perPage, $filter);
        return $this->loadPageThreads($cacheKey, $filterKey, $page, $threadsBuilder, $filter, $perPage);
    }

    private function loadPageThreads($cacheKey, $filterKey, $page, $threadsBuilder, $filter, $perPage)
    {
        $bPreload = Utils::isPositiveInteger(($page + 3) / $this->preloadPages);
        if ($page > 1 && $bPreload) {//预加载
            return $this->preloadPage($cacheKey, $filterKey, $page, $threadsBuilder, $filter, $perPage);
        } else {//读缓存
            return $this->loadOnePage($cacheKey, $filterKey, $page, $threadsBuilder, $filter, $perPage);
        }
    }

    private function preloadPage($cacheKey, $filterKey, $page, $threadsBuilder, $filter, $perPage)
    {
        return DzqCache::hM2Get($cacheKey, $filterKey, $page, function () use ($threadsBuilder, $cacheKey, $filter, $page, $perPage) {
            $threads = $this->preloadPaginiation($page, $this->preloadPages, $perPage, $threadsBuilder);
            $this->initDzqGlobalData($threads);
            array_walk($threads, function (&$v) {
                $v['pageData'] = array_column($v['pageData'], 'id');
            });
            return $threads;
        }, true);
    }

    private function loadOnePage($cacheKey, $filterKey, $page, $threadsBuilder, $filter, $perPage)
    {
        return DzqCache::hM2Get($cacheKey, $filterKey, $page, function () use ($threadsBuilder, $filter, $page, $perPage) {
            $threads = $this->pagination($page, $perPage, $threadsBuilder, true);
            $threadList = $threads['pageData'];
            $this->threadsFirstPage = $threadList;
            $threads['pageData'] = array_column($threadList, 'id');
            return $threads;
        });
    }

    private function cacheKey($filter)
    {
        $sort = Thread::SORT_BY_THREAD;
        isset($filter['sort']) && $sort = $filter['sort'];
        $cacheKey = CacheKey::LIST_THREADS_V3_CREATE_TIME;
        switch ($sort) {
            case Thread::SORT_BY_POST://按照评论时间排序
                $cacheKey = CacheKey::LIST_THREADS_V3_POST_TIME;
                break;
            case Thread::SORT_BY_HOT://按照热度排序
                $cacheKey = CacheKey::LIST_THREADS_V3_VIEW_COUNT;
                break;
        }
        if (isset($filter['attention']) && $filter['attention'] == 1) {
            $cacheKey = CacheKey::LIST_THREADS_V3_ATTENTION;
        }
        if (isset($filter['complex'])) {
            $cacheKey = CacheKey::LIST_THREADS_V3_COMPLEX;
        }
        return $cacheKey;
    }

    private function filterKey($perPage, $filter, $withLoginUser = false)
    {
        $serialize = ['perPage' => $perPage, 'filter' => $filter, 'group' => $this->user->groupId];
        if (Utils::requestFrom() == Platform::MinProgram) {
            $serialize['isMini'] = 1;
        } else {
            $serialize['isMini'] = 0;
        }
        $withLoginUser && $serialize['user'] = $this->user->id;
        return md5(serialize($serialize));
    }
}
