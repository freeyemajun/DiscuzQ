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

namespace App\Api\Serializer;

use App\Models\DenyUser;
use App\Models\User;
use App\Models\UsernameChange;
use App\Repositories\UserFollowRepository;
use App\Traits\UserSerializerTrait;
use Discuz\Api\Serializer\AbstractSerializer;
use Discuz\Contracts\Setting\SettingsRepository;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Http\Message\ServerRequestInterface;

class UserV3Serializer extends AbstractSerializer
{
    use UserSerializerTrait;

    /**
     * {@inheritdoc}
     */
    protected $type = 'users';

    /**
     * @var Gate
     */
    protected $gate;

    protected $userFollow;

    protected $settings;

    protected $request;

    /**
     * @param Gate $gate
     * @param UserFollowRepository $userFollow
     */
    public function __construct(Gate $gate, UserFollowRepository $userFollow, SettingsRepository $settings, ServerRequestInterface $request)
    {
        $this->gate = $gate;
        $this->userFollow = $userFollow;
        $this->settings = $settings;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     *
     * @param User $model
     */
    public function getDefaultAttributes($model)
    {
        $gate = $this->gate->forUser($this->actor);

        $canEdit = $gate->allows('edit', $model);

        $commonAttributes = $this->getCommonAttributes($model);
        $attributes = [
            'originalAvatarUrl' => $this->getOriginalAvatar($model),
            'backgroundUrl'     => $model->background,
            'originalBackGroundUrl'     => $this->getOriginalBackGround($model),
            'isReal'            => $this->getIsReal($model),
            'threadCount'       => (int) $model->thread_count,
            'followCount'       => (int) $model->follow_count,
            'fansCount'         => (int) $model->fans_count,
            'likedCount'        => (int) $model->liked_count,
            'questionCount'     => (int) $model->question_count,
            'signature'         => $model->signature,
            'usernameBout'      => (int) $model->username_bout,
            'updatedAt'         => optional($model->updated_at)->format('Y-m-d H:i:s'),
            'canEdit'           => $canEdit,
            'canDelete'         => $gate->allows('delete', $model),
            'showGroups'        => $gate->allows('showGroups', $model),     // 是否显示用户组
            'registerReason'    => $model->register_reason,                 // 注册原因
            'denyStatus'        => (bool) $model->denyStatus,
//            'canBeAsked'        => $model->id !== $this->actor->id && $model->can('canBeAsked'), // 是否允许被提问(已弃用指定人问答)
            'hasPassword'       => !empty($model->password),
            'isRenew'           => !empty($model->isRenew),

            'paid'              => $model->paid,
            'payTime'           => $this->formatDate($model->payTime),
//            'unreadNotifications' => $model->getUnreadNotificationCount(),
//            'typeUnreadNotifications' => $model->getUnreadTypesNotificationCount()
        ];

        $attributes = array_merge_recursive($attributes, $commonAttributes);

        // 用户详情用到，所以增加这个字段
        $attributes['follow'] = $this->userFollow->findFollowDetail($this->actor->id, $model->id);

        // 限制字段 本人/权限 显示
        if ($this->actor->id === $model->id) {
            $attributes['username'] = $model->username;

            $attributes += [
                'originalMobile'    => $model->getRawOriginal('mobile'),
//            'registerIp'        => $model->register_ip,
//            'registerPort'      => $model->register_port,
//            'lastLoginIp'       => $model->last_login_ip,
//            'lastLoginPort'     => $model->last_login_port,
                'identity'          => $model->identity,
                'realname'          => $model->realname,
                'mobile'            => $model->mobile,
                'hasPassword'       => $model->password ? true : false,

                // 钱包余额
                'canWalletPay'  => $this->actor->checkWalletPay(),
                'walletBalance' => $this->actor->userWallet->available_amount,
                'walletFreeze'  => $this->actor->userWallet->freeze_amount,
            ];

            //是否可以修改用户名
            $canEditUsername = true;
            $usernamechange = UsernameChange::query()->where('user_id', $model->id)->orderBy('id', 'desc')
                ->first();
            if ($usernamechange) {
                $currentTime=date('y-m-d h:i:s');
                $oldTime=$usernamechange->updated_at;
                if (strtotime($currentTime)<strtotime('+1years', strtotime($oldTime))) {
                    $canEditUsername = false;
                }
            }
            $attributes += [
                'canEditUsername' => $canEditUsername
            ];
        }

        //是否屏蔽
        if ($this->actor->id != $model->id) {
            $denyUser = DenyUser::query()
                ->where('user_id', $this->actor->id)
                ->where('deny_user_id', $model->id)
                ->first();
            $isDeny = false;
            if ($denyUser) {
                $isDeny = true;
            }
            $attributes += [
              'isDeny' => $isDeny
           ];
        }

        return $attributes;
    }

    public function getOriginalAvatar($model)
    {
        $uid = sprintf('%09d', $model->id);
        $dir1 = substr($uid, 0, 3);
        $dir2 = substr($uid, 3, 2);
        $dir3 = substr($uid, 5, 2);
        $originalAvatar = $dir1.'/'.$dir2.'/'.$dir3.'/original_'.substr($uid, -2).'.png';
        $avatar = $model->getRawOriginal('avatar');
        $originalAvatarUrl = $model->getOriginalAvatarPath();
        return $originalAvatarUrl;
        if (strpos($avatar, '://') === false) {
            if (!file_exists(storage_path('app/public/avatars/' . $originalAvatar))) {
                $originalAvatarUrl = $model->avatar;
            }
        } else {
            $fileData = @file_get_contents($originalAvatarUrl, false, stream_context_create(['ssl'=>['verify_peer'=>false, 'verify_peer_name'=>false]]));
            if (!$fileData) {
                $originalAvatarUrl = $model->avatar;
            }
        }
        return $originalAvatarUrl;
    }

    public function getOriginalBackGround($model)
    {
        $uid = sprintf('%09d', $model->id);
        $dir1 = substr($uid, 0, 3);
        $dir2 = substr($uid, 3, 2);
        $dir3 = substr($uid, 5, 2);

        $background = $model->getRawOriginal('background');
        $originalBackGroundUrl = $model->getOriginalBackGroundPath();
        return $originalBackGroundUrl;
        if (strpos($background, '://') === false) {
            $backUrl = str_replace($dir1.'/'.$dir2.'/'.$dir3.'/', $dir1.'/'.$dir2.'/'.$dir3.'/'.'original_', $background);
            if (!file_exists(storage_path('app/public/background/' . $backUrl))) {
                $originalBackGroundUrl = $model->background;
            }
        } else {
            $fileData = @file_get_contents($originalBackGroundUrl, false, stream_context_create(['ssl'=>['verify_peer'=>false, 'verify_peer_name'=>false]]));
            if (!$fileData) {
                $originalBackGroundUrl = $model->background;
            }
        }
        return $originalBackGroundUrl;
    }

    /**
     * 是否实名认证
     *
     * @param User $model
     * @return string
     */
    public function getIsReal(User $model)
    {
        if (isset($model->realname) && $model->realname != null) {
            return true;
        } else {
            return false;
        }
    }
}
