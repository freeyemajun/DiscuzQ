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

namespace App\Traits;

use App\Api\Controller\Attachment\AttachmentTrait;
use App\Models\User;
use Discuz\Http\UrlGenerator;

trait ForumSettingSerializerTrait
{
    use AttachmentTrait;

    public function getCommonAttributes($actor = null): array
    {
        // 获取logo完整地址
        $favicon = $this->forumField->siteUrlSplicing($this->settings->get('favicon')) ?: app(UrlGenerator::class)->to('/favicon.ico');
        $logo = $this->forumField->siteUrlSplicing($this->settings->get('logo'));
        $headerLogo = $this->forumField->siteUrlSplicing($this->settings->get('header_logo'));
        $backgroundImage = $this->forumField->siteUrlSplicing($this->settings->get('background_image'));

        $attributes = [
            // 站点设置
            'set_site' => [
                'site_name' => $this->settings->get('site_name'),
                'site_title' => $this->settings->get('site_title'),
                'site_fabu' => (int)$this->settings->get('site_fabu'),
                'site_keywords' => $this->settings->get('site_keywords'),
                'site_introduction' => $this->settings->get('site_introduction'),
                'site_mode' => $this->settings->get('site_mode'), // pay public
                'open_ext_fields'=>$this->settings->get('open_ext_fields'),
                'site_close' => (bool)$this->settings->get('site_close'),
                'loginaes' => (bool)$this->settings->get('loginaes'),
                'AesKey' => $this->settings->get('AesKey') ?: '',
                'AesIv' => $this->settings->get('AesIv') ?: '',
                //'site_manage' => json_decode($this->settings->get('site_manage'), true),
//                'api_freq'    => $actor->isAdmin()?json_decode($this->settings->get('api_freq'), true):null,
                'site_close_msg'=>$this->settings->get('site_close_msg'),
                'site_favicon' => $favicon,
                'site_logo' => $logo ?: '',
                'site_header_logo' => $headerLogo ?: '',
                'site_background_image' => $backgroundImage ?: '',
//                'site_url' => $siteUrl,
                'site_stat' => $this->settings->get('site_stat') ?: '',
                'site_author' => User::query()->where('id', $this->settings->get('site_author'))->first(['id', 'username', 'avatar','nickname']),
//                'site_install' => $this->settings->get('site_install'), // 安装时间
                'site_record' => $this->settings->get('site_record'),
                //'site_cover' => $this->settings->get('site_cover') ?: '',
                'site_record_code' => $this->settings->get('site_record_code') ?: '',
                'site_master_scale' => $this->settings->get('site_master_scale'), // 站长比例
                'site_pay_group_close' => $this->settings->get('site_pay_group_close'), // 用户组购买开关
//                'site_minimum_amount' => $this->settings->get('site_minimum_amount'),
                'site_open_sort' => $this->settings->get('site_open_sort') == '' ? 0 : (int)$this->settings->get('site_open_sort'),
//                'site_can_reward'     => (bool) $this->settings->get('site_can_reward'),
//                'usernameLoginIsdisplay' => $usernameLoginIsdisplay,
//                'open_api_log' => !empty($this->settings->get('open_api_log')) ? $this->settings->get('open_api_log') : '0',
                'open_view_count' => !empty($this->settings->get('open_view_count')) ? $this->settings->get('open_view_count') : '0',
                'site_charge' => !empty($this->settings->get('site_charge', 'default')) ?  (int) $this->settings->get('site_charge', 'default') : 0,
                 //站点是否开启充值
                 'site_titles' => !empty($this->settings->get('site_titles', 'default')) ?  (int) $this->settings->get('site_titles', 'default') : 0,
                'site_rewards' => json_decode($this->settings->get('site_rewards'), true),
                'site_areward' => json_decode($this->settings->get('site_areward'), true),
                'site_redpacket' => json_decode($this->settings->get('site_redpacket'), true),
                'site_anonymous' => json_decode($this->settings->get('site_anonymous'), true),
                'site_personalletter' => json_decode($this->settings->get('site_personalletter'), true),
                'site_shop' => json_decode($this->settings->get('site_shop'), true),
                'site_pay' => json_decode($this->settings->get('site_pay'), true),
                'site_usergroup' => json_decode($this->settings->get('site_usergroup'), true),
                'site_recharges' => json_decode($this->settings->get('site_recharges'), true),
                'site_withdrawal' => json_decode($this->settings->get('site_withdrawal'), true),
                'site_comment' => json_decode($this->settings->get('site_comment'), true),

            ],

            // 注册设置
            'set_reg' => [
                'register_close' => (bool)$this->settings->get('register_close'),
                'register_validate' => (bool)$this->settings->get('register_validate'),
                'register_captcha' => (bool)$this->settings->get('register_captcha'),
                'password_length' => (int)$this->settings->get('password_length'),
                'password_strength' => $this->settings->get('password_strength') === '' ? [] : explode(',', $this->settings->get('password_strength')),
//                'register_type' => (int)$this->settings->get('register_type', 'default', 0),
                'is_need_transition' => (bool)$this->settings->get('is_need_transition'),
            ],

            // 第三方登录设置
            'passport' => [
                'offiaccount_open' => (bool)$this->settings->get('offiaccount_close', 'wx_offiaccount'), // 微信H5 开关
                'miniprogram_open' => (bool)$this->settings->get('miniprogram_close', 'wx_miniprogram'), // 微信小程序 开关
//                'oplatform_close' => (bool)$this->settings->get('oplatform_close', 'wx_oplatform'),       // 微信PC 开关
            ],

            // 支付设置
            'paycenter' => [
                'wxpay_close' => (bool)$this->settings->get('wxpay_close', 'wxpay'),
                'wxpay_ios' => (bool)$this->settings->get('wxpay_ios', 'wxpay'),
                'wxpay_mchpay_close' => (bool)$this->settings->get('wxpay_mchpay_close', 'wxpay'),
                'wxpay_mchpay_close2' => (bool)$this->settings->get('wxpay_mchpay_close2', 'wxpay'),
            ],

            // 附件设置
            'set_attach' => [
                'support_img_ext' => $this->settings->get('support_img_ext', 'default'),
                'support_file_ext' => $this->settings->get('support_file_ext', 'default'),
                'support_max_size' => $this->settings->get('support_max_size', 'default'),
                'support_max_download_num' => $this->settings->get('support_max_download_num', 'default'),
                'support_max_upload_attachment_num' => $this->getSupportMaxUploadAttachmentNum(),
                'max_upload_attachment_num' => $this->getMaxUploadAttachmentNum(),
            ],

            //ChatGpt设置
            'set_chatgpt' => [
                'fandaiurl' => $this->settings->get('fandaiurl', 'chatgpt') ? $this->settings->get('fandaiurl', 'chatgpt') : 'https://openai.1rmb.tk/v1/chat/completions',
                'airenge' => $this->settings->get('airenge', 'chatgpt') ? $this->settings->get('airenge', 'chatgpt') : '你是原神里的胡桃',
                'apikey' => $this->settings->get('apikey', 'chatgpt') ? $this->settings->get('apikey', 'chatgpt') : '',
                'aicid' => $this->settings->get('aicid', 'chatgpt') ? $this->settings->get('aicid', 'chatgpt') : 0,
                'aiuid' => $this->settings->get('aiuid', 'chatgpt') ? $this->settings->get('aiuid', 'chatgpt') : 0,
                'aiusername' => $this->settings->get('aiusername', 'chatgpt') ? $this->settings->get('aiusername', 'chatgpt') : '',
                'aipassword' => $this->settings->get('aipassword', 'chatgpt') ? $this->settings->get('aipassword', 'chatgpt') : '',
                'hosturl' => $this->settings->get('hosturl', 'chatgpt') ? $this->settings->get('hosturl', 'chatgpt') : '',
                'model' => $this->settings->get('model', 'chatgpt') ? $this->settings->get('model', 'chatgpt') : 'gpt-3.5-turbo',
                'callai' => (bool)$this->settings->get('callai', 'chatgpt') ,
                'offiaccount' => (bool)$this->settings->get('offiaccount', 'chatgpt') ,
                'revoice' => (bool)$this->settings->get('revoice', 'chatgpt') ,
            ],

            // 腾讯云设置
            'qcloud' => [
                'qcloud_app_id' => $this->settings->get('qcloud_app_id', 'qcloud'),
                'qcloud_close' => (bool)$this->settings->get('qcloud_close', 'qcloud'),
                'qcloud_cos' => (bool)$this->settings->get('qcloud_cos', 'qcloud'),
                'qcloud_captcha' => (bool)$this->settings->get('qcloud_captcha', 'qcloud'),
                'qcloud_captcha_app_id' => $this->settings->get('qcloud_captcha_app_id', 'qcloud'),
                'qcloud_faceid' => (bool)$this->settings->get('qcloud_faceid', 'qcloud'),
                'qcloud_sms' => (bool)$this->settings->get('qcloud_sms', 'qcloud'),
                'qcloud_vod' => (bool)$this->settings->get('qcloud_vod', 'qcloud'),
                'qcloud_cos_doc_preview' => (bool)$this->settings->get('qcloud_cos_doc_preview', 'qcloud'),
                'qcloud_cos_bucket_name' => $this->settings->get('qcloud_cos_bucket_name', 'qcloud'),
                'qcloud_cos_bucket_area' => $this->settings->get('qcloud_cos_bucket_area', 'qcloud'),
                'qcloud_cos_sign_url' => (bool)$this->settings->get('qcloud_cos_sign_url', 'qcloud'),
                'qcloud_vod_auto_play' => (bool)$this->settings->get('qcloud_vod_auto_play', 'qcloud')
            ],

            // 提现设置
            'set_cash' => [
                'cash_rate' => $this->settings->get('cash_rate', 'cash'), // 提现费率
                'cash_min_sum' => $this->settings->get('cash_min_sum', 'cash') ?: '',
            ],

            // 其它信息(非setting中的信息)
            'other' => [
                'thread_optimize' => (bool) $this->settings->get('thread_optimize', 'default'),   // 小程序一键开启和关闭状态
                'thread_tab'                 => (int) $this->settings->get('thread_tab', 'default'),   //首页导航选项 所有:1 推荐:2 精华:3 已关注:4
            ],

            'lbs' => [
                'lbs' => (bool) $this->settings->get('lbs', 'lbs'),         // 位置服务开关
                'qq_lbs_key' => $this->settings->get('qq_lbs_key', 'lbs'),  // 腾讯位置服务 key
            ],

            'ucenter' => [
                'ucenter' => (bool) $this->settings->get('ucenter', 'ucenter'),
            ]

        ];

        // 站点开关 - 满足条件返回
        if ($attributes['set_site']['site_close'] == 1) {
            $attributes['set_site'] += $this->forumField->getSiteClose();
        }

        // 付费模式 - 满足条件返回
        if ($attributes['set_site']['site_mode'] == 'pay') {
            $attributes['set_site'] += $this->forumField->getSitePayment();
        }

        // 开启视频服务 - 满足条件返回
        if ($attributes['qcloud']['qcloud_close'] && $attributes['qcloud']['qcloud_vod']) {
            $attributes['qcloud'] += $this->forumField->getQCloudVod();
        }

        // 当前用户是否是管理员 - 补充返回数据
        if ($actor->isAdmin()) {
            // 站点设置
            $attributes['set_site'] += $this->forumField->getSiteSettings();

            // 第三方登录设置
            $attributes['passport'] += $this->forumField->getPassportSettings();

            // 支付设置
            $attributes['paycenter'] += $this->forumField->getPaycenterSettings();

            // 腾讯云设置
            $attributes['qcloud'] += $this->forumField->getQCloudSettings();

            // 提现设置
            $attributes['set_cash'] += $this->forumField->getCashSettings();

            // 水印设置
            $attributes['watermark'] = $this->forumField->getWatermarkSettings();

            // UCenter设置
            $attributes['ucenter'] += $this->forumField->getUCenterSettings();

        // lbs 设置
            // $attributes['lbs'] += [ 'qq_lbs_key' => $this->settings->get('qq_lbs_key', 'lbs')];
        } else {
            $attributes['qcloud']['qcloud_vod_token'] = '';
        }

        return $attributes;
    }
}
