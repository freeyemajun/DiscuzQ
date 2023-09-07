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

use App\Common\ResponseCode;
use App\Models\Attachment;
use App\Models\Thread;
use App\Models\AttachmentShare;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Discuz\Base\DzqController;
use Illuminate\Contracts\Routing\UrlGenerator;

class ShareAttachmentController extends DzqController
{
    use DownloadAuthTrait;

    protected $url;

    protected $thread;

    public function __construct(UrlGenerator $url)
    {
        $this->url = $url;
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        $threadId =$this->inPut('threadId');
        $attachmentsId = $this->inPut('attachmentsId');
        $user = $this->user;

        $thread = Thread::getThreadTomInfoById($threadId);
        if (!$thread) {
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND);
        }
        $attachment = Attachment::getOneAttachment($attachmentsId);
        if (empty($attachment)) {
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND);
        }
        //判断附件是否需要付费
        if ($thread->price_type && in_array($attachmentsId, json_decode($thread->price_ids, 1))) {
            $this->checkDownloadAttachment($thread, $user, $userRepo);
        }

        if (!$userRepo->canViewThreadAttachment($user, $thread)) {
            $this->outPut(ResponseCode::UNAUTHORIZED, '无权限查看该附件');
        }

        $this->thread = $thread;
        return true;
    }

    public function main()
    {
        $user = $this->user;
        $data = [
            'threadId' => $this->inPut('threadId'),
            'attachmentsId' => $this->inPut('attachmentsId'),
        ];

        $this->dzqValidate($data, [
            'threadId' => 'required|int',
            'attachmentsId' => 'required|int'
        ]);

        if (!($user->isGuest())) {
            $count = AttachmentShare::query()
                ->where(['attachments_id' => $data['attachmentsId'], 'user_id' => $user->id])
                ->where('created_at', '>=', Carbon::now()->modify('-1 minutes'))
                ->count('attachments_id');
            if ($count >= 2) {
                $this->outPut(ResponseCode::NET_ERROR, '操作太快，请稍后再试');
            }
        }
        $docValue = json_decode($this->thread->value, true);

        if (!isset($docValue['docIds']) || !is_array($docValue['docIds']) || !in_array($data['attachmentsId'], $docValue['docIds'])) {
            $this->outPut(ResponseCode::RESOURCE_NOT_FOUND);
        }

        $sign = $this->sign($data);

        $attachmentShare = new AttachmentShare;
        $attachmentShare->sign = $sign;
        $attachmentShare->attachments_id = $data['attachmentsId'];
        $attachmentShare->user_id = $user->id;
        $attachmentShare->expired_at = Carbon::now()->modify('+10 minutes');
        $attachmentShare->save();

        $this->outPut(ResponseCode::SUCCESS, '', [
            'url' => $this->url->to('/api/v3/attachment.download') . '?sign=' . $sign . '&attachmentsId=' . $data['attachmentsId']. '&threadId='. $data['threadId']
        ]);
    }

    //生成唯一标识
    public function sign($data)
    {
        $stringArr = openssl_random_pseudo_bytes(16);
        $stringArr[6] = chr(ord($stringArr[6]) & 0x0f | 0x40); // set version to 0100
        $stringArr[8] = chr(ord($stringArr[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        $string =  vsprintf(
            '%s%s%s%s%s%s%s%s',
            str_split(bin2hex($stringArr), 4)
        );
        return md5($string.$data['threadId'].$data['attachmentsId']);
    }
}
