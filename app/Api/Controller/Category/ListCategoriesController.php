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

namespace App\Api\Controller\Category;

use App\Common\ResponseCode;
use App\Models\Category;
use App\Repositories\UserRepository;
use Discuz\Base\DzqController;

class ListCategoriesController extends DzqController
{
    private $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        return true;
    }

    public function main()
    {
        $categories = Category::query()
            ->select([
                'id as pid', 'id as categoryId', 'name', 'description', 'icon', 'sort', 'property', 'thread_count as threadCount', 'parentid'
            ])
            ->orderBy('parentid', 'asc')
            ->orderBy('sort')
            ->get()->toArray();

        $categoriesFather = [];
        $categoriesChild = [];

        foreach ($categories as $category) {
            $category['canCreateThread'] = $this->userRepo->canCreateThread($this->user, $category['categoryId']);
            $category['searchIds'] = [(int)$category['categoryId']];

            // 二级子类集合
            if ($category['parentid'] !== 0) {
                $categoriesChild[$category['parentid']][] = $category;
            }

            if ($category['parentid'] == 0 && $this->userRepo->canViewThreads($this->user, $category['categoryId'])) {
                $categoriesFather[] = $category;
            }
        }
        // 获取一级分类的二级子类
        foreach ($categoriesFather as $key => $value) {
            if (isset($categoriesChild[$value['categoryId']])) {
                $categoriesFather[$key]['searchIds'] = array_merge([$value['searchIds']], array_column($categoriesChild[$value['categoryId']], 'categoryId'));
                $categoriesFather[$key]['children'] = $categoriesChild[$value['categoryId']];
            } else {
                $categoriesFather[$key]['children'] = [];
            }
        }

        if (empty($categoriesFather)) {
            $this->outPut(ResponseCode::UNAUTHORIZED, '没有浏览权限');
        }
        $this->outPut(ResponseCode::SUCCESS, '', $categoriesFather);
    }
}
