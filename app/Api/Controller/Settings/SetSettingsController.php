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

namespace App\Api\Controller\Settings;

use App\Common\CacheKey;
use App\Common\ResponseCode;
use App\Events\Setting\Saved;
use App\Events\Setting\Saving;
use App\Listeners\Setting\CheckCdn;
use App\Models\AdminActionLog;
use App\Models\Setting;
use App\Models\User;
use App\Validators\SetSettingValidator;
use Carbon\Carbon;
use Discuz\Auth\Exception\PermissionDeniedException;
use Discuz\Base\DzqAdminController;
use Discuz\Base\DzqCache;
use Discuz\Base\DzqLog;
use Discuz\Contracts\Setting\SettingsRepository;
use Discuz\Qcloud\QcloudTrait;
use Exception;
use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SetSettingsController extends DzqAdminController
{
    use CosTrait;

    use QcloudTrait;

    use CdnTrait;

    public function suffixClearCache($user)
    {
        DzqCache::delKey(CacheKey::SETTINGS);
    }

    /**
     * @var Events
     */
    protected $events;

    /**
     * @var SetSettingValidator
     */
    protected $validator;

    protected $settings;

    /**
     * @param Events $events
     * @param SetSettingValidator $validator
     */
    public function __construct(Events $events, SettingsRepository $settings, SetSettingValidator $validator)
    {
        $this->events = $events;
        $this->settings = $settings;
        $this->validator = $validator;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws PermissionDeniedException
     * @throws Exception
     */
    public function main()
    {
        $data = $this->inPut('data');
        $data = $this->filterHideSetting($data);
        // 转换为以 tag + key 为键的集合，即可去重又方便取用
        $settings = collect($data)
            ->map(function ($item) {
                $item['tag'] = $item['tag'] ?? 'default';
                return $item;
            })
            ->keyBy(function ($item) {
                return $item['tag'] . '_' . $item['key'];
            });


        /**
         * TODO: 将不同功能的设置放到监听器中验证，不要全写在 SetSettingValidator
         * @example ChangeSiteMode::class
         * @deprecated SetSettingValidator::class（建议整改后废弃）
         */
        $this->events->dispatch(new Saving($settings));

        // 分成比例检查
        $siteAuthorScale = $settings->pull('default_site_author_scale');
        $siteMasterScale = $settings->pull('default_site_master_scale');

        // 只要传了其中一个，就检查分成比例相加是否为 10
        if ($siteAuthorScale || $siteMasterScale) {
            $siteAuthorScale = abs(Arr::get($siteAuthorScale, 'value', 0));
            $siteMasterScale = abs(Arr::get($siteMasterScale, 'value', 0));
            $sum = $siteAuthorScale + $siteMasterScale;

            if ($sum === 10) {
                $this->setSiteScale($siteAuthorScale, $siteMasterScale, $settings);
            } else {
                $this->outPut(ResponseCode::INVALID_PARAMETER, 'scale_sum_not_10');
            }
        }

        // 扩展名统一改为小写
        $settings->transform(function ($item, $key) {
            $extArr = ['default_support_img_ext', 'default_support_file_ext', 'qcloud_qcloud_vod_ext'];
            if (in_array($key, $extArr)) {
                $item['value'] = strtolower($item['value']);
            }
            return $item;
        });

        /**
         * @see SetSettingValidator
         */
        $validator = $settings->pluck('value', 'key')->all();
        $keys = array_keys($validator);
        $vals = array_values($validator);
        if (!empty($keys[0]) && in_array($keys[0], Setting::$linkage) && !empty((int)$vals[0])) {
            if (!$this->settings->get('qcloud_close', 'qcloud')) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '请先开启云API');
            }
        }
        try {
            $this->validator->valid($validator);
        } catch (\Exception $e) {
            DzqLog::error('invalid_parameter', ['validator' => $validator], $e->getMessage());
            $this->outPut(ResponseCode::INVALID_PARAMETER, '', $e->getMessage());
        }
        $now = Carbon::now();
        $settings->transform(function ($setting) use ($now) {
            $key = Arr::get($setting, 'key');
            $value = Arr::get($setting, 'value');
            $tag = Arr::get($setting, 'tag', 'default');
            if ($key == 'site_manage' || $key == 'api_freq' || $key == 'site_rewards' || $key == 'site_areward' || $key == 'site_redpacket' || $key == 'site_anonymous' || $key == 'site_personalletter' || $key == 'site_shop' || $key == 'site_pay' || $key == 'site_usergroup' || $key == 'site_recharges' || $key == 'site_withdrawal' || $key == 'site_comment') {
                if (is_array($value)) {
                    $value = json_encode($value, 256);
                }
            }
            if ($key == 'password_length' && (int)$value < 6) {
                $value = '6'; // 修改数据库值
                Arr::set($setting, 'value', '6'); // 修改返回集合中的值
            }
            if ($key == 'site_expire') {
                $value = intval($value);
                if ($value > 1000000 || $value < 0) {
                    $this->outPut(ResponseCode::INVALID_PARAMETER, '请输入正确的付费模式过期天数：0~1000000');
                }
            }
            if ($key == 'inner_net_ip') {
                $this->checkInnerNetIp($value);
                $value = json_encode($value, 256);
            }
            if ($key == 'qcloud_cdn') {
                $speedDomain = $this->settings->get('qcloud_cdn_speed_domain', 'qcloud');
                $mainDomain = $this->settings->get('qcloud_cdn_main_domain', 'qcloud');
                $cdnOrigins = $this->settings->get('qcloud_cdn_origins', 'qcloud');
                $serverName = $this->settings->get('qcloud_cdn_server_name', 'qcloud');
                if (empty($speedDomain) || empty($mainDomain) || empty($cdnOrigins) || empty($serverName)) {
                    $this->outPut(ResponseCode::INVALID_PARAMETER, '请先完善CDN配置');
                }

                $cdnStatus = !empty($value) ? 1 : 0;
                $checkCdn = app()->make(CheckCdn::class);

                if (is_array($cdnOrigins)) {
                    $originsIp = $checkCdn->getRemoteIp($cdnOrigins);
                } else {
                    $originsIp = $checkCdn->getRemoteIp(json_decode($cdnOrigins));
                }

                if (!empty($cdnStatus)) {
                    $checkCdn->switchCdnStatus($speedDomain, true, $mainDomain, $originsIp);
                } else {
                    $checkCdn->switchCdnStatus($speedDomain, false, $mainDomain, $originsIp);
                }
            }
            $this->settings->set($key, $value, $tag);
            //针对腾讯云配置，设置初始时间
            switch ($key) {
                case 'qcloud_cms_image':
                    if ($value && empty($this->settings->get('qcloud_cms_image_init_time'))) {
                        $this->settings->set('qcloud_cms_image_init_time', $now, $tag);
                    }
                    break;
                case 'qcloud_cms_text':
                    if ($value && empty($this->settings->get('qcloud_cms_text_init_time'))) {
                        $this->settings->set('qcloud_cms_text_init_time', $now, $tag);
                    }
                    break;
                case 'qcloud_sms':
                    if ($value && empty($this->settings->get('qcloud_sms_init_time'))) {
                        $this->settings->set('qcloud_sms_init_time', $now, $tag);
                    }
                    break;
                case 'qcloud_faceid':
                    if ($value && empty($this->settings->get('qcloud_faceid_init_time'))) {
                        print_r([$value, empty($this->settings->get('qcloud_faceid_init_time')), $tag]);
                    }
                    break;
                case 'qcloud_cos':
                    if ($value && empty($this->settings->get('qcloud_cos_init_time'))) {
                        $this->settings->set('qcloud_cos_init_time', $now, $tag);
                    }
                    break;
                case 'qcloud_vod':
                    if ($value && empty($this->settings->get('qcloud_vod_init_time'))) {
                        $this->settings->set('qcloud_vod_init_time', $now, $tag);
                    }
                    break;
                case 'qcloud_captcha':
                    if ($value && empty($this->settings->get('qcloud_captcha_init_time'))) {
                        $this->settings->set('qcloud_captcha_init_time', $now, $tag);
                    }
                    break;
                case 'qcloud_secret_id':
                    if ($value && empty($this->settings->get('qcloud_secret_init_time'))) {
                        $this->settings->set('qcloud_secret_init_time', $now, $tag);
                    }
                    break;
                case 'qcloud_cdn':
                    if ($value && empty($this->settings->get('qcloud_secret_init_time'))) {
                        $this->settings->set('qcloud_cdn_init_time', $now, $tag);
                    }
                    break;
                case 'qcloud_ssr_region':
                    if ($value && empty($this->settings->get('qcloud_ssr_init_time'))) {
                        $this->settings->set('qcloud_ssr_init_time', $now, $tag);
                    }
                    break;
                default:
                    break;
            }


            return $setting;
        });

        $this->putBucketCors();

        $actionDesc = '';
        if (!empty($settings['cash_cash_interval_time']['key'])) {
            if ($settings['cash_cash_interval_time']['key'] == 'cash_interval_time') {
                $actionDesc = '更改提现设置';
            }
        }

        if (!empty($settings['wxpay_app_id']['key'])) {
            if ($settings['wxpay_app_id']['key'] == 'app_id') {
                $actionDesc = '配置了微信支付';
            }
        }
        if (!empty($settings['wxpay_wxpay_close']['key'])) {
            if ($settings['wxpay_wxpay_close']['key'] == 'wxpay_close') {
                if ($settings['wxpay_wxpay_close']['value'] == 0) {
                    $actionDesc = '关闭了微信支付';
                } else {
                    $actionDesc = '开启了微信支付';
                }
            }
        }

        if (!empty($actionDesc)) {
            AdminActionLog::createAdminActionLog(
                $this->user->id,
                AdminActionLog::ACTION_OF_SETTING,
                $actionDesc
            );
        }

        $this->events->dispatch(new Saved($settings));
        $this->outPut(ResponseCode::SUCCESS);
    }

    /**
     * 设置分成比例
     *
     * @param int $siteAuthorScale
     * @param int $siteMasterScale
     * @param Collection $settings
     */
    private function setSiteScale(int $siteAuthorScale, int $siteMasterScale, &$settings)
    {
        $settings->put('default_site_author_scale', [
            'key' => 'site_author_scale',
            'value' => $siteAuthorScale,
            'tag' => 'default',
        ]);

        $settings->put('default_site_master_scale', [
            'key' => 'site_master_scale',
            'value' => $siteMasterScale,
            'tag' => 'default',
        ]);
    }

    private function filterHideSetting($settingData)
    {
        foreach ($settingData as &$item) {
            $key = $item['key'];
            if (!empty($item['value']) && is_string($item['value'])) {
                $value = $item['value'];
            } else {
                continue;
            }
            $tag = $item['tag'];
            if (preg_match('/^\*+$/', $value)) {
                $item['value'] = $this->settings->get($key, $tag);
            }
        }
        return $settingData;
    }

    private function checkInnerNetIp($value)
    {
        if ($this->user->id != User::SUPER_ADMINISTRATOR) {
            $this->outPut(ResponseCode::UNAUTHORIZED, '该功能只有超管可编辑');
        }
        if (!is_array($value)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '参数必须为数组');
        }
        foreach ($value as $key => $ipNet) {
            $ipArr = explode('/', $ipNet);
            if (count($ipArr) != 2) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '第'.($key+1).'个参数格式错误');
            }
            if ($this->isIp($ipArr[0]) == false) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '第'.($key+1).'个参数ip地址格式不正确');
            }
            if (($ipArr[1] < 8) || ($ipArr[1] > 30)) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '第'.($key+1).'个参数掩码位不正确（请输入8～30的掩码位）');
            }
        }
    }

    //检测IP地址的函数
    public function isIp($ip): bool
    {
        $arr = explode('.', $ip);
        if (count($arr) != 4) {
            return false;
        } else {
            for ($i = 0;$i < 4;$i++) {
                if (($arr[$i] < 0) || ($arr[$i] > 255)) {
                    return false;
                }
            }
        }
        return true;
    }
}
