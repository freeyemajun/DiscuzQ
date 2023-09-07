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

use App\Censor\Censor;
use App\Commands\Attachment\AttachmentUploader;
use App\Common\ResponseCode;
use App\Models\Attachment;
use App\Repositories\UserRepository;
use Discuz\Base\DzqController;
use Discuz\Common\Utils;
use Intervention\Image\ImageManager;
use Illuminate\Http\UploadedFile;
use App\Settings\SettingsRepository;

class RelationAttachmentController extends DzqController
{
    use AttachmentTrait;

    protected $censor;

    protected $image;

    protected $uploader;

    public $settings;

    const RATIO = 0.25;

    public $positions = [
        1 => 'top-left',
        2 => 'top',
        3 => 'top-right',
        4 => 'left',
        5 => 'center',
        6 => 'right',
        7 => 'bottom-left',
        8 => 'bottom',
        9 => 'bottom-right',
    ];

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        $type = (int) $this->inPut('type'); //0 附件 1图片 2视频 3音频 4消息图片
        $this->checkUploadAttachmentPermissions($type, $this->user, $userRepo);
        return true;
    }

    public function __construct(Censor $censor, ImageManager $image, AttachmentUploader $uploader, SettingsRepository $settings)
    {
        $this->censor   = $censor;
        $this->image    = $image;
        $this->uploader = $uploader;
        $this->settings = $settings;
    }

    public function main()
    {
        $data = [
            'cosUrl' => $this->inPut('cosUrl'),
            'type' => (int)$this->inPut('type'),
            'fileName' => $this->inPut('fileName')
        ];

        $this->dzqValidate(
            $data,
            [
                'cosUrl' => 'required',
                'type' => 'required|integer|in:0,1,2,3,4',
                'fileName' => 'required|max:200'
            ]
        );

        $cosUrl = $data['cosUrl'];
        if (!Utils::isCosUrl($cosUrl)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '域名不合法，请使用cos合法地址');
        }

        if (in_array($data['type'], [Attachment::TYPE_OF_IMAGE, Attachment::TYPE_OF_DIALOG_MESSAGE])) {
            $fileInfo = $this->getImageInfo($cosUrl, $this->censor);
        } else {
            $fileInfo = $this->getDocumentInfo($cosUrl);
        }

        $attachment = Attachment::query()
            ->where(['user_id' => $this->user->id, 'type' => $data['type'], 'is_approved' => Attachment::UNAPPROVED,
                     'attachment' => $fileInfo['attachmentName'], 'file_name' => $data['fileName'], 'file_path' => $fileInfo['filePath']])
            ->first();
        if (!$attachment) {
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND, '文件匹配错误');
        }

        $this->checkAttachmentExt($data['type'], $fileInfo['ext']);
        $this->checkAttachmentSize($fileInfo['fileSize']);
        $mimeType = $this->getAttachmentMimeType($cosUrl);

        // 模糊图处理
        if ($data['type'] == Attachment::TYPE_OF_IMAGE) {
            $tmpFile = tempnam(storage_path('/tmp'), 'attachment');
            $tmpFileWithExt = $tmpFile . '.' . $fileInfo['ext'];
            @file_put_contents($tmpFileWithExt, $this->getFileContents($cosUrl));
            $blurImageFile = new UploadedFile(
                $tmpFileWithExt,
                $fileInfo['attachmentName'],
                $mimeType,
                0,
                true
            );
            // 帖子图片自适应旋转
            if (strtolower($fileInfo['ext']) != 'gif' && extension_loaded('exif')) {
                $this->image->make($tmpFileWithExt)->orientate()->save();
            }
            //添加水印
            if ((bool) $this->settings->get('watermark', 'watermark')) {
                // 原图
                $waterImage = $this->image->make($tmpFileWithExt);
                // 自定义水印图
                $watermarkImage = storage_path(
                    'app/public/' . $this->settings->get('watermark_image', 'watermark')
                );
                // 默认水印图
                if (! is_file($watermarkImage)) {
                    $watermarkImage = resource_path('images/watermark.png');
                }
                if (is_file($watermarkImage)) {
                    // 水印图按原图百分比缩放
                    $watermarkImage = $this->image->make($watermarkImage)
                        ->resize($waterImage->getWidth() * self::RATIO, $waterImage->getHeight() * self::RATIO, function ($constraint) {
                            $constraint->aspectRatio();     // 保持纵横比
                            $constraint->upsize();          // 避免文件变大
                        });
                    // 水印位置
                    $position = (int) $this->settings->get('position', 'watermark', 1);
                    // the watermark image on x-axis of the current image.
                    $x = (int) $this->settings->get('horizontal_spacing', 'watermark');
                    // the watermark image on y-axis of the current image.
                    $y = (int) $this->settings->get('vertical_spacing', 'watermark');
                    $waterImage->insert($watermarkImage, $this->positions[$position], $x, $y);
                    $waterImage->save();
                }
            }

            $this->uploader->put($data['type'], $blurImageFile, $fileInfo['attachmentName'], $fileInfo['filePath']);
            @unlink($tmpFile);
            @unlink($tmpFileWithExt);
        }

        $attachment->is_approved = Attachment::APPROVED;
        $attachment->file_size = $fileInfo['fileSize'];
        $attachment->file_width = $fileInfo['width'];
        $attachment->file_height = $fileInfo['height'];
        $attachment->file_type = $mimeType;
        $attachment->save();
        $attachment->url = $cosUrl;
        $attachment->thumbUrl = $fileInfo['thumbUrl'] ?: '';

        $this->outPut(ResponseCode::SUCCESS, '', $this->camelData($attachment));
    }
}
