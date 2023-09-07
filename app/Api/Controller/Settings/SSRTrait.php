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

use App\Common\ResponseCode;
use Discuz\Common\Utils;
use Discuz\Contracts\Setting\SettingsRepository;

trait SSRTrait
{
    use SSRCosTrait;

    use SSRScfTrait;

    /**
     * @var SettingsRepository
     */
    public $settings;

    public $cosClient;

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    public function initSSR($region = '', $bucket = '')
    {
        $location = $this->ssrCosMain($region, $bucket);
        $this->settings->set('qcloud_ssr_package_location', $location, 'qcloud');

        $this->scfRegion = $region;
        $this->createScfFunction(
            $region,
            $this->getCosBucketName($bucket),
            '/'.$this->ssrCosZipPath,
            $this->scfFunctionName
        );

        for ($second = 0; $second < 5; $second++) {
            sleep(1);
            $scfFunctionInfo = $this->ListScfFunctions($this->scfFunctionName);
            $functionStatus = !empty($scfFunctionInfo['result']['Functions'][0]['Status']) ? $scfFunctionInfo['result']['Functions'][0]['Status'] : '';
            if ($functionStatus == 'Active') {
                $this->createTrigger(); // 创建函数完成之后延迟执行创建触发器
                break;
            }
        }
        if ($second == 5) {
            Utils::outPut(ResponseCode::NET_ERROR, '函数：'.$this->scfFunctionName.' 触发器创建失败，请重新点击提交按钮');
        }
    }

    private function getCosBucketName($bucket)
    {
        // $bucket: discuz-ssr-appid
        return 'discuz-ssr';
    }

    private function setAccessPath($triggerDesc)
    {
        if (!empty($triggerDesc)) {
            $TriggerDesc = json_decode($triggerDesc);
            $subDomain = $TriggerDesc->service->subDomain;
            $TriggerDescArr = explode('/release', $subDomain);
            if (count($TriggerDescArr) != 0) {
                $accessPath = $TriggerDescArr[0];
                $this->settings->set('qcloud_ssr_access_path', $accessPath, 'qcloud');
            }
        }
    }

    private function createTrigger()
    {
        $triggersRes = $this->ListScfTriggers($this->scfFunctionName);
        $triggerDesc = !empty($triggersRes['result']['Triggers'][0]['TriggerDesc']) ? $triggersRes['result']['Triggers'][0]['TriggerDesc'] : '';
        if (empty($triggerDesc)) {
            $createTriggerRes = $this->createScfTrigger();
            if (!empty($createTriggerRes['errorMsg'])) {
                dd($createTriggerRes);
            }
            $triggerDesc = !empty($createTriggerRes['result']['TriggerInfo']['TriggerDesc']) ? $createTriggerRes['result']['TriggerInfo']['TriggerDesc'] : '';
        }
        $this->setAccessPath($triggerDesc);
    }
}
