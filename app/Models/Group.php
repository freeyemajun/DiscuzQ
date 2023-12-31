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

use App\Common\CacheKey;
use App\Events\Group\Deleted;
use Discuz\Base\DzqCache;
use Discuz\Base\DzqModel;
use Discuz\Database\ScopeVisibilityTrait;
use Discuz\Foundation\EventGeneratorTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $color
 * @property string $icon
 * @property int $default
 * @property int $is_display
 * @property int is_paid
 * @property float fee
 * @property int days
 * @property int scale
 * @property int $is_subordinate
 * @property int $is_commission
 * @property Collection $users
 * @property Collection $permissions
 * @property int level
 * @property string description
 * @property string notice
 * @property int time_range
 * @property int content_range
 */
class Group extends DzqModel
{
    use EventGeneratorTrait;
    use ScopeVisibilityTrait;

    /**
     * The ID of the administrator group.
     */
    const ADMINISTRATOR_ID = 1;

    /**
     * The ID of the banned group.
     */
    const BAN_ID = 5;

    /**
     * The ID of the unpaid group.
     */
    const UNPAID = 6;

    /**
     * The ID of the guest group.
     */
    const GUEST_ID = 7;

    /**
     * The ID of the experience card group.
     */
    const EXPERIENCE_ID = 8;

    /**
     * The ID of the member group.
     */
    const MEMBER_ID = 10;

    /**
     * The ID of preset groups : admin 、待付费、游客、免费体验、普通会员
     */
    const PRESET_GROUPS = [
        Group::ADMINISTRATOR_ID,
        Group::UNPAID,
        Group::GUEST_ID,
        Group::EXPERIENCE_ID,
        Group::MEMBER_ID
    ];

    /**
     * 付费站点禁止被购买用户组：admin、待付费、游客
     */
    const FORBIDDEN_PAY_GROUPS = [1, 6, 7];

    /**
     * The group need paid
     */
    const IS_PAID = 1;

    const PAID_GROUPS_NUM = 5;

    // 免费体验组预设
    const DEFAULT_TIME_RANGE = 3; // 默认访问时间范围

    const DEFAULT_CONTENT_RANGE = 30; // 默认访问内容范围

    /**
     * {@inheritdoc}
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'default' => 'boolean',
        'is_display' => 'boolean',
        'is_subordinate' => 'boolean',
        'is_commission' => 'boolean',
    ];

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'id',
        'name',
        'type',
        'color',
        'icon',
        'default',
        'is_paid',
        'fee',
        'days',
        'scale',
        'is_subordinate',
        'is_commission',
        'time_range',
        'content_range'
    ];

    /**
     * {@inheritdoc}
     */
    protected static function boot()
    {
        parent::boot();

        static::deleted(function (self $group) {
            $group->raise(new Deleted($group));
        });
    }

    /**
     * Define the relationship with the group's users.
     *
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Define the relationship with the group's permissions.
     *
     * @return HasMany
     */
    public function permission()
    {
        return $this->hasMany(Permission::class);
    }

    public function permissionWithoutCategories()
    {
        return $this->permission()->where('permission', 'not like', 'category%');
    }

    /**
     * Check whether the group has a certain permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission(string $permission)
    {
        if ($this->id == self::ADMINISTRATOR_ID) {
            return true;
        }

        return $this->permissions->contains('permission', $permission);
    }

    public static function getGroup($userId)
    {
        $groups = GroupUser::instance()->getGroupInfo([$userId]);
        $groups = array_column($groups, null, 'user_id');
        return empty($groups[$userId]) ? false : $groups[$userId];
    }

    public static function getGroups()
    {
        $cache = app('cache');
        $groups = $cache->get(CacheKey::LIST_GROUPS);
        if ($groups) {
            return $groups;
        }
        $groups = Group::query()->get()->toArray();
        $cache->put(CacheKey::LIST_GROUPS, $groups, 60 * 60);
        return $groups;
    }

    protected function clearCache()
    {
        DzqCache::delKey(CacheKey::LIST_GROUPS);
    }
}
