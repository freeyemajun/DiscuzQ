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

use Discuz\Base\DzqLog;
use Discuz\Contracts\Setting\SettingsRepository;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Scf\V20180416\Models\CreateFunctionRequest;
use TencentCloud\Scf\V20180416\Models\CreateNamespaceRequest;
use TencentCloud\Scf\V20180416\Models\CreateTriggerRequest;
use TencentCloud\Scf\V20180416\Models\ListFunctionsRequest;
use TencentCloud\Scf\V20180416\Models\ListTriggersRequest;
use TencentCloud\Scf\V20180416\ScfClient;

trait SSRScfTrait
{
    /**
     * @var SettingsRepository
     */
    public $settings;

    public $scfClient;

    public $scfFunctionName = 'discuz-ssr-test';

    public $scfRegion = 'ap-guangzhou';

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    protected function initScfClient(): ScfClient
    {
        $secretId = $this->settings->get('qcloud_secret_id', 'qcloud');
        $secretKey = $this->settings->get('qcloud_secret_key', 'qcloud');

        $cred = new Credential($secretId, $secretKey);
        $httpProfile = new HttpProfile();
        $httpProfile->setEndpoint('scf.tencentcloudapi.com');

        $clientProfile = new ClientProfile();
        $clientProfile->setHttpProfile($httpProfile);
        $this->scfClient = new ScfClient($cred, $this->scfRegion, $clientProfile);
        return $this->scfClient;
    }

    protected function commonScfMethod($type, $params)
    {
        $res = [
            'region' => $this->scfRegion,
            'result' => '',
            'type' => $type,
            'params' => $params,
            'errorCode' => '',
            'errorMsg' => ''
        ];
        try {
            $this->initScfClient();

            switch ($type) {
                case 'createFunction':
                    $req = new CreateFunctionRequest();
                    $action = 'CreateFunction';
                    break;
                case 'listFunctions':
                    $req = new ListFunctionsRequest();
                    $action = 'ListFunctions';
                    break;
                case 'createTrigger':
                    $req = new CreateTriggerRequest();
                    $action = 'CreateTrigger';
                    break;
                case 'listTriggers':
                    $req = new ListTriggersRequest();
                    $action = 'ListTriggers';
                    break;
                case 'createNamespace':
                    $req = new CreateNamespaceRequest();
                    $action = 'CreateNamespace';
                    break;
            }

            $req->fromJsonString(json_encode($params));

            $resp = $this->scfClient->$action($req);

            $res['result'] = json_decode($resp->toJsonString(), true);
        } catch (TencentCloudSDKException $e) {
            $res['code'] = $e->getCode();
            $res['errorCode'] = $e->getErrorCode();
            $res['errorMsg'] = $e->getMessage();
        }
        DzqLog::info('SSRScfTrait::commonScfMethod', $res);
        return $res;
    }

    public function createScfFunction($region, $cosBucketName = '', $cosObjectName = '', $scfFunctionName = '')
    {
        $param = [
            'FunctionName' => $scfFunctionName,
            'Code' => [
                'CosBucketName' => $cosBucketName,
                'CosObjectName' => $cosObjectName,
            ],
            'Description' => 'discuz-ssr模板函数',
            'MemorySize' => 1024,
            'Timeout' => 10,
            'Runtime' => 'Nodejs12.16',
            'Type' => 'HTTP',
            'CodeSource' => 'Cos'
        ];
        if (in_array($region, ['ap-beijing', 'ap-beijing-1'])) {
            $param['Code']['CosBucketRegion'] = $region; //对象存储的地域，地域为北京时需要传入ap-beijing,北京一区时需要传递ap-beijing-1，其他的地域不需要传递。
        }
        return $this->commonScfMethod('createFunction', $param);
    }

    public function ListScfFunctions($functionName = '')
    {
        return $this->commonScfMethod('listFunctions', ['SearchKey' => $functionName]);
    }

    public function createScfTrigger()
    {
        return $this->commonScfMethod('createTrigger', [
            'FunctionName' => $this->scfFunctionName,
            'TriggerName' => 'discuz-ssr-trigger',
            'Type' => 'apigw',
            'TriggerDesc' => json_encode([
                'api' => [
                    'authRequired' => 'FALSE',
                    'requestConfig' => [
                        'method' => 'ANY',
                    ],
                    'isIntegratedResponse' => 'FALSE',
                ],
                'service' => [
                    'serviceName' => 'SCF_API_SERVICE',
                ],
                'release' => [
                    'environmentName' => 'release',
                ],
            ])
        ]);
    }

    public function ListScfTriggers($functionName = '')
    {
        return $this->commonScfMethod('listTriggers', ['FunctionName' => $functionName]);
    }

    public function createScfNamespace($region = '')
    {
        return $this->commonScfMethod('createNamespace', ['Region' => $region, 'Namespace' => 'discuz']);
    }
}
