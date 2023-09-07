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

namespace App\Api\Controller\Group;

use App\Common\ResponseCode;
use App\Models\Group;
use App\Models\GroupPaidUser;
use App\Models\GroupUser;
use App\Models\GroupUserMq;
use App\Models\User;
use App\Repositories\UserRepository;
use Discuz\Base\DzqAdminController;
use Discuz\Base\DzqLog;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Carbon;

class BatchDeleteGroupsController extends DzqAdminController
{
    protected $bus;

    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        $ids = $this->inPut('ids') ?: '';
        $ids = explode(',', $ids);
        return $userRepo->canDeleteGroup($this->user, $ids);
    }

    public function main()
    {
        $id = $this->inPut('ids');
        if (empty($id)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '未获取到必要参数');
        }

        $ids = explode(',', $id);

        foreach ($ids as $id) {
            if ($id < 1) {
                $this->outPut(ResponseCode::INVALID_PARAMETER);
            }
            $groupRecord = Group::query()->where('id', $id)->first();
            if (!$groupRecord) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '记录不存在');
            }
        }
        $groupDatas = Group::query()->whereIn('id', $ids)->get();

        $groupIdDeletes = $groupDatas->pluck('id')->toArray();
        $doResult = $this->changeUserGroupId($groupIdDeletes);
        if ($doResult === true) {
            $paidGroupIds=[];

            /* @var DatabaseManager $dbMgr*/
            $dbMgr = app('db');
            $dbMgr->beginTransaction();
            try {
                foreach ($groupDatas as $key=>$group) {
                    $result = $group->delete();
                    if ($result === false) {
                        $dbMgr->rollBack();
                        $doResult = '执行异常';
                        DzqLog::error('delete_user_group_error', [], 'group delete return false, group_id'.$group->id);
                        break;
                    }

                    if ($group->is_paid == Group::IS_PAID) {
                        $paidGroupIds[] = $group->id;

                        if ($group->level>0) {
                            //调整比该等级大的其他等级
                            $result = Group::query()->where('is_paid', Group::IS_PAID)
                                ->where('level', '>', $group->level)->decrement('level');
                            if ($result === false) {
                                $dbMgr->rollBack();
                                $doResult = '执行异常';
                                DzqLog::error('delete_user_group_error', [], 'group decrement return false, group_id'.$group->id);
                                break;
                            }
                        }
                    }
                }
                if ($doResult === true) {
                    $dbMgr->commit();
                }
            } catch (Throwable $e) {
                if ($doResult === true) {
                    $dbMgr->rollBack();
                }
                if (empty($e->validator) || empty($e->validator->errors())) {
                    $errorMsg = $e->getMessage();
                } else {
                    $errorMsg = $e->validator->errors()->first();
                }
                DzqLog::error('BatchDeleteGroupsController::', [], $errorMsg);
            }

            if ($doResult === true && !empty($paidGroupIds)) {
                GroupPaidUser::query()->whereIn('group_id', $paidGroupIds)->where('delete_type', '=', '0')
                    ->update(['operator_id' => $this->user->id, 'deleted_at' => Carbon::now(), 'delete_type' => GroupPaidUser::DELETE_TYPE_ADMIN]);
            }
        }

        if ($doResult === true) {
            $this->outPut(ResponseCode::SUCCESS, '');
        } else {
            $this->outPut(ResponseCode::INTERNAL_ERROR, $doResult);
        }
    }

    private function changeUserGroupId($groupIdDeletes)
    {
        $defualtGroup = Group::query()->select('id')->where('default', 1)->first();
        $defualtGroupId = Group::MEMBER_ID;
        if (!empty($defualtGroup)) {
            $defualtGroupId = $defualtGroup->id;
        }
        /** @var DatabaseManager $dbMgr */
        $dbMgr = app('db');
        $dbMgr->beginTransaction();
        try {
            $changeGroupUser = GroupUser::query()->whereIn('group_id', $groupIdDeletes)->get();
            foreach ($changeGroupUser as $key=>$item) {
                $userId = $item->user_id;
                $expired_at = $item->expiration_time;
                $dtSeconds = 0;
                if (!empty($expired_at)) {
                    if ($expired_at>Carbon::now()) {
                        $dtSeconds = Carbon::now()->diffInSeconds(Carbon::parse($expired_at));
                    }
                }
                //减少过期时间
                $remainDays = GroupUserMq::query()->whereIn('group_id', $groupIdDeletes)->where('user_id', $userId)->sum('remain_days');
                $userExpiredAt = User::query()->select('expired_at')->where('id', $userId)->first();
                if (!empty($userExpiredAt) && !empty($userExpiredAt->expired_at)) {
                    $userExpiredAtNew = Carbon::parse($userExpiredAt->expired_at)->subDays($remainDays)->subSeconds($dtSeconds);
                    $result = User::query()->where('id', $userId)->update(['expired_at'=>$userExpiredAtNew]);
                    if ($result === false) {
                        $dbMgr->rollBack();
                        DzqLog::error(
                            'BatchDeleteGroupsController::changeUserGroupId',
                            [],
                            'update User expired_at return false. user.id='.$userId.' expired_at='.$userExpiredAtNew
                        );
                        return '执行异常';
                    }
                }
                //设置新的用户组
                $newFeeGroup = GroupUserMq::query()->select(['group_id','remain_days'])
                        ->leftJoin('groups as tb2', 'group_id', 'tb2.id')
                        ->where('tb2.is_paid', Group::IS_PAID)
                        ->where('user_id', $userId)
                        ->whereNotIn('group_id', $groupIdDeletes)
                        ->orderByDesc('tb2.level')->first();
                if (empty($newFeeGroup)) {
                    //设置为免费默认组
                    $result = GroupUser::query()->where('user_id', $userId)->update(['group_id'=>$defualtGroupId]);
                    if ($result === false) {
                        $dbMgr->rollBack();
                        DzqLog::error(
                            'BatchDeleteGroupsController::changeUserGroupId',
                            [],
                            'update GroupUser default return false. user.id='.$userId.' group_id='.$defualtGroupId
                        );
                        return '执行异常';
                    }
                } else {
                    $expiredCarbon = Carbon::now()->addDays($newFeeGroup->remain_days);
                    $result = GroupUser::query()->where('user_id', $userId)
                            ->update(['group_id'=> $newFeeGroup->group_id,'expiration_time'=>$expiredCarbon]);

                    if ($result === false) {
                        $dbMgr->rollBack();
                        DzqLog::error(
                            'BatchDeleteGroupsController::changeUserGroupId',
                            [],
                            'update GroupUser fee group return false. user.id='.$userId.' group_id='.$newFeeGroup->group_i.' expiration_time='.$expiredCarbon
                        );
                        return '执行异常';
                    }

                    $result = GroupUserMq::query()->where('group_id', $newFeeGroup->group_id)->where('user_id', $userId)->delete();
                    if ($result === false) {
                        $dbMgr->rollBack();
                        DzqLog::error(
                            'BatchDeleteGroupsController::changeUserGroupId',
                            [],
                            'delete  GroupUserMq  return false. user.id='.$userId.' group_id='.$newFeeGroup->group_id
                        );
                        return '执行异常';
                    }
                }
            }

            $result = GroupUserMq::query()->whereIn('group_id', $groupIdDeletes)->delete();
            if ($result === false) {
                $dbMgr->rollBack();
                DzqLog::error(
                    'BatchDeleteGroupsController::changeUserGroupId',
                    [],
                    'delete  GroupUserMq  return false.  group_id='.json_encode($groupIdDeletes)
                );
                return '执行异常';
            }

            $dbMgr->commit();
            return true;
        } catch (Throwable $e) {
            $dbMgr->rollBack();

            if (empty($e->validator) || empty($e->validator->errors())) {
                $errorMsg = $e->getMessage();
            } else {
                $errorMsg = $e->validator->errors()->first();
            }
            DzqLog::error('BatchDeleteGroupsController::changeUserGroupId', [], $errorMsg);
            return $errorMsg;
        }
    }
}
