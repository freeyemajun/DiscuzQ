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

namespace App\Api\Controller\Report;

use App\Common\ResponseCode;
use App\Models\GroupUser;
use App\Models\Report;
use App\Models\User;
use Discuz\Base\DzqAdminController;

class ListReportsController extends DzqAdminController
{
    public function main()
    {
        $filter = $this->inPut('filter');
        $currentPage = $this->inPut('page');
        $perPage = $this->inPut('perPage');
        $reports = $this->filterReports($filter, $currentPage, $perPage);
        $reportsList = $reports['pageData'];

        $userIds = array_unique(array_column($reportsList, 'user_id'));
        $groups = GroupUser::instance()->getGroupInfo($userIds);
        $groups = array_column($groups, null, 'user_id');
        $users = User::instance()->getUsers($userIds);
        $users = array_column($users, null, 'id');
        $reportList = [];
        if ($reportsList) {
            $result = [];
            foreach ($reportsList as $report) {
                $userId = $report['user_id'];
                $user = [];
                if (!empty($users[$userId])) {
                    $user = $this->getUserInfo($users[$userId]);
                }
                $group = [];
                if (!empty($groups[$userId])) {
                    $group = $this->getGroupInfo($groups[$userId]);
                }
                $result[] = [
                    'user' => $user,
                    'group' => $group,
                    'report' => $report,
                ];
            }
            $reports['pageData'] = $result;
            foreach ($reports['pageData'] as $k=>$v) {
                $reportList['pageData'][$k]['user'] = $v['user'];
                $reportList['pageData'][$k]['group'] = $v['group'];
                $reportList['pageData'][$k]['report']['id']=$v['report']['id'];
                $reportList['pageData'][$k]['report']['userId']=$v['report']['user_id'];
                $reportList['pageData'][$k]['report']['threadId']=$v['report']['thread_id'];
                $reportList['pageData'][$k]['report']['postId']=$v['report']['post_id'];
                $reportList['pageData'][$k]['report']['type']=$v['report']['type'];
                $reportList['pageData'][$k]['report']['reason']=$v['report']['reason'];
                $reportList['pageData'][$k]['report']['status']=$v['report']['status'];
                $reportList['pageData'][$k]['report']['createdAt']=$v['report']['created_at'];
                $reportList['pageData'][$k]['report']['updatedAt']=$v['report']['updated_at'];
            }
            $reportList['currentPage']=$reports['currentPage'];
            $reportList['perPage']=$reports['perPage'];
            $reportList['firstPageUrl']=$reports['firstPageUrl'];
            $reportList['nextPageUrl']=$reports['nextPageUrl'];
            $reportList['prePageUrl']=$reports['prePageUrl'];
            $reportList['pageLength']=$reports['pageLength'];
            $reportList['totalPage']=$reports['totalPage'];
            $reportList['totalCount']=$reports['totalCount'];
        }

        $this->outPut(ResponseCode::SUCCESS, '', $reportList);
    }

    private function filterReports($filter, $currentPage, $perPage)
    {
        $reports = Report::query();
        if (!empty($filter['username'])) {
            $this->dzqValidate($filter, [
                'username' => 'string|max:200'
            ]);
            $reports->whereIn('user_id', User::query()->where('username', 'like', "%{$filter['username']}%")->pluck('id'));
        }
        if (isset($filter['status']) && strlen($filter['status']) > 0) {
            $this->dzqValidate($filter, [
                'status' => 'integer|in:0,1'
            ]);
            $reports->where('status', $filter['status']);
        }

        if (isset($filter['type']) && strlen($filter['type']) > 0) {
            $this->dzqValidate($filter, [
                'type' => 'integer|in:0,1,2'
            ]);
            $reports->where('type', $filter['type']);
        }
        if (!empty($filter['startTime']) && !empty($filter['endTime'])) {
            $filter['endTime'] =  date('Y-m-d', strtotime('+1 day', strtotime($filter['endTime'])));
            $reports->whereBetween('created_at', [$filter['startTime'], $filter['endTime']]);
        }
        $reports->orderByDesc('created_at');
        $reports = $this->pagination($currentPage, $perPage, $reports);
        return $reports;
    }

    private function getUserInfo($user)
    {
        return [
            'pid' => $user['id'],
            'userId' => $user['id'],
            'userName' => $user['username'],
        ];
    }

    private function getGroupInfo($group)
    {
        return [
            'pid' => $group['group_id'],
            'groupId' => $group['group_id'],
            'groupName' => $group['groups']['name'],
            'groupIcon' => $group['groups']['icon']
        ];
    }
}
