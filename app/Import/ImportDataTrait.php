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

namespace App\Import;

use App\Api\Controller\Attachment\AttachmentTrait;
use App\Censor\Censor;
use App\Commands\Attachment\AttachmentUploader;
use App\Commands\Users\RegisterCrawlerUser as RegisterUser;
use App\Commands\Users\UploadCrawlerAvatar;
use App\Common\CacheKey;
use App\Models\Attachment;
use App\Models\Category;
use App\Models\Post;
use App\Models\Thread;
use App\Models\ThreadTag;
use App\Models\ThreadTom;
use App\Models\ThreadTopic;
use App\Models\ThreadVideo;
use App\Models\Topic;
use App\Models\User;
use App\Repositories\UserRepository;
use App\User\CrawlerAvatarUploader;
use App\Validators\AttachmentValidator;
use App\Validators\AvatarValidator;
use App\Validators\UserValidator;
use App\Traits\VideoCloudTrait;
use Carbon\Carbon;
use Discuz\Auth\Guest;
use Discuz\Contracts\Setting\SettingsRepository;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Events\Dispatcher as Events;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Filesystem\Factory as FactoryFilesystem;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Laminas\Diactoros\UploadedFile as RequestUploadedFile;

trait ImportDataTrait
{
    use ImportLockFileTrait;

    use VideoCloudTrait;

    use AttachmentTrait;

    protected $userRepo;

    protected $bus;

    protected $settings;

    protected $censor;

    protected $userValidator;

    protected $avatarValidator;

    protected $crawlerAvatarUploader;

    protected $attachmentValidator;

    protected $uploader;

    protected $image;

    protected $db;

    protected $filesystem;

    protected $events;

    private $categoryId;

    private $topic;

    private $startCrawlerTime;

    private $crawlerPlatform;

    private $cookie;

    private $userAgent;

    private $importDataLockFilePath;

    private $autoImportDataLockFilePath;

    public function __construct(
        UserRepository $userRepo,
        Dispatcher $bus,
        SettingsRepository $settings,
        Events $events,
        Censor $censor,
        UserValidator $userValidator,
        AvatarValidator $avatarValidator,
        AttachmentValidator $attachmentValidator,
        ImageManager $image,
        ConnectionInterface $db,
        Filesystem $filesystem
    ) {
        $this->userRepo = $userRepo;
        $this->bus = $bus;
        $this->settings = $settings;
        $this->events = $events;
        $this->censor = $censor;
        $this->userValidator = $userValidator;
        $this->avatarValidator = $avatarValidator;
        $this->attachmentValidator = $attachmentValidator;
        $this->image = $image;
        $this->db = $db;
        $this->filesystem = $filesystem;
        $this->uploader = new AttachmentUploader($this->filesystem, $this->settings);
        $this->crawlerAvatarUploader = new CrawlerAvatarUploader($this->censor, $this->filesystem, $this->settings);
        $publicPath = public_path();
        $this->importDataLockFilePath = $publicPath . DIRECTORY_SEPARATOR . 'importDataLock.conf';
        $this->autoImportDataLockFilePath = $publicPath . DIRECTORY_SEPARATOR . 'autoImportDataLock.conf';
        parent::__construct();
    }

    public function importDataMain($optionData)
    {
        $topic = $optionData['topic'];
        $number = $optionData['number'];
        if ($number < 0 || $number > 100 || floor($number) != $number) {
            throw new \Exception('number参数错误');
        }
        if (!empty($number) && empty($topic)) {
            throw new \Exception('缺少关键词！');
        }

        $category = Category::query()->select('id')->orderBy('id', 'asc')->first()->toArray();
        if (empty($category)) {
            throw new \Exception('缺少分类，请您先创建内容分类！');
        }
        $categoryId = $category['id'];

        if (isset($optionData['auto']) && $optionData['auto']) {
            $autoImportParameters = $optionData;
            unset($autoImportParameters['auto']);
            $checkResult = $this->checkAutoImportParameters($autoImportParameters, 'WeiBo');
            if ($checkResult == 1) {
                $this->insertLogs('----The automatic import task is written successfully.----');
            } elseif ($checkResult == 2) {
                $this->insertLogs('----The automatic import task is written successfully,and overwrites the previous task.----');
            }
            return true;
        }

        if (empty($topic) && empty($number)) {
            if (!file_exists($this->autoImportDataLockFilePath)) {
                return false;
            }

            $autoImportDataLockFileContent = $this->getLockFileContent($this->autoImportDataLockFilePath);
            if ($autoImportDataLockFileContent['platform'] != 'WeiBo') {
                return false;
            }

            $fileData = $this->getAutoImportData($autoImportDataLockFileContent);
            if ($fileData && !empty($fileData['topic']) && !empty($fileData['number'])) {
                $this->insertPlatformData($fileData, $categoryId, true);
            }
            return true;
        } else {
            if (empty($number)) {
                throw new \Exception('number数值必须大于0！');
            }
            if (file_exists($this->importDataLockFilePath)) {
                $lockFileContent = $this->getLockFileContent($this->importDataLockFilePath);
                if ($lockFileContent['runtime'] < Thread::CREATE_CRAWLER_DATA_LIMIT_MINUTE_TIME && $lockFileContent['status'] == Thread::IMPORT_PROCESSING) {
                    $this->insertLogs('----The content import process has been occupied,You cannot start a new process.----');
                    return false;
                } elseif ($lockFileContent['runtime'] > Thread::CREATE_CRAWLER_DATA_LIMIT_MINUTE_TIME) {
                    $this->insertLogs('----Execution timed out.The file lock has been deleted.----');
                    CacheKey::delListCache();
                    $this->changeLockFileContent($this->importDataLockFilePath, 0, Thread::PROCESS_OF_START_INSERT_CRAWLER_DATA, Thread::IMPORT_TIMEOUT_ENDING, $lockFileContent['topic']);
                    return false;
                }
            }

            $this->insertPlatformData($optionData, $categoryId, false);
            return true;
        }
    }

    private function insertPlatformData($parameter, $categoryId, $auto)
    {
        $topic = $parameter['topic'];
        $startCrawlerTime = time();
        $this->changeLockFileContent($this->importDataLockFilePath, $startCrawlerTime, Thread::PROCESS_OF_START_INSERT_CRAWLER_DATA, Thread::IMPORT_PROCESSING, $topic);
        if ($auto) {
            $this->insertLogs('----Start automatic import.----');
            $this->changeLastImportFileContent($startCrawlerTime, Thread::AUTO_IMPORT_HAVE_FINISHED);
        } else {
            $this->insertLogs('----Start import.----');
        }

        $data = $this->getPlatformData($parameter);

        if (empty($data)) {
            $this->insertLogs('----No data is obtained. Process ends.----');
            $this->changeLockFileContent($this->importDataLockFilePath, 0, Thread::PROCESS_OF_START_INSERT_CRAWLER_DATA, Thread::IMPORT_NOTHING_ENDING, $topic);
            return false;
        }

        $processPercent = 0;
        $averageProcessPercent = 95 / count($data);
        $totalImportDataNumber = 0;
        foreach ($data as $value) {
            $threadId = $this->insertCrawlerData($topic, $categoryId, $value);
            $totalImportDataNumber++;
            $processPercent = $processPercent + $averageProcessPercent;
            $this->changeLockFileContent($this->importDataLockFilePath, $startCrawlerTime, $processPercent, Thread::IMPORT_PROCESSING, $topic, $totalImportDataNumber);
            $this->insertLogs('----Insert a new thread success.The thread id is ' . $threadId . '.The progress is ' . floor((string)$processPercent) . '%.----');
        }
        Category::refreshThreadCountV3($this->categoryId);
        $this->changeLockFileContent($this->importDataLockFilePath, 0, Thread::PROCESS_OF_END_INSERT_CRAWLER_DATA, Thread::IMPORT_NORMAL_ENDING, $topic, $totalImportDataNumber);
        CacheKey::delListCache();
        $this->insertLogs("----Importing crawler data success.The progress is 100%.The importing' data total number is " . $totalImportDataNumber . '.----');
        return true;
    }

    private function insertCrawlerData($topic, $categoryId, $data)
    {
        if (empty($data)) {
            throw new \Exception('未接收到相关数据！');
        }

        $this->topic = $topic;
        $this->categoryId = $categoryId;

        $oldUserData = User::query()->select('id', 'username', 'nickname')->get()->toArray();
        $oldUsernameData = array_column($oldUserData, null, 'username');
        $oldNicknameData = array_column($oldUserData, 'nickname');
        $oldTopics = Topic::query()->select('id', 'user_id', 'content', 'thread_count', 'view_count')->get()->toArray();
        $oldTopics = array_column($oldTopics, null, 'content');
        try {
            $this->db->beginTransaction();

            if (!isset($data['user']) || !isset($data['forum'])) {
                throw new \Exception('数据格式有误');
            }

            [$oldUsernameData, $oldNicknameData, $userData] = $this->insertUser($oldUsernameData, $oldNicknameData, $data['user']);
            $threadUserId = $userData->id;

            $newThread = $this->insertThread($data['forum'], $threadUserId);
            $threadId = $newThread->id;
            $threadContent = $this->changeThreadContent($oldTopics, $data['forum'], $threadUserId, $threadId);
            $this->insertContent($threadId, $threadUserId, $threadContent, Post::FIRST_YES, $newThread->created_at);

            if (isset($data['comment']) && !empty($data['comment'])) {
                $postNumber = $this->insertPosts($data['comment'], $oldUsernameData, $oldNicknameData, $threadId);
            }

            $this->db->commit();

            $newThread->is_draft = Thread::BOOL_NO;
            if (isset($postNumber)) {
                $newThread->post_count = $newThread->post_count + $postNumber;
                $newThread->view_count = $newThread->view_count + $postNumber;
            }
            $newThread->save();
            $userData->thread_count = $userData->thread_count + 1;
            $userData->save();
            return $newThread->id;
        } catch (\Exception $e) {
            $this->db->rollback();
            Category::refreshThreadCountV3($this->categoryId);
            CacheKey::delListCache();
            $this->changeLockFileContent($this->importDataLockFilePath, 0, Thread::PROCESS_OF_START_INSERT_CRAWLER_DATA, Thread::IMPORT_ABNORMAL_ENDING, $this->topic, 0);
            throw new \Exception('数据导入失败：' . $e->getMessage());
        }
    }

    private function insertUser($oldUsernameData, $oldNicknameData, $user)
    {
        if (!isset($user['nickname'])) {
            throw new \Exception('数据格式有误');
        }

        $username = 'robotdzq_' . $user['nickname'];
        if (isset($oldUsernameData[$username])) {
            $userData = User::query()->where('id', $oldUsernameData[$username]['id'])->first();
            return [$oldUsernameData, $oldNicknameData, $userData];
        }

        if (in_array($user['nickname'], $oldNicknameData)) {
            $user['nickname'] = User::addStringToNickname($user['nickname']);
        }

        $randomNumber = mt_rand(111111, 999999);
        $password = $user['nickname'] . $randomNumber;
        $data = [
            'username' => $username,
            'nickname' => $user['nickname'],
            'password' => $password,
            'passwordConfirmation' => $password,
            'dataType' => 'crawler'
        ];
        $newGuest = new Guest();
        $register = new RegisterUser($newGuest, $data);
        try {
            $registerUserResult = $register->handle($this->events, $this->censor, $this->settings, $this->userValidator);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, "users_username_unique") !== false) {
                $data['username'] = User::addStringToUsername($data['username']);
                $register = new RegisterUser($newGuest, $data);
                $registerUserResult = $register->handle($this->events, $this->censor, $this->settings, $this->userValidator);
            }
        }

        if (isset($user['avatar']) && !empty($user['avatar'])) {
            $this->uploadCrawlerUserAvatar($user['avatar'], $registerUserResult);
        }

        if ($registerUserResult) {
            $registerUserResult->status = User::STATUS_NORMAL;
            $registerUserResult->save();
        }

        $oldUsernameData = array_merge($oldUsernameData, [$registerUserResult->username => [
            'id' => $registerUserResult->id,
            'username' => $registerUserResult->username,
            'nickname' => $registerUserResult->nickname
        ]]);
        $oldNicknameData = array_merge($oldNicknameData, [$registerUserResult->nickname => [
            'id' => $registerUserResult->id,
            'username' => $registerUserResult->username,
            'nickname' => $registerUserResult->nickname
        ]]);

        return [$oldUsernameData, $oldNicknameData, $registerUserResult];
    }

    private function uploadCrawlerUserAvatar($avatar, $registerUserData)
    {
        $mimeType = $this->getAttachmentMimeType($avatar);
        $fileExt = substr($mimeType, strpos($mimeType, '/') + strlen('/'));
        if (!in_array($fileExt, ['gif', 'png', 'jpg', 'jpeg', 'jpe', 'heic'])) {
            return false;
        }

        $fileName = Str::random(40) . '.' . $fileExt;
        set_time_limit(0);
        $file = $this->getFileContents($avatar);
        if (!$file) {
            return false;
        }

        $tmpFile = tempnam(storage_path('/tmp'), 'avatar');
        $fileExt = $fileExt ? ".$fileExt" : '';
        $tmpFileWithExt = $tmpFile . $fileExt;
        $avatarSize = @file_put_contents($tmpFileWithExt, $file);
        $avatarFile = new RequestUploadedFile(
            $tmpFile,
            $avatarSize,
            0,
            $fileName,
            $mimeType
        );

        $avatar = new UploadCrawlerAvatar($registerUserData->id, $avatarFile, $registerUserData, $tmpFile);
        $uploadAvatarResult = $avatar->handle($this->userRepo, $this->crawlerAvatarUploader, $this->avatarValidator);
        return $uploadAvatarResult;
    }

    private function insertThread($forumData, $threadUserId)
    {
        $createdAt = strtotime($forumData['createdAt']) ? $forumData['createdAt'] : Carbon::now();
        $newThread = new Thread();
        $newThread->user_id = $threadUserId;
        $newThread->category_id = $this->categoryId;
        $newThread->title = $forumData['text']['title'] ?? '';
        $newThread->type = Thread::TYPE_OF_ALL;
        $newThread->post_count = 1;
        $newThread->share_count = mt_rand(0, 100);
        $newThread->view_count = mt_rand(0, 100);
        $newThread->address = $newThread->location = $forumData['text']['position'] ?? '';
        $newThread->is_draft = Thread::BOOL_YES;
        $newThread->is_approved = Thread::BOOL_YES;
        $newThread->is_anonymous = Thread::BOOL_NO;
        $newThread->created_at = $newThread->updated_at = $createdAt;
        $newThread->source = Thread::DATA_PLATFORM_OF_IMPORT;
        $newThread->save();
        return $newThread;
    }

    private function changeThreadContent($oldTopics, $data, $userId, $threadId)
    {
        if (!isset($data['text']['text']) || empty($data['text']['text'])) {
            throw new \Exception('数据格式有误');
        }

        $content = $data['text']['text'];
        $topicIds = [];
        $imageIds = [];
        $attachmentIds = [];
        $videoId = 0;
        $audioId = 0;

        $content = $this->changeContentImg($content, $userId, Attachment::TYPE_OF_IMAGE);
        if (isset($data['text']['topicList']) && !empty($data['text']['topicList'])) {
            [$content, $topicIds] = $this->insertTopics($oldTopics, $content, $userId, $data['text']['topicList']);
        }

        if (isset($data['images']) && !empty($data['images'])) {
            $imageIds = $this->insertImages($userId, $data['images'], Attachment::TYPE_OF_IMAGE);
            $imageIds = array_column($imageIds, 'id');
        }

        if (isset($data['attachments']) && !empty($data['attachments'])) {
            $attachmentIds = $this->insertImages($userId, $data['attachments'], Attachment::TYPE_OF_FILE);
            $attachmentIds = array_column($attachmentIds, 'id');
        }

        if (isset($data['media'])) {
            if (isset($data['media']['video']) && !empty($data['media']['video'])) {
                $newVideoData = $this->insertMedia($data['media']['video'], ThreadVideo::TYPE_OF_VIDEO, $userId, $threadId);
                $videoId = $newVideoData['videoId'] ?? 0;
            }
            if (isset($data['media']['audio']) && !empty($data['media']['audio'])) {
                $newAudioData = $this->insertMedia($data['media']['audio'], ThreadVideo::TYPE_OF_AUDIO, $userId, $threadId);
                $audioId = $newAudioData['videoId'] ?? 0;
            }
        }

        if (isset($data['contentMedia']['videos']) && !empty($data['contentMedia']['videos'])) {
            $content = $this->changeContentMedia($content, $userId, $threadId, ThreadVideo::TYPE_OF_VIDEO, $data['contentMedia']['videos']);
        }
        if (isset($data['contentMedia']['audio']) && !empty($data['contentMedia']['audio'])) {
            $content = $this->changeContentMedia($content, $userId, $threadId, ThreadVideo::TYPE_OF_AUDIO, $data['contentMedia']['audio']);
        }


        // 写入对应关联关系
        if (!empty($topicIds)) {
            $this->insertThreadTopics($threadId, $topicIds);
        }
        $this->insertTom($threadId, $imageIds, $attachmentIds, $videoId, $audioId);

        return $content;
    }

    private function changeContentImg($content, $userId, $type)
    {
        $postPicturesSrc = $this->getImagesSrc($content);
        $insertImagesResult = $this->insertImages($userId, $postPicturesSrc, $type);
        if (!empty($insertImagesResult)) {
            foreach ($insertImagesResult as $value) {
                if (in_array($value['oldImageSrc'], $postPicturesSrc)) {
                    $content = str_replace($value['oldImageSrc'] . '"', $value['newImageSrc'] . '" alt="attachmentId-' . $value['id'] . '" ', $content);
                }
            }
        }

        return $content;
    }

    private function changeContentMedia($content, $userId, $threadId, $type, $mediaUrl)
    {
        foreach ($mediaUrl as $value) {
            $data = $this->insertMedia($value, $type, $userId, $threadId);
            $videoId = $data['videoId'] ?? 0;
            if ($videoId) {
                $content = str_replace($value . '"', '" alt="videoId-' . $videoId . '" ', $content);
            }
        }
        return $content;
    }

    private function getImagesSrc($content)
    {
        $imgSrcArr = [];
        //首先将富文本字符串中的 img 标签进行匹配
        $pattern_imgTag = '/<img\b.*?(?:\>|\/>)/i';
        preg_match_all($pattern_imgTag, $content, $matchIMG);
        if (isset($matchIMG[0])) {
            foreach ($matchIMG[0] as $key => $imgTag) {
                //进一步提取 img标签中的 src属性信息
                $pattern_src = '/\bsrc\b\s*=\s*[\'\"]?([^\'\"]*)[\'\"]?/i';
                preg_match_all($pattern_src, $imgTag, $matchSrc);
                if (isset($matchSrc[1])) {
                    foreach ($matchSrc[1] as $src) {
                        //将匹配到的src信息压入数组
                        $imgSrcArr[] = $src;
                    }
                }
            }
        }
        return $imgSrcArr;
    }

    private function insertImages($userId, $imagesSrc, $type)
    {
        $imageIds = [];
        $actor = User::query()->where('id', $userId)->first();
        $ipAddress = '';
        $imgExt = $this->settings->get('support_img_ext', 'default', 0);
        $attachmentExt = $this->settings->get('support_file_ext', 'default', 0);
        $allowExt = $type === Attachment::TYPE_OF_IMAGE ? explode(',', $imgExt) : explode(',', $attachmentExt);

        foreach ($imagesSrc as $key => $value) {
            $value = htmlspecialchars_decode($value);
            $originFileName = '';

            set_time_limit(0);
            $mimeType = $this->getAttachmentMimeType($value);
            $fileExt = substr($mimeType, strpos($mimeType, '/') + strlen('/'));
            if (!in_array($fileExt, $allowExt)) {
                $originFileName = $this->getContentDispositionFileName($value);
                if (empty($originFileName)) {
                    continue;
                }
                $fileExt = substr($originFileName, strrpos($originFileName, '.') + 1);
                if (!in_array($fileExt, $allowExt)) {
                    continue;
                }
            }
            $fileName = Str::random(40) . '.' . $fileExt;

            $file = $this->getFileContents($value);
            $imageSize = strlen($file);
            $maxSize = $this->settings->get('support_max_size', 'default', 0) * 1024 * 1024;
            if ($file && $imageSize > 0 && $imageSize < $maxSize) {
                ini_set('memory_limit', -1);
                $tmpFile = tempnam(storage_path('/tmp'), 'attachment');
                $ext = $fileExt ? ".$fileExt" : '';
                $tmpFileWithExt = $tmpFile . $ext;
                $putResult = @file_put_contents($tmpFileWithExt, $file);
                if (!$putResult) {
                    return false;
                }

                $imageFile = new UploadedFile(
                    $tmpFileWithExt,
                    $fileName,
                    $mimeType,
                    0,
                    true
                );

                if (strtolower($ext) != 'gif') {
                    if ((int)$type === Attachment::TYPE_OF_IMAGE && extension_loaded('exif')) {
                        $this->image->make($tmpFileWithExt)->orientate()->save();
                    }
                }

                // 上传
                $this->uploader->uploadCrawlerData($imageFile, $type);
                list($width, $height) = getimagesize($tmpFileWithExt);
                $attachment = Attachment::build(
                    $actor->id,
                    $type,
                    $this->uploader->fileName,
                    $this->uploader->getPath(),
                    $originFileName ?: $imageFile->getClientOriginalName(),
                    $imageFile->getSize(),
                    $imageFile->getClientMimeType(),
                    $this->settings->get('qcloud_cos', 'qcloud') ? 1 : 0,
                    Attachment::APPROVED,
                    $ipAddress,
                    0,
                    $width ?: 0,
                    $height ?: 0
                );

                $attachment->save();
                @unlink($tmpFile);
                @unlink($tmpFileWithExt);

                if ($attachment->is_remote) {
                    $url = $this->settings->get('qcloud_cos_sign_url', 'qcloud', false)
                        ? app()->make(FactoryFilesystem::class)->disk('attachment_cos')->temporaryUrl($attachment->full_path, Carbon::now()->addDay())
                        : app()->make(FactoryFilesystem::class)->disk('attachment_cos')->url($attachment->full_path);
                } else {
                    $url = app()->make(FactoryFilesystem::class)->disk('attachment')->url($attachment->full_path);
                }

                $imageIds[] = [
                    'id' => $attachment->id,
                    'oldImageSrc' => $value,
                    'newImageSrc' => $url
                ];
            }
        }

        return $imageIds;
    }

    private function insertTopics($oldTopics, $content, $userId, $topicList)
    {
        $topicIds = [];
        foreach ($topicList as $key => $value) {
            if (isset($oldTopics[$value])) {
                $topicIds[] = $oldTopics[$value]['id'];
                $html = sprintf('<span id="topic" value="%s">#%s#</span>', $oldTopics[$value]['id'], $value);
                $topicContent = $oldTopics[$value]['content'];
            } else {
                $newTopic = new Topic();
                $newTopic->user_id = $userId;
                $newTopic->content = $value;
                $newTopic->created_at = $newTopic->updated_at = Carbon::now();
                $newTopic->save();
                $topicIds[] = $newTopic->id;
                $html = sprintf('<span id="topic" value="%s">#%s#</span>', $newTopic->id, $newTopic->content);
                $topicContent = $newTopic->content;
                $oldTopics = array_merge($oldTopics, [
                    $newTopic->content => [
                        'id' => $newTopic->id,
                        'user_id' => $newTopic->user_id,
                        'content' => $newTopic->content
                    ]
                ]);
            }

            if (!strpos($content, $html)) {
                $searchTopicContent = '#' . $topicContent . '#';
                $content = str_replace($searchTopicContent, $html, $content);
            }
        }

        return [$content, $topicIds];
    }

    private function insertMedia($mediaUrl, $type, $userId, $threadId)
    {
        $newVideoData = [];
        if (!empty($mediaUrl)) {
            if ($type == ThreadVideo::TYPE_OF_AUDIO) {
                $mimeType = $this->getAttachmentMimeType($mediaUrl);
                $ext = substr($mimeType, strrpos($mimeType, '/') + 1);
                $videoId = $this->videoUpload($userId, $threadId, $mediaUrl, $this->settings, $ext);
            } else {
                $videoId = $this->videoUpload($userId, $threadId, $mediaUrl, $this->settings);
            }
            if ($videoId) {
                $video = ThreadVideo::query()->where('id', $videoId)->first();
                $video->type = $type;
                $video->save();

                $newVideoData['videoId'] = $videoId;
                $newVideoData['videoType'] = $type;
                $newVideoData['oldUrl'] = $mediaUrl;
            }
        }
        return $newVideoData;
    }

    private function insertThreadTopics($threadId, $topicIds)
    {
        $threadTopic = [];
        foreach ($topicIds as $key => $value) {
            $threadTopic[] = [
                'thread_id' => $threadId,
                'topic_id' => $value,
                'created_at' => Carbon::now()
            ];
        }
        $threadTopic = array_column($threadTopic, null, 'topic_id');
        $insertThreadTopicsResult = ThreadTopic::query()->insert($threadTopic);
        return $insertThreadTopicsResult;
    }

    private function insertTom($threadId, $imageIds, $attachmentIds, $videoId, $audioId)
    {
        $threadTomData = [];
        $threadTagData[] = [
            'thread_id' => $threadId,
            'tag' => ThreadTag::TEXT,
        ];
        if (!empty($imageIds)) {
            [$threadTomData, $threadTagData] = $this->getTomData($threadId, ThreadTag::IMAGE, 'imageIds', $imageIds, $threadTomData, $threadTagData);
        }

        if (!empty($attachmentIds)) {
            [$threadTomData, $threadTagData] = $this->getTomData($threadId, ThreadTag::DOC, 'docIds', $attachmentIds, $threadTomData, $threadTagData);
        }

        if (!empty($videoId)) {
            [$threadTomData, $threadTagData] = $this->getTomData($threadId, ThreadTag::VIDEO, 'videoId', $videoId, $threadTomData, $threadTagData);
        }

        if (!empty($audioId)) {
            [$threadTomData, $threadTagData] = $this->getTomData($threadId, ThreadTag::VOICE, 'audio', $audioId, $threadTomData, $threadTagData);
        }

        ThreadTom::query()->insert($threadTomData);
        ThreadTag::query()->insert($threadTagData);

        return true;
    }

    private function getTomData($threadId, $type, $typeStr, $ids, $threadTomData, $threadTagData)
    {
        $threadTomData[] = [
            'thread_id' => $threadId,
            'tom_type' => $type,
            'key' => $type,
            'value' => json_encode([$typeStr => $ids], 256)
        ];

        $threadTagData[] = [
            'thread_id' => $threadId,
            'tag' => $type
        ];

        return [$threadTomData, $threadTagData];
    }

    private function insertContent($threadId, $userId, $content, $isFirst, $createdAt)
    {
        $post = new Post();
        $post->user_id = $userId;
        $post->thread_id = $threadId;
        $post->content = $content;
        $post->is_first = $isFirst;
        $post->is_approved = Post::APPROVED_YES;
        $post->ip = '';
        $post->port = 0;
        $post->created_at = $post->updated_at = $createdAt;
        $post->save();
        return $post;
    }

    private function insertPosts($commentList, $oldUsernameData, $oldNicknameData, $threadId)
    {
        $postNumber = 0;
        foreach ($commentList as $value) {
            if (!isset($value['user']) || empty($value['user']) ||
                !isset($value['comment']['text']['text']) ||
                empty($value['comment']['text']['text'])) {
                continue;
            }

            [$oldUsernameData, $oldNicknameData, $userData] = $this->insertUser($oldUsernameData, $oldNicknameData, $value['user']);
            $userId = $userData->id;
            $imageIds = [];
            $content = $value['comment']['text']['text'];
            if (!empty($value['comment']['images'])) {
                $insertAttachmentResult = $this->insertImages($userId, $value['comment']['images'], Attachment::TYPE_OF_IMAGE);
                $imageIds = array_column($insertAttachmentResult, 'id');
            }

            $createdAt = strtotime($value['comment']['createdAt']) ? $value['comment']['createdAt'] : Carbon::now();
            $newPost = $this->insertContent($threadId, $userId, $content, Post::FIRST_NO, $createdAt);

            if ($newPost) {
                if (!empty($imageIds)) {
                    Attachment::query()->whereIn('id', $imageIds)->update(['type_id' => $newPost->id]);
                }
                $postNumber++;
            }
        }
        return $postNumber;
    }

    private function insertLogs($logString)
    {
        $this->info($logString);
        app('log')->info($logString);
        return true;
    }

    private function getContentDispositionFileName($url)
    {
        $fileName = '';
        $responseHeader = $this->getResponseHeader($url);
        if (empty($responseHeader)) {
            return $fileName;
        }

        $responseHeader = explode(';', $responseHeader);
        foreach ($responseHeader as $value) {
            if (strpos($value, 'filename=') !== false) {
                $fileName = substr($value, strrpos($value, '=') + 1, strlen($value));
                $fileName = str_replace('"', '', $fileName);
            }
        }

        $originEncoding = mb_detect_encoding($fileName, array("ASCII", "UTF-8", "GB2312", "GBK", "BIG5"));
        if ($originEncoding != 'UTF-8') {
            if ($originEncoding == 'ASCII') {
                $fileName = urldecode($fileName);
            } else {
                $fileName = mb_convert_encoding($fileName, "UTF-8", $originEncoding);
            }
        }

        return $fileName;
    }

    private function getResponseHeader($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36'); // 模拟用户使用的浏览器
        curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);       //链接超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);       //设置超时时间
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);  //对于web页等有重定向的，要加上这个设置，才能真正访问到页面
        curl_setopt($ch, CURLOPT_COOKIE, '');
        $responseHeader = curl_exec($ch);
        curl_close($ch);
        return $responseHeader;
    }
}
