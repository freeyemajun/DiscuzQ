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

namespace App\Api\Controller\Topic;

use App\Common\ResponseCode;
use App\Models\AdminActionLog;
use App\Models\Topic;
use App\Repositories\UserRepository;
use Discuz\Auth\Exception\PermissionDeniedException;
use Discuz\Base\DzqAdminController;

class BatchDeleteTopicController extends DzqAdminController
{
    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        if (!$this->user->isAdmin()) {
            throw new PermissionDeniedException('您没有批量删除话题的权限');
        }
        return true;
    }

    public function main()
    {
        $ids = $this->inPut('ids');
        if (empty($ids)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }
        $idsArr = explode(',', $ids);

        foreach ($idsArr as $key=>$value) {
            $topic = Topic::query()->where('id', $value)->first();
            if (!$topic) {
                continue;
            }
            $topicContent = $topic->content;
            $topic->delete();
            AdminActionLog::createAdminActionLog(
                $this->user->id,
                AdminActionLog::ACTION_OF_TOPIC,
                '删除话题【'. $topicContent .'】'
            );
        }

        $this->outPut(ResponseCode::SUCCESS);
    }
}
