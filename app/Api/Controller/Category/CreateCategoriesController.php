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

namespace App\Api\Controller\Category;

use App\Common\CacheKey;
use App\Common\ResponseCode;
use App\Models\AdminActionLog;
use App\Models\Category;
use Discuz\Base\DzqAdminController;
use App\Repositories\UserRepository;
use Discuz\Auth\Exception\PermissionDeniedException;
use Discuz\Base\DzqCache;

class CreateCategoriesController extends DzqAdminController
{
    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        if (!$this->user->isAdmin()) {
            throw new PermissionDeniedException('您没有创建分类的权限');
        }
        return true;
    }

    public function main()
    {
        $data = $this->inPut('data');
        if (empty($data)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }
        // 批量添加的限制
        if (count($data) > 100) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '批量添加超过限制', '');
        }
        $ip = ip($this->request->getServerParams());
        foreach ($data as $key=>$val) {
            $name = $val['name'];
            $sort = $val['sort'] ? $val['sort']: 0;
            $description = $val['description'];
            $parentId = $val['parentId'] ? $val['parentId']: 0;
            $icon = isset($val['icon']) ? $val['icon'] : '';
            $category = Category::build(
                $name,
                $description,
                (int) $sort,
                (int) $parentId,
                $icon,
                $ip
            );
            $this->dzqValidate($category->getAttributes(), [
                'name' => 'required',
            ]);
            $category->save();
            AdminActionLog::createAdminActionLog(
                $this->user->id,
                AdminActionLog::ACTION_OF_CATEGORY,
                '新增内容分类【'. $name .'】'
            );
        }
        $this->outPut(ResponseCode::SUCCESS, '');
    }

    public function suffixClearCache($user)
    {
        DzqCache::delKey(CacheKey::CATEGORIES);
    }
}
