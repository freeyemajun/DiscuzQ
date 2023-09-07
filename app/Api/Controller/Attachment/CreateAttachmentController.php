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

use App\Api\Serializer\AttachmentSerializer;
use App\Commands\Attachment\AttachmentUploader;
use App\Common\ResponseCode;
use App\Events\Attachment\Saving;
use App\Events\Attachment\Uploaded;
use App\Events\Attachment\Uploading;
use App\Models\Attachment;
use App\Models\Dialog;
use App\Models\DialogMessage;
use App\Models\Group;
use App\Repositories\UserRepository;
use App\Settings\SettingsRepository;
use App\Validators\AttachmentValidator;
use Discuz\Base\DzqController;
use Discuz\Base\DzqLog;
use Discuz\Common\Utils;
use Discuz\Foundation\EventsDispatchTrait;
use Discuz\Wechat\EasyWechatTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Http\UploadedFile;

class CreateAttachmentController extends DzqController
{
    use EventsDispatchTrait;

    use EasyWechatTrait;

    use AttachmentTrait;

    protected $events;

    protected $validator;

    protected $uploader;

    protected $settings;

    private $file;
    private $fileName;
    private $ext;
    private $tmpFile;
    private $tmpFileWithExt;
    private $fileType;
    private $fileSize;

    public function __construct(Dispatcher $events, AttachmentValidator $validator, AttachmentUploader $uploader, SettingsRepository $settings)
    {
        $this->events = $events;
        $this->validator = $validator;
        $this->uploader = $uploader;
        $this->settings = $settings;
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        $type = $this->inPut('type') ?: 0;

        $typeMethodMap = [
            Attachment::TYPE_OF_FILE => [$userRepo, 'canInsertAttachmentToThread'],
            Attachment::TYPE_OF_IMAGE => [$userRepo, 'canInsertImageToThread'],
            Attachment::TYPE_OF_AUDIO => [$userRepo, 'canInsertAudioToThread'],
            Attachment::TYPE_OF_VIDEO => [$userRepo, 'canInsertVideoToThread'],
            Attachment::TYPE_OF_ANSWER => [$userRepo, 'canInsertRewardToThread'],
            Attachment::TYPE_OF_DIALOG_MESSAGE => [$userRepo, 'canCreateDialog'],
        ];
        // 不在这里面，则通过，后续会有 type 表单验证
        if (!isset($typeMethodMap[$type])) {
            return true;
        }

        //开启付费站点，新用户注册时会被加入到待付费组，导致填写补充信息上传图片附件提示无权限
        try {
            if (!empty($groupId = $this->user->getRelations()['groups'][0]->getAttribute('id')) && $groupId == Group::UNPAID) {
                $group = Group::query()->where('id', Group::MEMBER_ID)->get();
                if (!empty($group)) {
                    $this->user->setRelation('groups', $group);
                }
            }
        } catch (\Exception $e) {
            DzqLog::error('create_attachment', [
                'user'      => $this->user,
                'groupId'   => $groupId,
                'group'     => $group
            ]);
            $this->outPut(ResponseCode::INTERNAL_ERROR, '附件上传失败');
        }

        return call_user_func_array($typeMethodMap[$type], [$this->user]);
    }

    public function main()
    {
        $actor = $this->user;
        $file = Arr::get($this->request->getUploadedFiles(), 'file');
        $name = Arr::get($this->request->getParsedBody(), 'name', '');
        $type = (int) Arr::get($this->request->getParsedBody(), 'type', 0);
        $dialogMessageId = (int) Arr::get($this->request->getParsedBody(), 'dialogMessageId', 0);
        $order = (int) Arr::get($this->request->getParsedBody(), 'order', 0);
        $ipAddress = ip($this->request->getServerParams());
        $mediaId = Arr::get($this->request->getParsedBody(), 'mediaId', '');
        $request = $this->request->getParsedBody();
        if (isset($request['fileUrl']) && empty($request['fileUrl'])) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '图片链接不可为空');
        }
        $fileUrl = Arr::get($this->request->getParsedBody(), 'fileUrl', '');

//        ini_set('memory_limit',-1);

        if (!empty($mediaId)) {
            $this->getMediaFile($mediaId);
        }
        if (!empty($fileUrl)) {
            $this->getFileFromUrl($fileUrl, $type);
        }
        if (empty($mediaId) && empty($fileUrl)) {
            $this->getConventionalFile($file);
        }

        //上传临时目录之前验证
        $this->validator->valid([
            'type' => $type,
            'file' => $this->file,
            'size' => $this->fileSize,
            'ext' => strtolower($this->ext),
        ]);

        // 从微信下载的文件不需要再移动
        if (!$mediaId && !$fileUrl) {
            $this->file->moveTo($this->tmpFileWithExt);
        }

        try {
            if (!$mediaId && !$fileUrl) {
                $file = new UploadedFile(
                    $this->tmpFileWithExt,
                    $this->fileName,
                    $this->fileType,
                    $this->file->getError(),
                    true
                );
            } else {
                $file = new UploadedFile(
                    $this->tmpFileWithExt,
                    $this->fileName,
                    $this->fileType,
                    null,
                    true
                );
            }

            if (strtolower($this->ext) != 'gif' && $this->fileType != 'image/gif') {
                $this->events->dispatch(
                    new Uploading($actor, $file)
                );
            }
            // 上传
            $this->uploader->upload($file, $type);

            $this->events->dispatch(
                new Uploaded($actor, $this->uploader)
            );

            $width = 0;
            $height = 0;
            if (in_array($type, [Attachment::TYPE_OF_IMAGE, Attachment::TYPE_OF_DIALOG_MESSAGE, Attachment::TYPE_OF_ANSWER])) {
                list($width, $height) = getimagesize($this->tmpFileWithExt);
            }

            $attachment = Attachment::build(
                $actor->id,
                $type,
                $this->uploader->fileName,
                $this->uploader->getPath(),
                $name ?: $file->getClientOriginalName(),
                $file->getSize(),
                $file->getClientMimeType(),
                $this->uploader->isRemote(),
                Attachment::APPROVED,
                $ipAddress,
                $order,
                $width,
                $height
            );

            $this->events->dispatch(
                new Saving($attachment, $this->uploader, $actor)
            );

            $attachment->save();

            $this->dispatchEventsFor($attachment);
        } catch (\Exception $e) {
            DzqLog::error('create_attachment_api_error', [
                'user'  => $this->user
            ], $e->getMessage());
            $this->outPut(ResponseCode::INTERNAL_ERROR, '附件上传异常', [$e->getMessage()]);
        } finally {
            @unlink($this->tmpFile);
            @unlink($this->tmpFileWithExt);
        }
        $attachmentSerializer = $this->app->make(AttachmentSerializer::class);
        $attachment = $attachmentSerializer->getDefaultAttributes($attachment);
        $data = $this->camelData($attachment);

        if (!empty($dialogMessageId)) {
            $this->updateDialogMessage($dialogMessageId, $data);
        }

        $this->outPut(ResponseCode::SUCCESS, '', $data);
    }

    private function getMediaFile($mediaId)
    {
        $app = $this->offiaccount();
        $mediaFile = $app->media->get($mediaId);
        if ($mediaFile instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $this->file = $mediaFile->save(storage_path('/tmp'));
            $this->fileName = basename($this->file);
            $this->ext = pathinfo($this->file, PATHINFO_EXTENSION);
            $this->tmpFileWithExt = storage_path('/tmp') .'/' . $this->fileName;
            $imageSize = getimagesize($this->tmpFileWithExt);
            $this->fileType = $imageSize['mime'];
            $this->fileSize = filesize($this->tmpFileWithExt);
        }
    }

    private function getFileFromUrl($fileUrl, $type)
    {
        $urlContent = Utils::downLoadFile($fileUrl);
        if (!$urlContent) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '未获取到文件内容');
        }

        // url链接图处理
        $this->fileType = $this->getAttachmentMimeType($fileUrl);
        $allowedMimeType = $this->getAllowedMimeType($type);
        if (!in_array($this->fileType, $allowedMimeType)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, $this->fileType . ' 类型文件不允许上传');
        }

        $this->ext = $this->getFileExt($type, $this->fileType);

        // 文件名兼容处理
        $this->fileName = urldecode(basename($fileUrl));
        if (strpos($this->fileName, '?') !== false) {
            $this->fileName = substr($this->fileName, 0, strpos($this->fileName, '?'));
            if (strpos($this->fileName, '/') !== false) {
                $this->fileName = substr($this->fileName, strrpos($this->fileName, '/') + 1, strlen($this->fileName));
            }
        }
        if (strpos($this->fileName, '.') === false) {
            $this->fileName  = $this->fileName . '.' . $this->ext;
        }

        $this->tmpFile = $tmpFile = tempnam(storage_path('/tmp'), 'attachment');
        $this->tmpFileWithExt = $this->tmpFile . '.' . $this->ext;
        @file_put_contents($this->tmpFileWithExt, $urlContent);
        $this->fileSize = filesize($this->tmpFileWithExt);
        $this->file = $urlContent;
    }

    private function getConventionalFile($file)
    {
        $this->file = $file;
        $this->fileName = $this->file->getClientFilename();
        $this->fileSize = $this->file->getSize();
        $this->fileType = $this->file->getClientMediaType();
        $this->ext = pathinfo($this->fileName, PATHINFO_EXTENSION);
        $this->tmpFile = tempnam(storage_path('/tmp'), 'attachment');
        $this->tmpFileWithExt = $this->tmpFile . ($this->ext ? ".$this->ext" : '');
    }

    private function updateDialogMessage($dialogMessageId, $data)
    {
        $message_text = [
            'message_text'  => null,
            'image_url'     => $data['url']
        ];
        $message_text = addslashes(json_encode($message_text));
        $updateDialogMessageResult = DialogMessage::query()
            ->where('id', $dialogMessageId)
            ->update(['attachment_id' => $data['id'], 'message_text' => $message_text, 'status' => DialogMessage::NORMAL_MESSAGE]);
        if (!$updateDialogMessageResult) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, '私信图片更新失败');
        } else {
            $dialogMessage = DialogMessage::query()->where('id', $dialogMessageId)->first();
            $dialog = Dialog::query()->where('id', $dialogMessage->dialog_id)->first();
            $lastDialogMessage = DialogMessage::query()->where('id', $dialog->dialog_message_id)->first();
            if ($dialog->dialog_message_id == 0 ||
                (isset($lastDialogMessage['created_at']) && ($lastDialogMessage['created_at'] < $dialogMessage['created_at']))) {
                $updateDialogResult = Dialog::query()
                    ->where('id', $dialogMessage->dialog_id)
                    ->update(['dialog_message_id' => $dialogMessage->id]);
                if (!$updateDialogResult) {
                    $this->outPut(ResponseCode::INTERNAL_ERROR, '最新对话更新失败');
                }
            }
        }
        return true;
    }
}
