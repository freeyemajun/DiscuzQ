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

namespace App\Api\Controller\Dialog;

use App\Commands\Dialog\CreateDialog;
use App\Common\ResponseCode;
use App\Models\DialogMessage;
use App\Providers\DialogMessageServiceProvider;
use App\Repositories\UserRepository;
use Discuz\Base\DzqController;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Validation\ValidationException;

class CreateDialogController extends DzqController
{
    protected $validation;

    /**
     * @var Dispatcher
     */
    protected $bus;

    public $providers = [
        DialogMessageServiceProvider::class,
    ];

    public function __construct(Dispatcher $bus, Factory $validation)
    {
        $this->validation = $validation;
        $this->bus = $bus;
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        if ($this->user->isGuest()) {
            $this->outPut(ResponseCode::JUMP_TO_LOGIN);
        }
        if (DialogMessage::isDisable()) {
            $this->outPut(ResponseCode::DIALOG_MESSAGE_DISABLE);
        }
        return $userRepo->canCreateDialog($this->user);
    }

    public function main()
    {
        $actor = $this->user;
        $data = [
            'message_text'=>$this->inPut('messageText'),
            'recipientUserId'=>$this->inPut('recipientUserId'),
            'isImage'=>$this->inPut('isImage'),
            'image_url' => $this->inPut('imageUrl') ?? '',
            'attachment_id' => $this->inPut('attachmentId') ?? 0
        ];

        if (empty($data['recipientUserId'])) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '接收者用户id不能为空');
        }

        try {
            $this->validation->make($data, [
                'message_text'  => 'sometimes:messageText|max:450',
                'image_url'     => 'required_with:attachment_id|string',
                'attachment_id' => 'required_with:image_url|int|min:1',
                'isImage' => 'required|bool'
            ])->validate();
        } catch (ValidationException $e) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, $e->validator->getMessageBag()->first());
        }

        if (!$data['isImage'] && empty($data['message_text']) && empty($data['attachment_id'])) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '发送内容不能为空！');
        }

        try {
            $res = $this->bus->dispatch(
                new CreateDialog($actor, $data)
            );
        } catch (\Exception $e) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, $e->getMessage());
        }

        $data = [
            'dialogId' => $res['dialogId'],
            'dialogMessageId' => $res['dialogMessageId']
        ];

        $this->outPut(ResponseCode::SUCCESS, '已发送', $data);
    }
}
