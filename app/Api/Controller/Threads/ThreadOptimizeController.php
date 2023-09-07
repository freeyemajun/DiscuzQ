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

use App\Common\ResponseCode;
use App\Models\Setting;
use App\Models\Thread;
use App\Modules\ThreadTom\TomConfig;
use Discuz\Base\DzqAdminController;
use Discuz\Base\DzqCache;
use Discuz\Contracts\Setting\SettingsRepository;

class ThreadOptimizeController extends DzqAdminController
{
    protected $settings;

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    public function main()
    {
        $isDisplay = $this->inPut('isDisplay');
        if (empty($isDisplay) && $isDisplay !== 0) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }
        if (!in_array($isDisplay, [Thread::BOOL_YES,Thread::BOOL_NO])) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }
        try {
            $db = $this->getDB();
            $prefix = $db->getTablePrefix();
            $thread = 'threads';
            $threadTom = 'thread_tom';
            if (!empty($prefix)) {
                $thread = $prefix.'threads';
                $threadTom = $prefix.'thread_tom';
            }
            if ($isDisplay === Thread::BOOL_NO) {
                $optimizeStr = "'".implode("','",TomConfig::OPTIMIZE_TYPE_LIST)."'";
                $db->update("update {$thread} set is_display = {$isDisplay} where id in (select thread_id from {$threadTom} where tom_type in (".$optimizeStr.")) or price > 0 or attachment_price > 0 or is_anonymous = 1");
            } elseif ($isDisplay === Thread::BOOL_YES) {
                $db->update("update {$thread} set is_display = {$isDisplay} where id > 0");
            }

            $threadOptimize = Setting::query()->where('key', 'thread_optimize')->first();
            if ($threadOptimize) {
                $this->settings->set('thread_optimize', $isDisplay, 'default');
            } else {
                $th = new Setting();
                $th->key = 'thread_optimize';
                $th->value = $isDisplay;
                $th->tag = 'default';
                $th->save();
            }
            DzqCache::clear();
            $this->outPut(ResponseCode::SUCCESS);
        } catch (\Exception $e) {
            $this->info('threadOptimize_error_' . $this->user->id, $e->getMessage());
            $this->outPut(ResponseCode::DB_ERROR, $e->getMessage());
        }
    }
}
