<?php

/**
 * Copyright (C) 2020 Tencent Cloud.
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *   http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Models;

use App\Events\Notification\Created;
use Carbon\Carbon;
use Discuz\Base\DzqModel;
use Discuz\Foundation\EventGeneratorTrait;

/**
 * Class NotificationTiming
 *
 * @property int $id
 * @property string $notice_id
 * @property int $user_id
 * @property int $number
 * @property string $data
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $expired_at
 */
class NotificationTiming extends DzqModel
{
    use EventGeneratorTrait;

    public $table = 'notification_timing';

    public $timestamps = false;

    protected $fillable = [
        'notice_id',
        'user_id',
        'number',
        'data',
        'expired_at',
    ];

    public static function create($noticeId, $userId, $expiredAt = null, $number = 0, $data = ''): NotificationTiming
    {
        $nowTime = Carbon::now();
        $notification = new static();
        $notification->notice_id = $noticeId;
        $notification->user_id = $userId;
        $notification->number = $number;
        $notification->data = $data;
        $notification->created_at = $nowTime;
        $notification->updated_at = $nowTime;
        $notification->expired_at = $expiredAt;
        return $notification;
    }

    public static function createNotificationTiming($noticeId, $userId, $expiredAt = null): NotificationTiming
    {
        $currentNotification = self::create($noticeId, $userId, $expiredAt, 1);
        $currentNotification->save();
        $currentNotification->raise(new Created($currentNotification));
        return $currentNotification;
    }

    public static function getLastNotification($noticeId, $userId): array
    {
        $lastNotification = self::query()->where(['notice_id' => $noticeId, 'user_id' => $userId])->exists();
        if (!empty($lastNotification)) {
            $lastNotification = self::query()
                ->where(['notice_id' => $noticeId, 'user_id' => $userId])
                ->whereNotNull('expired_at')
                ->latest('updated_at')
                ->first();
        } else {
            $nowTime = Carbon::now();
            $lastNotification = self::create($noticeId, $userId, $nowTime, 1);
            $lastNotification->save();
            $lastNotification->raise(new Created($lastNotification));
        }
        return $lastNotification->toArray();
    }

    public static function getLastNotificationNum($noticeId, $receiveUserId): int
    {
        $LastNotification = self::query()
            ->where(['notice_id' => $noticeId, 'user_id' => $receiveUserId])
            ->whereNotNull('expired_at')
            ->latest('updated_at')
            ->first();

        return !empty($LastNotification) ? $LastNotification->toArray()['number'] : -1;
    }

    public static function getCurrentNotification($noticeId, $userId): array
    {
        $currentNotification = self::query()
            ->where(['notice_id' => $noticeId, 'user_id' => $userId])
            ->whereNull('expired_at')
            ->latest('updated_at')
            ->first();

        return !empty($currentNotification) ? $currentNotification->toArray() : [];
    }

    public static function setExpireAt($id): int
    {
        return self::query()
            ->where('id', $id)
            ->update(['expired_at' => Carbon::now()]);
    }

    public static function addNotificationNumber($id, $isCount = true): int
    {
        if (!$isCount) {
            return 0;
        }
        return self::query()
            ->where('id', $id)
            ->increment('number', 1);
    }

    public static function updateSendData($noticeTimingId = 0, $updateData = []): int
    {
        if (is_numeric($noticeTimingId) && is_array($updateData)) {
            return self::query()
                ->where('id', $noticeTimingId)
                ->update(['data' => json_encode($updateData)]);
        }
        return 0;
    }
}
