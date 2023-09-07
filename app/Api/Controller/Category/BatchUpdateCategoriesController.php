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
use App\Models\AdminActionLog;
use App\Models\Category;
use App\Common\ResponseCode;
use Discuz\Base\DzqAdminController;
use App\Repositories\UserRepository;
use Discuz\Auth\Exception\PermissionDeniedException;
use Discuz\Base\DzqCache;

class BatchUpdateCategoriesController extends DzqAdminController
{
    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        if (!$this->user->isAdmin()) {
            throw new PermissionDeniedException('您没有更新分类的权限');
        }
        return true;
    }

    public function main()
    {
        $data = $this->inPut('data');
        $ip   = ip($this->request->getServerParams());

        // 批量添加的限制
        if (count($data) > 100) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '批量添加超过限制', '');
        }

        $resultData = [];
        $validate = app('validator');
        foreach ($data as $key => $value) {
            try {
                $validate->validate($value, [
                    'id'            => 'required|int|min:1',
                    'name'          => 'required|min:1|max:20',
                    'sort'          => 'required|int',
                    'description'   => 'sometimes|max:200'
                ]);

                $category = Category::query()->findOrFail($value['id']);
                if (isset($value['name']) && $value['name'] != $category->name) {
                    $oldName = $category->name;
                    $category->name = $value['name'];
                }

                if (isset($value['description'])) {
                    $category->description = $value['description'];
                }

                if (isset($value['sort'])) {
                    $category->sort = $value['sort'];
                }
                $category->ip = $ip;
                $category->save();
                $resultData[] = $category;

                if (isset($oldName)) {
                    AdminActionLog::createAdminActionLog(
                        $this->user->id,
                        AdminActionLog::ACTION_OF_CATEGORY,
                        '更新内容分类名称【'. $oldName .'】为【'. $category->name .'】'
                    );
                    unset($oldName);
                }
            } catch (\Exception $e) {
                app('log')->info('requestId：' . $this->requestId . '-' . '修改内容分类 "' . $value['name'] . '" 出错： ' . $e->getMessage());
                $this->outPut(ResponseCode::INTERNAL_ERROR, '修改出错', [$e->getMessage(), $value]);
            }
        }

        $this->outPut(ResponseCode::SUCCESS, '', '');
    }

    public function suffixClearCache($user)
    {
        DzqCache::delKey(CacheKey::CATEGORIES);
    }
}
