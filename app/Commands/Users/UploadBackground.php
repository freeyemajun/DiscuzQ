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

namespace App\Commands\Users;

use App\Common\ResponseCode;
use App\Exceptions\UploadException;
use App\Models\User;
use App\Repositories\UserRepository;
use App\User\BackgroundUploader;
use App\Validators\BackgroundValidator;
use Discuz\Auth\AssertPermissionTrait;
use Discuz\Auth\Exception\PermissionDeniedException;
use Discuz\Common\Utils;
use Intervention\Image\ImageManager;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

class UploadBackground
{
    use AssertPermissionTrait;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var UploadedFileInterface
     */
    public $avatar;

    /**
     * @var User
     */
    public $actor;

    /**
     * @var UserRepository
     */
    protected $users;

    /**
     * @var BackgroundValidator
     */
    protected $uploader;

    /**
     * @var BackgroundValidator
     */
    protected $validator;

    /**
     * @param int $userId The ID of the user to upload the avatar for.
     * @param UploadedFileInterface $avatar The avatar file to upload.
     * @param User $actor The user performing the action.
     */
    public function __construct($userId, UploadedFileInterface $avatar, User $actor)
    {
        $this->userId = $userId;
        $this->avatar = $avatar;
        $this->actor = $actor;
    }

    /**
     * @param UserRepository $users
     * @param BackgroundUploader $uploader
     * @param BackgroundValidator $validator
     * @return User|mixed
     * @throws PermissionDeniedException
     * @throws UploadException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(UserRepository $users, BackgroundUploader $uploader, BackgroundValidator $validator)
    {
        $this->users = $users;
        $this->uploader = $uploader;
        $this->validator = $validator;

        return $this();
    }

    /**
     * @return mixed
     * @throws PermissionDeniedException
     * @throws UploadException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function __invoke()
    {
        $user = User::query()->where('id', $this->userId)->first();

        $ext = pathinfo($this->avatar->getClientFilename(), PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), ['jpeg', 'jpg', 'gif', 'png'])) {
            Utils::outPut(ResponseCode::INVALID_PARAMETER, '文件后缀名不合法');
        }
        $ext = $ext ? ".$ext" : '';

        $tmpFile = tempnam(storage_path('/tmp'), 'background');
        $tmpFileWithExt = $tmpFile . $ext;

        $this->avatar->moveTo($tmpFileWithExt);

        try {
            $file = new SymfonyUploadedFile(
                $tmpFileWithExt,
                $this->avatar->getClientFilename(),
                $this->avatar->getClientMediaType(),
                $this->avatar->getError(),
                true
            );
            $this->validator->valid(['background' => $file]);
            $image = (new ImageManager())->make($tmpFileWithExt);
            $this->uploader->upload($user, $image);
            $user->save();
        } finally {
            @unlink($tmpFile);
            @unlink($tmpFileWithExt);
        }
        return $user;
    }
}
