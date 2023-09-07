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

namespace App\Models;

use App\Events\AdminActionLog\Created;
use Carbon\Carbon;
use Discuz\Database\ScopeVisibilityTrait;
use Discuz\Foundation\EventGeneratorTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $user_id
 * @property string $action_desc
 * @property string $ip
 * @property Carbon $created_at
 */
class AdminActionLog extends Model
{
    use EventGeneratorTrait;
    use ScopeVisibilityTrait;

    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected $table = 'admin_action_logs';

    const ACTION_OF_SETTING = 1000; // 操作站点设置

    const ACTION_OF_PERMISSION = 1001; // 操作权限

    const ACTION_OF_GROUP = 1002; // 操作用户组信息

    const ACTION_OF_USER = 1003; // 操作用户信息

    const ACTION_OF_CATEGORY = 1004; // 操作内容分类

    const ACTION_OF_TOPIC = 1005; // 操作话题

    const ACTION_OF_THREAD = 1006; // 操作帖子

    const ACTION_OF_COMMENT = 1007; // 操作评论

    /**
     * Create a new adminactionlog.
     *
     * @property int $userId
     * @property int $type
     * @property string $actionDesc
     */
    public static function createAdminActionLog($userId, $type, $actionDesc)
    {
        $request = app('request');
        $adminactionlog = new static;
        $adminactionlog->type = $type;
        $adminactionlog->user_id = $userId;
        $adminactionlog->action_desc = $actionDesc;
        $adminactionlog->ip = ip($request->getServerParams());
        $adminactionlog->created_at = Carbon::now();
        $adminactionlog->save();
        $adminactionlog->raise(new Created($adminactionlog));
        return $adminactionlog;
    }
}
