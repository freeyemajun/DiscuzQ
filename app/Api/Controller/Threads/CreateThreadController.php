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

namespace App\Api\Controller\Threads;

use App\Common\CacheKey;
use App\Common\Platform;
use App\Common\ResponseCode;
use App\Models\Category;
use App\Models\Group;
use App\Models\Post;
use App\Models\SiteInfoDaily;
use App\Models\Thread;
use App\Models\ThreadTag;
use App\Models\ThreadTom;
use App\Models\User;
use App\Models\ChatGptKernel;
use App\Modules\ThreadTom\TomConfig;
use App\Repositories\UserRepository;
use App\Settings\SettingsRepository;
use Discuz\Auth\Exception\PermissionDeniedException;
use Discuz\Base\DzqController;
use Discuz\Common\Utils;

class CreateThreadController extends DzqController
{
    use ThreadTrait;

    private $isDraft = false;

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        $this->checkThreadPluginAuth($userRepo);
        $categoryId = $this->inPut('categoryId');
        $user = $this->user;

        if ($this->user->isGuest()) {
            $this->outPut(ResponseCode::JUMP_TO_LOGIN);
        }
        if ($this->user->status == User::STATUS_NEED_FIELDS) {
            $this->outPut(ResponseCode::JUMP_TO_SIGIN_FIELDS);
        }
        if ($this->user->status == User::STATUS_MOD) {
            $this->outPut(ResponseCode::JUMP_TO_AUDIT);
        }

        if (!$userRepo->canCreateThread($user, $categoryId)) {
            throw new PermissionDeniedException('没有发帖权限');
        }

        $price = floatval($this->inPut('price'));
        $attachmentPrice = floatval($this->inPut('attachmentPrice'));
        if (
            ($price > 0 || $attachmentPrice > 0)
            && !$userRepo->canInsertPayToThread($user)
        ) {
            throw new PermissionDeniedException('没有插入【付费】权限');
        }


        if (
            !empty($this->inPut('position'))
            && !$userRepo->canInsertPositionToThread($user)
        ) {
            throw new PermissionDeniedException('没有插入【位置信息】权限');
        }

        $captcha = [
            'captchaTicket' => $this->inPut('captchaTicket'),
            'captchaRandStr' => $this->inPut('captchaRandStr'),
            'ip' => ip($this->request->getServerParams())
        ];
        $userRepo->checkPublishPermission($user, $captcha);

        return true;
    }

    public function main()
    {
        $this->limitCreateThread();
        !empty($position) && $this->dzqValidate($position, [
            'longitude' => 'required',
            'latitude' => 'required',
            'address' => 'required',
            'location' => 'required'
        ]);
        $result = $this->createThread();
        $this->outPut(ResponseCode::SUCCESS, '', $result);
    }

    /**
     * @desc 发布一个新帖子
     */
    private function createThread()
    {
        $db = $this->getDB();
        $db->beginTransaction();
        try {
            $result = $this->executeEloquent();
            $db->commit();
            $this->increaseThreads();
            return $result;
        } catch (\Exception $e) {
            $db->rollBack();
            $this->info('createThread_error_' . $this->user->id, $e->getMessage());
            $this->outPut(ResponseCode::DB_ERROR, $e->getMessage());
        }
    }

    private function executeEloquent()
    {
        $content = $this->inPut('content');

        if (!empty($content['text'])) {
            //处理emoji表情
            $content['text'] = $this->optimizeEmoji($content['text']);
            //处理@
            $content['text'] = $this->renderCall($content['text']);
        }
        //插入thread数据
        $thread = $this->saveThread($content);
        //插入话题
        $content = $this->saveTopic($thread, $content);
        //插入post数据
        $post = $this->savePost($thread, $content);
        $parsedContent = $post->content;
        //发帖@用户
        $this->sendNews($thread, $post);
        //插入tom数据
        $tomJsons = $this->saveTom($thread, $content, $post);
        //更新帖子条数
        !$this->isDraft && Category::refreshThreadCountV3($thread['category_id']);
        $post->content = $parsedContent;
        return $this->getResult($thread, $post, $tomJsons);
    }

    private function saveThread(&$content)
    {
        $thread = new Thread();
        $userId = $this->user->id;
        $categoryId = $this->inPut('categoryId');
        $title = $this->inPut('title');//title没有则自动生成
        $price = $this->inPut('price');
        $attachmentPrice = $this->inPut('attachmentPrice');
        $freeWords = $this->inPut('freeWords');
        $position = $this->inPut('position');
        $isAnonymous = $this->inPut('anonymous');
        $isDraft = $this->inPut('draft');

        // 帖子是否需要支付，如果需要支付，则强制发布为草稿
        if ($this->needPay($content['indexes'] ?? []) && empty($isDraft)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '包含红包/悬赏的帖子必须存为草稿');
        }

        if (!empty($isDraft)) {
            $this->isDraft = $isDraft;
        }

        if (mb_strlen($title) > Thread::TITLE_LENGTH) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '标题不能超过' . Thread::TITLE_LENGTH . '字');
        }
        if (empty($content)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '请输入帖子内容');
        }
        if (empty($categoryId)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '请选择帖子分类');
        }
//        empty($title) && $title = Post::autoGenerateTitle($content['text']);//不自动生成title

        $dataThread = [
            'user_id' => $userId,
            'category_id' => $categoryId,
            'title' => $title,
            'post_count' => 1,
            'type' => Thread::TYPE_OF_ALL,
            'source' => Thread::DATA_PLATFORM_OF_SITE
        ];
        $price = floatval($price);
        $attachmentPrice = floatval($attachmentPrice);
        $freeWords = floatval($freeWords);
        if ($price > 0 || $attachmentPrice > 0) {
            $this->checkThreadPrice($price, $attachmentPrice);
            $price > 0 && $dataThread['price'] = $price;
            $attachmentPrice > 0 && $dataThread['attachment_price'] = $attachmentPrice;
            $freeWords > 0 && $dataThread['free_words'] = $freeWords;
        }
        !empty($freeWords) && $dataThread['free_words'] = $freeWords;
        if (!empty($position)) {
            $dataThread['longitude'] = $position['longitude'];
            $dataThread['latitude'] = $position['latitude'];
            $dataThread['address'] = $position['address'];
            $dataThread['location'] = $position['location'];
        } else {
            $dataThread['address'] = '';
            $dataThread['location'] = '';
        }
        //添加变更时间
        $dataThread['issue_at']=date('Y-m-d H:i:s');
        [$newTitle, $newContent] = $this->boolApproved($title, $content['text'], $isApproved);
        $content['text'] = $newContent;
        $dataThread['title'] = $newTitle;
        if ($isApproved) {
            $dataThread['is_approved'] = Thread::BOOL_NO;
        } else {
            $dataThread['is_approved'] = Thread::BOOL_YES;
        }
        $isDraft && $dataThread['is_draft'] = Thread::BOOL_YES;
        !empty($isAnonymous) && $dataThread['is_anonymous'] = Thread::BOOL_YES;
        if (!(bool)app(SettingsRepository::class)->get('thread_optimize') && Utils::requestFrom() != Platform::MinProgram) {
            $dataThread['is_display'] = Thread::BOOL_NO;
        }
        $thread->setRawAttributes($dataThread);
        $thread->save();
        if (!$isApproved && !$isDraft) {
            $this->user->refreshThreadCount();
            $this->user->save();
            Category::refreshThreadCountV3($categoryId);
        }
        $thread = Thread::find($thread->id);
        return $thread;
    }

    private function savePost($thread, $content)
    {
        $text = $content['text'];
        $post = new Post();
        [$ip, $port] = $this->getIpPort();
        $dataPost = [
            'user_id' => $this->user->id,
            'thread_id' => $thread['id'],
            'content' => $text,
            'ip' => $ip,
            'port' => $port,
            'is_first' => Post::FIRST_YES,
            'is_approved' => Post::APPROVED
        ];
        $post->setRawAttributes($dataPost);
        $post->save();

        $settings=app(SettingsRepository::class);;
        $aicid=$settings->get('aicid', 'chatgpt');
        $aiusername=$settings->get('aiusername', 'chatgpt');
        $callai=(bool)$settings->get('callai', 'chatgpt');
        $aiuid=$settings->get('aiuid', 'chatgpt');

        if (strpos($aicid, ',') === false){
            $aicid = $aicid.',';
        }
        $arrcid = explode(',', $aicid);

        if (in_array($thread->category_id,$arrcid) || ($callai && strpos($content, '@'.$aiusername) !== false)){
            $query = new ChatGptKernel();
            $query->toid = $thread->id;
            $query->type = 'TEXT';
            $query->msg_type = 0;
            $query->dataline = time();
            $query->save();
        }
        return $post;
    }

    private function saveTom($thread, $content, $post)
    {
        $indexes = $content['indexes'] ?? [];

        $attrs = [];
        $tomJsons = $this->tomDispatcher($indexes, $this->CREATE_FUNC, $thread['id'], $post['id']);
        $tags = [];
        if (!empty($content['text'])) {
            $tags[] = [
                'thread_id' => $thread['id'],
                'tag' => TomConfig::TOM_TEXT,
            ];
        }
        foreach ($tomJsons as $key => $value) {
            //针对全贴付费，部分付费处理
            $price_type = 0;
            $price_ids = '{}';
            if(($thread->attachment_price || $thread->price) && in_array($value['tomId'], TomConfig::$sub_pay_list)){
                $price_type = 1;
                if($thread->price > 0){
                    $body_value = array_values($value['body']);
                    if(!is_array($body_value[0])){
                        $body_value[0] = [$body_value[0]];
                    }
                    $price_ids = json_encode($body_value[0]);
                }else{
                    $price_ids = !empty($indexes[$key]['body']['priceList']) && is_array($indexes[$key]['body']['priceList']) ? json_encode($indexes[$key]['body']['priceList']) : '{}';
                }
            }
            $attrs[] = [
                'thread_id' => $thread['id'],
                'tom_type' => $value['tomId'],
                'key' => $key,
                'value' => json_encode($value['body'], 256),
                'price_type' => $price_type,
                'price_ids' =>  $price_ids
            ];
            $tags[] = [
                'thread_id' => $thread['id'],
                'tag' => $value['tomId']
            ];
        }
        ThreadTom::query()->insert($attrs);
        //添加tag类型
        ThreadTag::query()->insert($tags);
        return $tomJsons;
    }

    private function getResult($thread, $post, $tomJsons)
    {
        $user = $this->user;
        $group = Group::getGroup($user->id);
        $tags = [];
        if (!empty($tomJsons)) {
            foreach ($tomJsons as $val) {
                $tags[]['tag'] = $val['tomId'];
            }
        }
        return $this->packThreadDetail($user, $group, $thread, $post, $tomJsons, true, $tags);
    }

    private function limitCreateThread()
    {
        $threadFirst = Thread::query()
            ->select(['id', 'user_id', 'category_id', 'created_at'])
            ->where('user_id', $this->user->id)
            ->orderByDesc('created_at')->first();
        //发帖间隔时间30s
        if (!empty($threadFirst) && (time() - strtotime($threadFirst['created_at'])) < 30) {
            $this->outPut(ResponseCode::RESOURCE_EXIST, '发帖太快，请稍后重试');
        }
    }

    public function prefixClearCache($user)
    {
        CacheKey::delListCache();
    }

    public function increaseThreads()
    {
        $today = date('Y-m-d', time());
        $site_info_daily = SiteInfoDaily::query()->where('date', $today)->first();
        if (empty($site_info_daily)) {
            $site_info_daily = new SiteInfoDaily();
            $site_info_daily->date = $today;
            $site_info_daily->mini_threads = 0;
            $site_info_daily->pc_threads = 0;
            $site_info_daily->h5_threads = 0;
            $site_info_daily->threads_sum = Thread::query()->where('is_approved', Thread::APPROVED)->whereNotNull('deleted_at')->count();
        }
        switch ($this->inPut('dzqPf')) {
            case Platform::FROM_WEAPP:
                $site_info_daily->mini_threads += 1;
                break;
            case Platform::FROM_H5:
                $site_info_daily->h5_threads += 1;
                break;
            case Platform::FROM_PC:
                $site_info_daily->pc_threads += 1;
                break;
            default:
                break;
        }
        $site_info_daily->threads_sum += 1;
        $site_info_daily->save();
    }
}
