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

namespace App\Console\Commands;

use Discuz\Console\AbstractCommand;
use Discuz\Foundation\Application;

class AddIssueAtColumeCommand extends AbstractCommand
{
    protected $signature = 'upgrade:issue_at';

    protected $description = '新增字段issue_at，设置初始化值为created_at';

    protected $table_threads;

    protected $app;

    protected $db;

    protected $db_pre;

    public function __construct(Application $app)
    {
        parent::__construct();
        $this->app = $app;
        $this->db = app('db');
        $database = $this->app->config('database');
        $this->db_pre = $database['prefix'];
        //该脚本会操作到的相关表
        $this->table_threads = $this->db_pre.'threads';
    }

    public function handle()
    {
        $sql='update '.$this->table_threads.' set issue_at=created_at';
        $this->info('初始化issue_at任务开始: '.date('Y-m-d H:i:s'));
        app('db')->update($sql);
        $this->info('初始化issue_at任务结束: '.date('Y-m-d H:i:s'));
    }
}
