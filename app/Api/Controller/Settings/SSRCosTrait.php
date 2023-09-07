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
use Discuz\Base\DzqLog;
use Discuz\Common\Utils;
use Discuz\Contracts\Setting\SettingsRepository;
use Qcloud\Cos\Client;

trait SSRCosTrait
{
    /**
     * @var SettingsRepository
     */
    public $settings;

    public $cosClient;

    public $ssrZipPath = 'ssr.zip';

    public $ssrCosZipPath = 'serverless/ssr.zip';

    public $ssrServerlessName = 'serverless.yml';

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    public function ssrCosMain($region = '', $bucket = '')
    {
        if (empty($region) || empty($bucket)) {
            Utils::outPut(ResponseCode::INVALID_PARAMETER, 'SSR初始化参数错误');
        }

        $this->initCosClient($region);

        $headBucketRes = $this->headBucket($bucket);
        if (empty($headBucketRes['result'])) {
            $createBucketRes = $this->createBucket($bucket);
            sleep(1); // 延迟一秒处理创建立即上传报错问题
            if (!empty($createBucketRes['errorMsg'])) {
                Utils::outPut(ResponseCode::INVALID_PARAMETER, '存储桶创建失败：'.$createBucketRes['errorMsg'].'；请稍后再次点击提交按钮');
            }
        }

        $this->ssrZipPath = base_path($this->ssrZipPath);
        $location = $this->uploadResourceToCos($this->ssrZipPath, $region, $bucket);
        if (empty($location)) {
            Utils::outPut(ResponseCode::INTERNAL_ERROR, '上传返回的cos地址为空');
        }
        return $location;
    }

    public function initCosClient($region = 'ap-guangzhou')
    {
        $secretId = $this->settings->get('qcloud_secret_id', 'qcloud');
        $secretKey = $this->settings->get('qcloud_secret_key', 'qcloud');

        $this->cosClient = new Client([
            'region' => $region,
            'schema' => 'https', //协议头部，默认为http
            'credentials'=> [
                'secretId'  => $secretId ,
                'secretKey' => $secretKey
            ]
        ]);
    }

    public function commonCosMethod($type = '', $params = [])
    {
        $res = [
            'result' => '',
            'type' => $type,
            'params' => $params,
            'errorCode' => '',
            'errorMsg' => ''
        ];
        try {
            $result = '';
            switch ($type) {
                case 'createBucket':
//                    $bucket = 'examplebucket-1250000000'; //存储桶名称 格式：BucketName-APPID
                    $result = $this->cosClient->createBucket($params);
                    break;
                case 'listBuckets':
                    $result = $this->cosClient->listBuckets($params);
                    break;
                case 'headBucket':
                    $result = $this->cosClient->headBucket($params);
                    break;
                case 'putObject':
                    $result = $this->cosClient->putObject($params);
                    break;
            }

            $res['result'] = $result;
        } catch (\Exception $e) {
            //请求失败
            $res['errorCode'] = $e->getCode();
            $res['errorMsg'] = $e->getMessage();
        }
        DzqLog::info('SSRCosTrait::commonCosMethod', $res);
        return $res;
    }

    public function createBucket($bucket = '')
    {
        // ACL:private（私有读写）,public-read（公有读私有写）,public-read-write（公有读写）
        return $this->commonCosMethod('createBucket', ['Bucket' => $bucket, 'ACL' => 'private']);
    }

    public function listBucket($bucket = '')
    {
        return $this->commonCosMethod('listBuckets', ['Bucket' => $bucket]);
    }

    public function headBucket($bucket = '')
    {
        return $this->commonCosMethod('headBucket', ['Bucket' => $bucket]);
    }

    public function putObject($srcPath = '', $bucket = '', $key = '')
    {
        $res = '';
        $file = fopen($srcPath, 'rb');
        if ($file) {
            $res = $this->commonCosMethod('putObject', ['Bucket' => $bucket, 'Key' => $key, 'Body' => $file]);
        }
        return $res;
    }

    public function uploadResourceToCos($ssrZipPath = '', $region = '', $bucket = ''): string
    {
        if (empty($ssrZipPath) || empty($region) || empty($bucket)) {
            Utils::outPut(ResponseCode::INVALID_PARAMETER, '上传资源错误');
        }

        $this->modifyYmlFileContent($ssrZipPath, $region, $bucket);

        $putObjectRes = $this->putObject($ssrZipPath, $bucket, $this->ssrCosZipPath);
        if (!empty($putObjectRes['errorMsg'])) {
            Utils::outPut(ResponseCode::INVALID_PARAMETER, 'zip包上传cos失败：'.$putObjectRes['errorMsg']);
        }

        $result = collect($putObjectRes['result'])->toArray();
        return !empty($result['Location']) ? $result['Location'] : '';
    }

    private function modifyYmlFileContent($ssrZipPath, $region, $bucket)
    {
        $zip = new \ZipArchive();
        if ($zip->open($ssrZipPath) != true) {
            Utils::outPut(ResponseCode::INTERNAL_ERROR, 'zip压缩包打开失败');
        }

        $stream = $zip->getStream($this->ssrServerlessName);
        if (!$stream) {
            $zip->close();
            Utils::outPut(ResponseCode::INVALID_PARAMETER, $this->ssrServerlessName.' 配置文件不存在');
        }

        //Read contents into memory
        $oldContents = $zip->getFromName($this->ssrServerlessName);
        //Modify contents:
        $newContents = str_replace('region: ap-guangzhou', 'region: '.$region, $oldContents);
        $newContents = str_replace('bucket: discuz-ssr-请填写appid', 'bucket: '.$bucket, $newContents);
        //Delete the old...
        $zip->deleteName($this->ssrServerlessName);
        //Write the new...
        $zip->addFromString($this->ssrServerlessName, $newContents);
    }
}
