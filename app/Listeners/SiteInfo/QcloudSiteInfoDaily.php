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

namespace App\Listeners\SiteInfo;

use App\Common\Utils;
use App\Models\Order;
use App\Models\Post;
use App\Models\SiteInfoDaily;
use App\Models\Thread;
use App\Models\User;
use App\Models\UserWalletCash;
use App\Settings\SettingsRepository;
use Discuz\Qcloud\QcloudTrait;
use Psr\Http\Message\ServerRequestInterface as Request;

class QcloudSiteInfoDaily
{
    use QcloudTrait;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var SettingsRepository
     */
    public $settings;

    /**
     * @param Request $request
     * @param SettingsRepository $settings
     */
    public function __construct(Request $request, SettingsRepository $settings)
    {
        $this->request = $request;
        $this->settings = $settings;
    }

    public function handle()
    {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $today = date('Y-m-d', time());
        $cache_time = strtotime($tomorrow) - time();
        $uin = app('cache')->get('qcloud_uin');
        $settings = app('cache')->get('settings_up');
        if(!empty($settings)){
            $isset_daily = app('cache')->get('qcloud_site_info_daily_'.$settings['site_id']);
            if ($isset_daily) {
                return;
            }
        }else{
            $settings = $this->settings->all()->toArray();
        }
        $site_url = !empty($settings['site_url']) ? $settings['site_url'] : '';
        if (empty($site_url) || $site_url != Utils::getSiteUrl()) {
            $site_url = Utils::getSiteUrl();
            $this->settings->set('site_url', $site_url, 'default');
        }
        $appfile = base_path('vendor/discuz/core/src/Foundation/Application.php');
        $current_version_time = date('Y-m-d H:i:s', filemtime($appfile));
        if (empty($settings['site_init_version'])) {
            $settings['site_init_version'] = app()->version();
            $this->settings->set('site_init_version', $settings['site_init_version'], 'default');
        }
        if (empty($settings['site_init_version_time'])) {
            $settings['site_init_version_time'] = $current_version_time;
            $this->settings->set('site_init_version_time', $settings['site_init_version_time'], 'default');
        }
        $withdrawal_profit = round(UserWalletCash::query()->where('cash_status', UserWalletCash::STATUS_PAID)->sum('cash_charge'), 2);
        $order_royalty = round(Order::query()->where('status', Order::ORDER_STATUS_PAID)->sum('master_amount'), 2);
        $total_register_profit = round(Order::query()->where('type', Order::ORDER_TYPE_REGISTER)->where('status', Order::ORDER_STATUS_PAID)->sum('amount'), 2);
        $total_profit = $withdrawal_profit + $order_royalty + $total_register_profit;

        //开源应用中心：KUBERNETES_OAC_HOST、云开发tcb：KUBERNETES_SERVICE_HOST、云市场镜像：CLOUD_MARKET_HOST
        $install_type = 'default';
        $oldversionfile = base_path('public/.oldversion');
        if (!file_exists($oldversionfile)) {
            //docker 环境
            $install_type = 'docker';
            $oac = getenv('KUBERNETES_OAC_HOST');
            $tcb = getenv('KUBERNETES_SERVICE_HOST');
            $market = getenv('CLOUD_MARKET_HOST');
            if (!empty($tcb)) {
                $install_type = 'tcb';
            }
            if (!empty($oac)) {
                $install_type = 'oac';
            }
            if (!empty($market)) {
                $install_type = 'market';
            }
        }
        //获取site_info_dailies 中 is_upload 为 0 的数据
        $site_info_dailies = SiteInfoDaily::query()->where('is_upload', 0)->where('date', '<', $today)->get()->toArray();
        $json = [
            'site_id' => $settings['site_id'] ?? '',
            'site_secret' => !empty($settings['site_secret']) ? $settings['site_secret'] : '',
            'site_url'  =>  !empty($settings['site_url']) ? $settings['site_url'] : '',
            'site_name' =>  !empty($settings['site_name']) ? $settings['site_name'] : '',
            'site_charge'    =>  !empty($settings['site_price']) ? 1 : 0,
            'site_titles'    =>  !empty($settings['site_titles']) ? 1 : 0,
            'qcloud_secret_id'  =>  !empty($settings['qcloud_secret_id']) ? $settings['qcloud_secret_id'] : '-',
            'site_uin'  =>  $uin,
            'relation_qcloud'   =>  !empty($settings['qcloud_secret_id']) ? 1 : 0,
            'qcloud_secret_init_time'   =>  $settings['qcloud_secret_init_time'] ?? null,
            'current_version'   =>  app()->version(),
            'current_version_time'  =>  $current_version_time,
            'site_init_version' =>  $settings['site_init_version'],
            'site_init_version_time' =>  $settings['site_init_version_time'],
            'install_type'  =>  $install_type,
            'miniprogram_open'     =>  !empty($settings['miniprogram_close']) ? 1 : 0,
            'offiaccount_open'     =>  !empty($settings['offiaccount_close']) ? 1 : 0,
            'web_open'  =>  empty($settings['site_close']) ? 1 : 0,
            'withdrawal_profit' =>  $withdrawal_profit,
            'order_royalty' =>  $order_royalty,
            'total_register_profit' =>  $total_register_profit,
            'total_profit' =>  $total_profit,
            'total_user_count'  =>  User::query()->count(),
            'total_thread_count'    =>  Thread::query()->count(),
            'total_post_count'  =>  Post::query()->count(),
            'site_info_dailies'    =>  $site_info_dailies
        ];
        try {
            $this->siteInfoDaily($json)->wait();
            SiteInfoDaily::query()->whereIn('id', array_column($site_info_dailies, 'id'))->update(['is_upload' => 1]);
            app('cache')->put('qcloud_site_info_daily_'.$settings['site_id'], 1, $cache_time);
        } catch (\Exception $e) {
        }
    }
}
