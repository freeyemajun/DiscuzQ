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

namespace App\Listeners\Setting;

use App\Api\Controller\Settings\CdnTrait;
use App\Api\Controller\Settings\DnspodTrait;
use App\Common\ResponseCode;
use App\Events\Setting\Saving;
use Discuz\Common\Utils;
use Discuz\Contracts\Setting\SettingsRepository;
use Illuminate\Support\Arr;
use Illuminate\Validation\Factory as Validator;
use Illuminate\Validation\ValidationException;

class CheckCdn
{
    use CdnTrait;

    use DnspodTrait;

    /**
     * @var SettingsRepository
     */
    protected $settings;

    /**
     * @var Validator
     */
    public $validator;

    /**
     * @param SettingsRepository $settings
     * @param Validator $validator
     */
    public function __construct(SettingsRepository $settings, Validator $validator)
    {
        $this->settings = $settings;
        $this->validator = $validator;
    }

    /**
     * @param Saving $event
     * @throws ValidationException
     */
    public function handle(Saving $event)
    {
        $settings = $event->settings->where('tag', 'qcloud')->pluck('value', 'key')->toArray();

        if (Arr::hasAny($settings, [
            'qcloud_cdn_speed_domain',
            'qcloud_cdn_main_domain',
            'qcloud_cdn_origins',
            'qcloud_cdn_server_name',
        ])) {
            // 合并原配置与新配置（新值覆盖旧值）
            $settings = array_merge((array) $this->settings->tag('qcloud'), $settings);

            $this->validator->make($settings, [
                'qcloud_cdn_speed_domain' => 'string|required',
                'qcloud_cdn_main_domain' => 'string|required',
                'qcloud_cdn_origins' => 'required',
                'qcloud_cdn_server_name' => 'string|required',
            ])->validate();

            if (is_array($settings['qcloud_cdn_origins'])) {
                $event->settings->put('qcloud_qcloud_cdn_origins', [
                    'key' => 'qcloud_cdn_origins',
                    'value' => json_encode($settings['qcloud_cdn_origins']),
                    'tag' => 'qcloud'
                ]);
            }

            $this->saveCdnDomain($settings);
        }
    }

    public function saveCdnDomain($settings)
    {
        $speedDomain = isset($settings['qcloud_cdn_speed_domain']) ? $settings['qcloud_cdn_speed_domain'] : '';
        $mainDomain = isset($settings['qcloud_cdn_main_domain']) ? $settings['qcloud_cdn_main_domain'] : '';
        $cdnOrigins = isset($settings['qcloud_cdn_origins']) ? $settings['qcloud_cdn_origins'] : [];
        if (!is_array($cdnOrigins)) {
            $cdnOrigins = json_decode($cdnOrigins);
        }
        $cdnServerName = isset($settings['qcloud_cdn_server_name']) ? $settings['qcloud_cdn_server_name'] : '';

        $serverName = $_SERVER['SERVER_NAME'];
        if (isset($settings['qcloud_cdn_speed_domain']) && $speedDomain != $serverName) {
            Utils::outPut(ResponseCode::INVALID_PARAMETER, '加速域名与本站点域名不符');
        }


//        $this->stopCdnDomain($speedDomain);
//        $this->deleteCdnDomain($speedDomain);
//        dd('已完成域名删除');

        $cdnDomainStatus = $this->getCdnDomainStatus($speedDomain);
        if ($cdnDomainStatus == 'processing') {
            Utils::outPut(ResponseCode::EXTERNAL_API_ERROR, 'CDN正在部署中，请稍后操作');
        }

        if ($cdnDomainStatus != 'deleted') {
            $this->startCdnDomain($speedDomain);
            $this->updateCdnDomain($speedDomain, $cdnOrigins, $cdnServerName);
            $this->purgeCdnPathCache();
        } else {
            // 添加cdn域名
            $this->addCdnDomain($speedDomain, $cdnOrigins, $cdnServerName);

            if (empty($this->getDomainArr()) || !in_array($mainDomain, $this->getDomainArr())) {
                // 添加dnspod主域名
                $this->createDomain($mainDomain);
            }

            $subDomain = $this->getSubDomain($speedDomain, $mainDomain);

            // 暂停ip地址解析
            $this->modifyIpRecordStatus($mainDomain, $this->getRemoteIp($cdnOrigins), 'DISABLE', $subDomain);
            // 添加cname解析
            $this->createRecord($mainDomain, $this->getCdnCname($speedDomain), 'CNAME', $subDomain, 'ENABLE');

            if (empty($this->settings->get('qcloud_cdn', 'qcloud'))) {
                $this->settings->set('qcloud_cdn', '1', 'qcloud');
            }

            // 添加ip地址解析
//            $this->createRecord($mainDomain, $this->getRemoteIp($cdnOrigins), 'A', $subDomain, 'DISABLE');
        }

        if (isset($settings['qcloud_cdn'])) {
            app('log')->info('qcloud_cdn_status', ['qcloud_cdn' => $settings['qcloud_cdn']]);
            if ((bool)$settings['qcloud_cdn'] == true) {  //开启了cdn
                $this->switchCdnStatus($speedDomain, true, $mainDomain, $this->getRemoteIp($cdnOrigins));
            } else {
                $this->switchCdnStatus($speedDomain, false, $mainDomain, $this->getRemoteIp($cdnOrigins));
            }
        }
    }

    public function modifyIpRecordStatus($mainDomain, $ipValue, $status, $subDomain)
    {
        $ipRecordId = $this->getRecordId($mainDomain, $ipValue, 'A', $subDomain);
        $this->modifyRecordStatus($mainDomain, $ipRecordId, $status);
    }

    public function modifyCnameRecordStatus($mainDomain, $cnameValue, $status, $subDomain)
    {
        $cnameRecordId = $this->getRecordId($mainDomain, $cnameValue, 'CNAME', $subDomain);
        $this->modifyRecordStatus($mainDomain, $cnameRecordId, $status);
    }

    public function switchCdnStatus($speedDomain, $status, $mainDomain, $ipValue)
    {
        $cdnDomainStatus = $this->getCdnDomainStatus($speedDomain);
        $subDomain = $this->getSubDomain($speedDomain, $mainDomain);

        if ($status === true) {
            if ($cdnDomainStatus == 'offline') {
                $this->startCdnDomain($speedDomain);
            }

            // 开启cname的解析
            $this->modifyIpRecordStatus($mainDomain, $ipValue, 'DISABLE', $subDomain);
            $this->modifyCnameRecordStatus($mainDomain, $this->getCdnCname($speedDomain), 'ENABLE', $subDomain);
        } else {
            if ($cdnDomainStatus == 'online') {
                $this->stopCdnDomain($speedDomain);
            }

            // 开启ip地址的解析
            $this->modifyCnameRecordStatus($mainDomain, $this->getCdnCname($speedDomain), 'DISABLE', $subDomain);
            $this->modifyIpRecordStatus($mainDomain, $ipValue, 'ENABLE', $subDomain);
        }
        $this->purgeCdnPathCache(); // 刷新cdn
    }

    public function getCdnCname($speedDomain): string
    {
        $domains = $this->describeDomains($speedDomain);
        $cname = '';
        if (isset($domains['TotalNumber']) && $domains['TotalNumber'] == 1) {
            $cname = $domains['Domains'][0]['Cname'];
        }
        return $cname;
    }

    public function getRemoteIp($cdnOrigins = []): string
    {
        if (!empty($cdnOrigins)) {
            return $cdnOrigins[0];
        }
        return '';
    }
}
