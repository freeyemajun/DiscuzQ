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

use App\Common\AuthUtils;
use App\Common\PermissionKey;
use App\Common\SettingCache;
use App\Models\Category;
use App\Models\Group;
use App\Models\User;
use App\Settings\ForumSettingField;
use App\Repositories\UserRepository;
use App\Traits\ForumSettingSerializerTrait;
use Discuz\Api\Serializer\AbstractSerializer;
use Discuz\Auth\Guest;
use App\Settings\SettingsRepository;
use Discuz\Foundation\Application;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\StopWord;

class ForumSettingSerializer extends AbstractSerializer
{
    use ForumSettingSerializerTrait;

    protected $type = 'forums';

    protected $settings;

    protected $forumField;

    protected $userRepo;

    /**
     * @var SettingCache
     */
    protected $settingcache;

    public function __construct(SettingsRepository $settings, ForumSettingField $forumField, SettingCache $settingcache, Request $request, UserRepository $userRepo)
    {
        $this->settings = $settings;
        $this->forumField = $forumField;
        $this->settingcache = $settingcache;
        $this->request = $request;
        $this->userRepo = $userRepo;
    }

    /**
     * @param object $user
     * @return array
     */
    public function getDefaultAttributes($user = null)
    {
        if ($user) {
            $actor = $user;
        } else {
            $actor = $this->getActor();
        }

        $port = $this->request->getUri()->getPort();
        $siteUrl = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost().(in_array($port, [80, 443, null]) ? '' : ':'.$port);

        $editGroupPermission = $this->userRepo->canEditGroup($actor);

        // 控制用户名密码入口是否展示
        $registerType = $this->settings->get('register_type');
        if ($registerType == 0) {
            $usernameLoginIsdisplay = true;
        } else {
            //存在未绑定任何第三方的信息用户，则展示用户名和密码登录
            $usernameLoginIsdisplay = false;
            if (User::query()->where('bind_type', AuthUtils::DEFAULT)->count('id') > 0) {
                $usernameLoginIsdisplay = true;
            }
        }
        //分类帖子总数计算
        $categories = Category::query()
            ->select([
                'id as pid', 'id as categoryId', 'name', 'description', 'icon', 'sort', 'property', 'thread_count as threadCount', 'parentid'
            ])
            ->orderBy('parentid', 'asc')
            ->orderBy('sort')
            ->get()->toArray();

        $categoriesFather = [];
        $groupId = !empty($actor->groups->toArray()[0]['id']) ? $actor->groups->toArray()[0]['id'] : Group::GUEST_ID;
        $subActor = $actor;
        if ($groupId == Group::UNPAID) {
            $subActor = new Guest();
        }
        foreach ($categories as $category) {
            if ($category['parentid'] == 0 && $this->userRepo->canViewThreads($subActor, $category['categoryId'])) {
                $categoriesFather[] = $category;
            }
        }
        $threadCount = array_sum(array_column($categoriesFather, 'threadCount'));
        //敏感词发私信禁用标识
        $disabledChat  = false;
        $dialog = StopWord::query()->where('find', '{1}')->first('dialog');
        if ($dialog && $dialog = $dialog->toArray() && $dialog['dialog'] == '{BANNED}') {
            $disabledChat = true;
        }
        $commonAttributes = $this->getCommonAttributes($actor);
        $attributes = [
            // 站点设置
            'set_site' => [
                'site_manage' => json_decode($this->settings->get('site_manage'), true),
                'api_freq'    => $actor->isAdmin()?json_decode($this->settings->get('api_freq'), true):null,
                'site_url' => $siteUrl,
                'site_install' => $this->settings->get('site_install'), // 安装时间
                'site_cover' => $this->settings->get('site_cover') ?: '',
                'site_minimum_amount' => $this->settings->get('site_minimum_amount'),
                'loginaes' => (bool)$this->settings->get('loginaes'),
                'AesKey' => $this->settings->get('AesKey'),
                'AesIv' => $this->settings->get('AesIv'),
//                'site_can_reward'     => (bool) $this->settings->get('site_can_reward'),
                'usernameLoginIsdisplay' => $usernameLoginIsdisplay,
                'open_api_log' => !empty($this->settings->get('open_api_log')) ? $this->settings->get('open_api_log') : '0',
                'thread_tab' => (int) $this->settings->get('thread_tab', 'default'),   //首页导航选项 所有:1 推荐:2 精华:3 已关注:4
                'version' => 'v' . Application::VERSION
            ],

            // 注册设置
            'set_reg' => [
                'register_type' => (int)$this->settings->get('register_type', 'default', 0),
            ],

            // 第三方登录设置
            'passport' => [],

            // 支付设置
            'paycenter' => [],

            // 附件设置
            'set_attach' => [],

            // 腾讯云设置
            'qcloud' => [],

            // 提现设置
            'set_cash' => [],

            // 其它信息(非setting中的信息)
            'other' => [
                // 基础信息
                'count_threads' => (int) $threadCount,          // 站点主题数
                'count_posts' => (int) $this->settings->get('post_count'),              // 站点回复数
                'count_users' => (int) $this->settings->get('user_count'),              // 站点用户数

                // 管理权限
                'can_edit_user_group'  => $editGroupPermission,                // 修改用户用户组
                'can_edit_user_status' => $editGroupPermission,                // 修改用户状态

                // 至少在一个分类下有发布权限
                'can_create_thread_in_category' => $actor->hasPermission('switch.'.PermissionKey::CREATE_THREAD),

                // 至少在一个分类下有查看主题列表权限 或 有全局查看权限
                'can_view_threads' => $actor->hasPermission('switch.'.PermissionKey::VIEW_THREADS),

                // 至少在一个分类下有免费查看付费帖子权限 或 有全局免费查看权限
                'can_free_view_paid_threads' => $actor->hasPermission('switch.'.PermissionKey::THREAD_FREE_VIEW_POSTS),

                // 发布权限
                'can_create_dialog'            => $this->userRepo->canCreateDialog($actor),               // 发短消息
                'can_invite_user_scale'        => $this->userRepo->canCreateInviteUserScale($actor),      // 发分成邀请
                'can_insert_thread_attachment' => $this->userRepo->canInsertAttachmentToThread($actor),   // 插入附件
                'can_insert_thread_paid'  => $this->userRepo->canInsertPayToThread($actor),               // 插入付费内容
                'can_insert_thread_video' => $this->userRepo->canInsertVideoToThread($actor),             // 插入视频
                'can_insert_thread_image' => $this->userRepo->canInsertImageToThread($actor),             // 插入图片
                'can_insert_thread_audio' => $this->userRepo->canInsertAudioToThread($actor),             // 插入语音
                'can_insert_thread_goods'      => $this->userRepo->canInsertGoodsToThread($actor),        // 插入商品
                'can_insert_thread_position'   => $this->userRepo->canInsertPositionToThread($actor),     // 插入位置
                'can_insert_thread_red_packet' => $this->userRepo->canInsertRedPacketToThread($actor),    // 插入红包
                'can_insert_thread_reward'     => $this->userRepo->canInsertRewardToThread($actor),       // 插入悬赏
                'can_insert_thread_anonymous'  => $this->userRepo->canCreateThreadAnonymous($actor),      // 允许匿名发布
                'can_insert_thread_vote'       => $this->userRepo->canInsertVoteToThread($actor),        // 插入投票

                // 其他
                'initialized_pay_password'   => (bool) $actor->pay_password,                              // 是否初始化支付密码
                'create_thread_with_captcha' => $this->userRepo->canCreateThreadWithCaptcha($actor),      // 发布内容需要验证码
                'publish_need_bind_phone'    => $this->userRepo->canCreateThreadNeedBindPhone($actor),    // 发布内容需要绑定手机
                'publish_need_bind_wechat'   => $this->userRepo->canCreateThreadNeedBindWechat($actor),    // 发布内容需要绑定微信
                'disabledChat'               => $disabledChat,
            ],

            'lbs' => [],

            'ucenter' => []
        ];

        $attributes = array_merge_recursive($attributes, $commonAttributes);

        $attributes['other']['thread_tab'] = (int) $this->settings->get('thread_tab', 'default');   //首页导航选项 所有:1 推荐:2 精华:3 已关注:4

        // 未开启vod服务 不可发布视频主题
        if (! ($attributes['qcloud']['qcloud_close'] && $attributes['qcloud']['qcloud_vod'])) {
            $attributes['other']['can_insert_thread_video'] = false;
            $attributes['other']['can_insert_thread_audio'] = false;
        }

        // 微信小程序请求时判断视频开关
        $headers = $this->request->getHeaders();
        $headersStr = strtolower(json_encode($headers, 256));
        if (! $this->settings->get('miniprogram_video', 'wx_miniprogram') &&
            (strpos(Arr::get($this->request->getServerParams(), 'HTTP_X_APP_PLATFORM'), 'wx_miniprogram') !== false || strpos($headersStr, 'miniprogram') !== false ||
                strpos($headersStr, 'compress') !== false)) {
            $attributes['other']['can_insert_thread_video'] = false;
        }

        // 判断用户是否存在
        if ($actor->exists) {
            // 当前用户信息
            $attributes['user'] = [
                'groups' => $actor->groups,
                'register_time' => $this->formatDate($actor->created_at),
                'user_id' => $actor->id
            ];
        } else {
            $attributes['qcloud']['qcloud_vod_token'] = '';
        }

        return $attributes;
    }
}
