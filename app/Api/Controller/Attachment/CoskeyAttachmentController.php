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

namespace App\Api\Controller\Attachment;

use App\Api\Controller\Settings\CosTrait;
use App\Common\ResponseCode;
use App\Common\Utils;
use App\Models\Attachment;
use App\Repositories\UserRepository;
use Discuz\Base\DzqController;
use Illuminate\Support\Str;
use QCloud\COSSTS\Sts;
use QCloud\COSSTS\Scope;

class CoskeyAttachmentController extends DzqController
{
    use AttachmentTrait;

    use CosTrait;

    private $sts;
    private $url = 'https://sts.tencentcloudapi.com/';
    private $domain = 'sts.tencentcloudapi.com';
    private $proxy = '';
    private $bucket;
    private $region;
    private $secretId;
    private $secretKey;
    private $durationSeconds = 1800;

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        $type = (int) $this->inPut('type'); //0 附件 1图片 2视频 3音频 4消息图片
        $this->checkUploadAttachmentPermissions($type, $this->user, $userRepo);
        return true;
    }

    public function __construct()
    {
        $this->sts = new Sts();
    }

    public function main()
    {
        $settings = $this->getSettings();
        $cosParam = $this->configuration($settings);
        $type = $this->inPut('type'); //0 附件 1图片 2视频 3音频 4消息图片
        $fileName = $this->inPut('fileName');
        $attachment = $this->inPut('attachment');

        if (empty($settings['qcloud_cos'])) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '请去管理员后台开启腾讯云对象存储');
        }

        if (empty($settings['qcloud_cos_bucket_name'])) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '请去管理员后台腾讯云设置对象存储配置所属地域');
        }

        if (empty($settings['qcloud_cos_bucket_area'])) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '请去管理员后台腾讯云设置对象存储配置空间名称');
        }

        if (empty($settings['qcloud_secret_id'])) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '请去管理员后台腾讯云设置云API配置Secretid');
        }

        if (empty($settings['qcloud_secret_key'])) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '请去管理员后台腾讯云设置云API配置SecretKey');
        }

        if (empty($settings['support_file_ext']) && $type == Attachment::TYPE_OF_FILE) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '请去管理员后台补充附件设置-支持的文件扩展名');
        }

        if (empty($settings['support_img_ext']) && $type == Attachment::TYPE_OF_IMAGE) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '请去管理员后台补充附件设置-支持图片扩展名');
        }

        if (empty($settings['support_max_size'])) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '请去管理员后台补充附件设置-支持的最大尺寸');
        }

        if (empty($fileName)) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '缺少必要参数：文件名');
        }

        if (empty($attachment)) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '缺少必要参数：加密字符串');
        }

        if (strrpos($fileName,".") && strrpos($attachment,".")) {
            $fileExt = substr($fileName, strrpos($fileName,".") + 1);
            $attachmentExt = substr($attachment, strrpos($attachment,".") + 1);
            if ($fileExt != $attachmentExt) {
                $this->outPut(ResponseCode::INVALID_PARAMETER, '文件后缀名不一致');
            }
            $this->checkAttachmentExt($type, $fileExt);
        } else {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '文件后缀名错误');
        }

        $attachmentData = Attachment::query()->where('attachment', $attachment)->first();
        if (!empty($attachmentData)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '该加密字符串已被占用，请重新加密');
        }

        $siteUrl = Utils::getSiteUrl();
        if (empty($settings['qcloud_cors_origin']) || !in_array($siteUrl, json_decode($settings['qcloud_cors_origin']))) {
            $putBucketCorsResult = $this->putBucketCors();
            if (!$putBucketCorsResult) {
                $this->outPut(ResponseCode::INTERNAL_ERROR, '对象存储跨域设置失败，请去管理后台重新开启腾讯云对象存储');
            }
        }

        $config = $this->appendix($cosParam);
        $tempKeys = $this->sts->getTempKeys($config);
        $this->createAttachment($type, $attachment, $fileName);

        $this->outPut(ResponseCode::SUCCESS,'', $tempKeys);
    }

    // 配置信息
    private function configuration($settings)
    {
        $this->bucket = $settings['qcloud_cos_bucket_name'];
        $this->region = $settings['qcloud_cos_bucket_area'];
        $this->secretId = $settings['qcloud_secret_id'];
        $this->secretKey = $settings['qcloud_secret_key'];

        return array(
            'url' => $this->url,
            'domain' => $this->domain,
            'proxy' => $this->proxy,
            'region' => $this->region , // 换成 bucket 所在园区
            'secretId' => $this->secretId, // 固定密钥
            'secretKey' => $this->secretKey, // 固定密钥
            'durationSeconds' => $this->durationSeconds, // 密钥有效期
            'allowActions' => array(
                'name/cos:PutObject',
                'name/cos:GetObject',
                'name/cos:PostObject'
            )
        );
    }

    // 文件上传
    private function appendix($cosParam)
    {
        $fileName = $this->inPut('attachment');
        $config = array();

        $allowPrefix = '/public/attachments/' . date('Y/m/d') . '/';
        array_push(
            $config,
            new Scope("name/cos:PutObject", $this->bucket, $this->region, $allowPrefix . $fileName),
            new Scope("name/cos:GetObject", $this->bucket, $this->region, $allowPrefix . $fileName),
            new Scope("name/cos:PostObject", $this->bucket, $this->region, $allowPrefix . $fileName)
        );
        return array_merge($cosParam, ['policy' => $this->sts->getPolicy($config)]);
    }

    private function createAttachment($type, $attachmentName, $fileName)
    {
        $attachment = new Attachment();
        $attachment->uuid = Str::uuid();;
        $attachment->user_id = $this->user->id;
        $attachment->type = $type;
        $attachment->is_approved = Attachment::UNAPPROVED;
        $attachment->attachment = $attachmentName;
        $attachment->file_path = 'public/attachments/' . date('Y/m/d') . '/';
        $attachment->file_name = $fileName;
        $attachment->file_size = 0;
        $attachment->file_width = 0;
        $attachment->file_height = 0;
        $attachment->file_type = '';
        $attachment->is_remote = Attachment::YES_REMOTE;
        $attachment->ip = ip($this->request->getServerParams());
        $attachment->save();
        return $attachment->id;
    }
}
