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

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\User;
use Discuz\Console\AbstractCommand;
use Illuminate\Database\ConnectionInterface;

class AddUsersGroupCommand extends AbstractCommand
{
    protected $signature = 'usersGroup:add';

    protected $description = '对没有用户组的用户设置默认用户组';

    // 数据备份涉及到的表 start
    protected $group_user;

    protected $group_user_dst;

    //end

    //表前缀
    protected $db_pre;

    protected $connection;

    public function __construct(ConnectionInterface $connection)
    {
        parent::__construct();

        $this->connection = $connection;
        $this->db_pre = $this->connection->getTablePrefix();
        //该脚本会操作到的相关表
        $this->group_user = $this->db_pre. (new GroupUser())->getTable();
        $this->group_user_dst = $this->db_pre. 'group_user_dst';
    }

    public function handle()
    {
        $this->info('设置默认用户组脚本执行[开始]');
        try {
            $this->connection->beginTransaction();
            ini_set('memory_limit', '-1');
            $backupData =  GroupUser::query()->get()->toArray();
            //备份原数据
            //新建备份表group_user_dst
            $this->connection->statement($this->connection->raw("DROP TABLE IF EXISTS {$this->group_user_dst}"));
            $this->connection->statement($this->connection->raw(self::groupUserSql()));
            //插入原数据
            if (!empty($backupData)) {
                collect($backupData)->when(true, function ($collection) {
                    foreach ($collection->chunk(1000) as $k=>$val) {
                        $this->connection->table($this->group_user_dst)->insert($val->toArray());
                    }
                });
            }

            //插入新数据
            $groupUserIds = GroupUser::query()->get('user_id')->toArray();
            $groupUserIds = array_column($groupUserIds, 'user_id');

            $userId = User::query()->get('id')->toArray();
            $userId = array_column($userId, 'id');

            $addGroupUsersId = array_diff($userId, $groupUserIds);
            $addGroupUsers = [];
            if (!empty($addGroupUsersId)) {
                foreach ($addGroupUsersId as $val) {
                    $addGroupUsers[] = ['group_id'=>Group::MEMBER_ID,'user_id'=>$val];
                }
            }
            if (!empty($addGroupUsers)) {
                collect($addGroupUsers)->when(true, function ($collection) {
                    foreach ($collection->chunk(1000) as $k=>$val) {
                        GroupUser::query()->insert($val->toArray());
                    }
                });
            }
            $this->connection->commit();
            $this->info('设置默认用户组脚本执行[完成]');
        } catch (\Exception $e) {
            $this->connection->rollBack();
            app('log')->info('设置默认用户组脚本执行[异常],'.$e->getMessage());
            $this->info('设置默认用户组脚本执行[异常],'.$e->getMessage());
        }
    }

    //group_user_sql
    public function groupUserSql()
    {
        return  "CREATE TABLE {$this->group_user_dst} (
  `group_id` bigint(20) unsigned NOT NULL COMMENT '用户组 id',
  `user_id` bigint(20) unsigned NOT NULL COMMENT '用户 id',
  `expiration_time` datetime DEFAULT NULL COMMENT '用户组到期时间',
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `{$this->db_pre}group_user_dst_user_id_foreign` (`user_id`),
  CONSTRAINT `{$this->db_pre}group_user_dst_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `{$this->db_pre}groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `{$this->db_pre}group_user_dst_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `{$this->db_pre}users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    }
}
