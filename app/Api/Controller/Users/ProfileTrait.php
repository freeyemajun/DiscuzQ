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

namespace App\Api\Controller\Users;

use App\Api\Serializer\AdminUserSerializer;
use App\Api\Serializer\UserV3Serializer;
use App\Models\GroupUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

trait ProfileTrait
{
    //返回的数据一定包含的数据
    public $include = [];

    public function getData($user, $isAdmin = false)
    {
        $serializer = UserV3Serializer::class;
        $isAdmin == true && $serializer = AdminUserSerializer::class;
        $userSerialize = $this->app->make($serializer);
        $userSerialize->setRequest($this->request);
        $data = $userSerialize->getDefaultAttributes($user);

        $grounUser = [$user->id];
        $groups = GroupUser::instance()->getGroupInfo($grounUser);
        $groups = array_column($groups, null, 'user_id');
        $data['group'] = $this->getGroupInfo($groups[$user->id]);

        //用户是否绑定微信
        $data['isBindWechat'] = !empty($user->wechat);

        return $data;
    }

    /**
     * @param $cacheData
     * @param null $limit
     * @return Collection
     */
    public function search($limit, $cacheData = null)
    {
        $query = User::query()->selectRaw('id,username,avatar,liked_count as likedCount')
            ->where('status', 0)
            ->whereBetween('login_at', [Carbon::parse('-30 days'), Carbon::now()])
            ->orderBy('thread_count', 'desc')
            ->orderBy('login_at', 'desc');
        // cache
        if ($cacheData) {
            $query->whereIn('id', $cacheData);
        }

        return $query->take($limit)->get();
    }

    protected function getGroupInfo($group)
    {
        return [
            'pid' => $group['group_id'],
            'groupId' => $group['group_id'],
            'groupName' => $group['groups']['name']
        ];
    }
}
