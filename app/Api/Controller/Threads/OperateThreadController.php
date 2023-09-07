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

use App\Commands\Thread\EditThread;
use App\Common\CacheKey;
use App\Common\ResponseCode;
use App\Models\Thread;
use App\Models\ThreadStickSort;
use App\Models\User;
use App\Repositories\UserRepository;
use Discuz\Auth\AssertPermissionTrait;
use Discuz\Auth\Exception\PermissionDeniedException;
use Discuz\Base\DzqCache;
use Discuz\Base\DzqController;
use Discuz\Common\Utils;
use Illuminate\Contracts\Bus\Dispatcher;

class OperateThreadController extends DzqController
{
    use AssertPermissionTrait;

    use ThreadStickTrait;

    protected $bus;

    public $providers = [
    ];

    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    // 权限检查
    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        $actor = $this->user;
        $isSticky = $this->inPut('isSticky');
        $isPoster = $this->inPut('isPoster'); //海报
        $isEssence = $this->inPut('isEssence');
        $isFavorite = $this->inPut('isFavorite');
        $isDeleted = $this->inPut('isDeleted');

        if ($actor->isGuest()) {
            $this->outPut(ResponseCode::JUMP_TO_LOGIN);
        }
        if ($actor->status == User::STATUS_NEED_FIELDS) {
            $this->outPut(ResponseCode::JUMP_TO_SIGIN_FIELDS);
        }
        if ($actor->status == User::STATUS_MOD) {
            $this->outPut(ResponseCode::JUMP_TO_AUDIT);
        }

        if (
            (!empty($isSticky) || $isSticky === 0 || is_bool($isSticky))
            && !$userRepo->canStickThread($actor)
        ) {
            throw new PermissionDeniedException('没有置顶权限');
        }
        $thread_id = $this->inPut('id');
        $thread = Thread::getOneActiveThread($thread_id);
        if (
            (!empty($isEssence) || $isEssence === 0 || is_bool($isEssence))
            && !$userRepo->canEssenceThread($actor, $thread)
        ) {
            throw new PermissionDeniedException('没有加精权限');
        }
        if (
            (!empty($isPoster) || $isPoster === 0 || is_bool($isPoster))
            && !$userRepo->canPosterThread($actor, $thread)
        ) {
            throw new PermissionDeniedException('没有海报权限');
        } // 张安冠添加20221101
        if (
            (!empty($isFavorite) || $isFavorite === 0 || is_bool($isFavorite))
            && !$userRepo->canFavoriteThread($actor)
        ) {
            throw new PermissionDeniedException('没有收藏权限');
        }

        if (
            (!empty($isDeleted) || $isDeleted === 0 || is_bool($isDeleted))
            && !$userRepo->canHideThread($actor, $thread)
        ) {
            throw new PermissionDeniedException('没有删除权限');
        }

        return true;
    }

    public function main()
    {
        //参数校验
        $thread_id = $this->inPut('id');

        if (empty($thread_id)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }

        $threadRow = Thread::query()->where('id', $thread_id)->first();
        if (empty($threadRow)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '主题id' . $thread_id . '不存在');
        }

        $categoriesId = $this->inPut('categoriesId');
        $type = $this->inPut('type');

        //当传分类时有默认
        $isEssence = $this->inPut('isEssence');
        $isPoster = $this->inPut('isPoster');
        $isSticky = $this->inPut('isSticky');
        $isFavorite = $this->inPut('isFavorite');
        $isDeleted = $this->inPut('isDeleted');

        $attributes = [];
        $requestData = [];
        if ($categoriesId) {
            $requestData = [
                'type' => 'threads',
                'relationships' => [
                    'category' => [
                        'data' => [
                            'type' => 'categories',
                            'id' => $categoriesId
                        ]
                    ],
                ]
            ];
            $attributes['type'] = (string)$type;
        }

        if ($isEssence || $isEssence === false) {
            $attributes['isEssence'] = $isEssence;
        }
        if ($isPoster || $isPoster === false) {
            $attributes['isPoster'] = $isPoster;
        }
        if ($isSticky || $isSticky === false) {
            $attributes['isSticky'] = $isSticky;
        }
        if ($isSticky === true && $this->isAllowSetThreadStick() == false) {
            Utils::outPut(ResponseCode::SET_ERROR, '置顶贴最多只允许设置'.ThreadStickSort::THREAD_STICK_COUNT_LIMIT.'条');
        }
        if ($isFavorite || $isFavorite === false) {
            $attributes['isFavorite'] = $isFavorite;
        }
        if ($isDeleted || $isDeleted === false) {
            $attributes['isDeleted'] = $isDeleted;
        }

        $requestData['id'] = $thread_id;
        $requestData['type'] = 'threads';

        $requestData['attributes'] = $attributes;
        $result = $this->bus->dispatch(
            new EditThread($thread_id, $this->user, $requestData)
        );
        $result = $this->camelData($result);

        $this->outPut(ResponseCode::SUCCESS, '', $result);
    }

    public function prefixClearCache($user)
    {
        $threadId = $this->inPut('id');
        DzqCache::delHashKey(CacheKey::LIST_THREADS_V3_THREADS, $threadId);
        DzqCache::delKey(CacheKey::LIST_THREADS_V3_CREATE_TIME);
        DzqCache::delKey(CacheKey::LIST_THREADS_V3_SEQUENCE);
        DzqCache::delKey(CacheKey::LIST_THREADS_V3_VIEW_COUNT);
        DzqCache::delKey(CacheKey::LIST_THREADS_V3_POST_TIME);
        DzqCache::delKey(CacheKey::LIST_THREADS_V3_COMPLEX);
    }
}
