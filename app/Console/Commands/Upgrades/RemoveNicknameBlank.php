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

namespace App\Console\Commands\Upgrades;

use App\Models\User;
use Discuz\Console\AbstractCommand;

class RemoveNicknameBlank extends AbstractCommand
{
    protected $signature = 'upgrade:removeNicknameBlank';
    protected $description = '去除用户昵称中空格';

    const HANDLE_NUM = 500;

    protected function handle()
    {
        $this->info('start remove nickname blank');
        $blank_user_ids = User::query()->where('nickname', 'like', '% %')->pluck('id')->toArray();
        $pre_between = array_chunk($blank_user_ids, self::HANDLE_NUM);
        foreach ($pre_between as $val){
            $users = User::query()->whereIn('id', $val)->get();
            foreach ($users as $user){
                $user->nickname = str_replace(" ", "", $user->nickname);
                $isset_nickname = User::query()->where('nickname', $user->nickname)->first();
                while($isset_nickname){
                    $user->nickname = $user->nickname.rand(000, 999);
                    $isset_nickname = User::query()->where('nickname', $user->nickname)->first();
                }
                $user->save();
            }
        }
        $this->info('remove nickname blank success');
    }
}