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

use App\Common\CacheKey;
use Discuz\Base\DzqAdminController;
use App\Common\ResponseCode;
use Discuz\Auth\AssertPermissionTrait;
use App\Models\Group;
use App\Models\Invite;
use App\Repositories\UserRepository;
use Discuz\Auth\Exception\PermissionDeniedException;
use Discuz\Base\DzqCache;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Validation\Factory;

class BatchUpdateGroupController extends DzqAdminController
{
    use AssertPermissionTrait;

    protected $validation;

    protected $bus;

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        if (!$userRepo->canEditGroup($this->user)) {
            throw new PermissionDeniedException('您没有修改用户组的权限');
        }
        return true;
    }

    public function __construct(Dispatcher $bus, Factory $validation)
    {
        $this->validation = $validation;
        $this->bus = $bus;
    }

    public function main()
    {
        $data = $this->inPut('data');
        $this->assertBatchData($data);

        $resultData = [];
        if (empty($data)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }

        $payGroupLevel = [];
        $payGroupIds = [];
        foreach ($data as $k=>$val) {
            $this->dzqValidate($val, [
                'name'=> 'required_without|max:10',
                'description'=> 'max:20',
                'notice'=> 'max:200',
            ]);

            $groupData = Group::query()->where('id', $val['id'])->first();
            if (empty($groupData)) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '组不存在了');
            }

            if (isset($val['isPaid']) &&  $val['isPaid'] != $groupData->is_paid) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '付费组和免费组不可变换');
            }

            if ($groupData->is_paid == Group::IS_PAID) {
                $val['default'] = false; //固定

                if (isset($val['fee']) && $val['fee'] <= 0) {
                    $this->outPut(ResponseCode::INVALID_PARAMETER, '付费组，费用错误');
                }
                if (isset($val['days']) && $val['days'] <= 0) {
                    $this->outPut(ResponseCode::INVALID_PARAMETER, '付费组，天数错误');
                }
                //检查level
                if (isset($val['level'])) {
                    if ($val['level'] <= 0) {
                        $this->outPut(ResponseCode::INVALID_PARAMETER, '付费组，级别错误');
                    }

                    if (array_key_exists($val['level'], $payGroupLevel)) {
                        $this->outPut(ResponseCode::INVALID_PARAMETER, '付费组，级别错误');
                    }
                    $payGroupLevel[$val['level']]= $val;
                    $payGroupIds[] = $val['id'];
                }
            }
        }

        $levelChange = [];
        if (!empty($payGroupIds)) {  //有付费组则必须是全部的付费组
            $groupQuery = Group::query()->where('is_paid', Group::IS_PAID);
            if (!empty($payGroupIds)) {
                $groupQuery->whereNotIn('id', $payGroupIds);
            }
            $groupIdList = $groupQuery->select('id')->get();
            if ($groupIdList->count()!=0) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '付费组数据不一致请刷新');
            }
            //检查level,不连续的整成连续
            $payGroupLevelSort = collect($payGroupLevel)->sortKeys();
            $curLevel = 1;
            foreach ($payGroupLevelSort as $key=>$value) {
                if ($key != $curLevel) {
                    $levelChange[$value['id']] = $curLevel;
                }
                $curLevel++;
            }
        }


        foreach ($data as $value) {
            try {
                $group = Group::query()->findOrFail($value['id']);
                $group->name      = $value['name'];
                if (isset($value['type'])) {
                    $group->type = $value['type'];
                }

                if (isset($value['isPaid'])) {
                    $group->is_paid = $value['isPaid'];
                }

                if (isset($value['scale'])) {
                    $group->scale = $value['scale'];
                }

                if (isset($value['isSubordinate'])) {
                    $group->is_subordinate =(bool) $value['isSubordinate'];
                }

                if (isset($value['isCommission'])) {
                    $group->is_commission = (bool) $value['isCommission'];
                }

                if (isset($value['isDisplay'])) {
                    $group->is_display = (bool) $value['isDisplay'];
                }

                if (isset($value['default'])) {
                    $group->default = (bool) $value['default'];
                    if ($value['default']) {
                        $changeInviteGroupResult = $this->changeInviteGroup($value['id']);
                    }
                }

                if (isset($value['fee'])) {
                    $fee= $value['fee'];
                    $group->fee = sprintf('%.2f', $fee);
                }

                if (isset($value['days'])) {
                    $group->days = (int)$value['days'];
                }

                if (isset($value['level'])) {
                    $group->level = (int)$value['level'];
                    if (isset($levelChange[$value['id']])) {
                        $group->level = $levelChange[$value['id']];
                    }
                }

                if (isset($value['description'])) {
                    $group->description = $value['description'];
                }

                if (isset($value['notice'])) {
                    $group->notice = $value['notice'];
                }

                if (isset($value['timeRange'])) {
                    $group->time_range = $value['timeRange'];
                }

                if (isset($value['contentRange'])) {
                    $group->content_range = $value['contentRange'];
                }

                $group->save();
                $resultData[] = $group;
            } catch (\Exception $e) {
                $this->outPut(ResponseCode::DB_ERROR, '用户组修改失败', '');
                $this->info('用户组修改失败：' . $e->getMessage());
            }
        }

        $data = $this->camelData($resultData);
        $this->outPut(ResponseCode::SUCCESS, '', $data);
    }

    public function changeInviteGroup($groupId)
    {
        $unusedInviteLinkList = Invite::query()
            ->where('group_id', '!=', $groupId)
            ->where('status', Invite::STATUS_UNUSED)
            ->where('endtime', '>', time())
            ->get();

        $unusedInviteLinkList->map(function ($item) use ($groupId) {
            try {
                $item->group_id = $groupId;
                $item->save();
            } catch (\Exception $e) {
                $this->outPut(ResponseCode::DB_ERROR, '相关邀请链接修改失败', '');
                $this->info('相关邀请链接修改失败：' . $e->getMessage());
            }
        });
    }

    public function suffixClearCache($user)
    {
        DzqCache::delKey(CacheKey::LIST_GROUPS);
    }
}
