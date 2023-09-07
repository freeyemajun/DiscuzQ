<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $thread_id
 * @property int $sort
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ThreadStickSort extends Model
{
    protected $table = 'thread_stick_sort';

    public const THREAD_STICK_COUNT_LIMIT = 20;

    protected $fillable = [
        'thread_id',
        'sort',
    ];

    public static function createThreadStick($threadId, $sort = 0)
    {
        return self::query()->create(['thread_id' => $threadId, 'sort' => $sort]);
    }

    public static function updateThreadStick($threadId, $sort = 0): int
    {
        return self::query()->update(['thread_id' => $threadId, 'sort' => $sort]);
    }

    public static function deleteThreadStick($threadId)
    {
        return self::query()->where('thread_id', '=', $threadId)->delete();
    }
}
