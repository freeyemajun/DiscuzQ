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

namespace Plugin\Activity\Console;

use App\Common\DzqConst;
use App\Models\Thread;
use App\Models\User;
use App\Notifications\Messages\Database\CustomMessage;
use App\Notifications\System;
use App\Settings\SettingsRepository;
use Discuz\Base\DzqCommand;
use Illuminate\Support\Str;
use Plugin\Activity\Model\ActivityUser;
use Plugin\Activity\Model\ThreadActivity;

class RegisterNoticeCommand extends DzqCommand
{
    protected $signature = 'register:notice';

    protected $description = '报名帖消息通知';

    protected function main()
    {
        $now = time();
        $now0 = date('Y-m-d H:i:s', strtotime('-1 minute', $now));
        $now1 = date('Y-m-d H:i:s', $now);
        //活动开始通知
        $activities = ThreadActivity::query()
            ->where('activity_start_time', '>=', $now0)
            ->where('activity_start_time', '<', $now1)
            ->where('status', DzqConst::BOOL_YES)
            ->get()->keyBy('id')->all();
        $activityIds = array_column($activities, 'id');
        $aUsers = ActivityUser::query()->whereIn('activity_id', $activityIds)->where('status', DzqConst::BOOL_YES)->get()->toArray();
        $userIds = array_column($aUsers, 'user_id');
        $users = User::query()->whereIn('id', $userIds)->get()->keyBy('id');
        $settings = app(SettingsRepository::class);
        foreach ($aUsers as $aUser) {
            $activity = $activities[$aUser['activity_id']] ?? null;
            if (empty($activity)) {
                continue;
            }
            $thread = Thread::getOneActiveThread($activity['thread_id']);
            if (empty($thread)) {
                continue;
            }
            $user = $users[$aUser['user_id']] ?? null;
            if (empty($user)) {
                continue;
            }
            $url = $settings->get('site_url') . '/thread/' . $activity['thread_id'];
            $nickname = strlen($user['nickname']) < User::NICKNAME_LIMIT_LENGTH ? $user['nickname'] :
                Str::substr($user['nickname'], 0, User::NICKNAME_LIMIT_LENGTH) . '...';
            $msg = sprintf('%s 你好，你报名的活动【%s（%s）】已开始', $nickname, $activity['title'], $url);
            echo $msg.PHP_EOL;
            $user->notify(new System(CustomMessage::class, $user, ['title'=>'活动开始通知','content'=>$msg,'threadId'=>$activity['thread_id']]));
        }
        //报名结束通知
        $activities = ThreadActivity::query()
            ->where('register_end_time', '>=', $now0)
            ->where('register_end_time', '<', $now1)
            ->where('status', DzqConst::BOOL_YES)
            ->get()->keyBy('id')->all();
        $userIds = array_column($activities, 'user_id');
        $users = User::query()->whereIn('id', $userIds)->get()->keyBy('id');
        foreach ($activities as $activity) {
            $thread = Thread::getOneActiveThread($activity['thread_id']);
            if (empty($thread)) {
                continue;
            }
            $user = $users[$activity['user_id']] ?? null;
            if (empty($user)) {
                continue;
            }
            $url = $settings->get('site_url') . '/thread/' . $activity['thread_id'];
            $nickname = strlen($user['nickname']) < User::NICKNAME_LIMIT_LENGTH ? $user['nickname'] :
                Str::substr($user['nickname'], 0, User::NICKNAME_LIMIT_LENGTH) . '...';
            $msg = sprintf('%s 你好，你发起的活动【%s（%s）】报名已结束，快去查看参与人列表吧', $nickname, $activity['title'], $url);
            echo $msg.PHP_EOL;
            $user->notify(new System(CustomMessage::class, $user, ['title'=>'报名截止通知', 'content'=>$msg,'threadId'=>$activity['thread_id']]));
        }
    }
}
