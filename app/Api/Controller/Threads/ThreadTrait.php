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

use App\Api\Serializer\AttachmentSerializer;
use App\Censor\Censor;
use App\Common\CacheKey;
use App\Common\DzqRegex;
use App\Common\ResponseCode;
use App\Models\Attachment;
use App\Models\PluginGroupPermission;
use App\Models\ThreadUserStickRecord;
use App\Models\ThreadVideo;
use App\Traits\PostNoticesTrait;
use Discuz\Base\DzqCache;
use App\Models\Category;
use App\Models\Order;
use App\Models\Post;
use App\Models\PostUser;
use App\Models\Thread;
use App\Models\ThreadTopic;
use App\Models\ThreadUser;
use App\Models\Topic;
use App\Models\User;
use App\Modules\ThreadTom\TomConfig;
use App\Modules\ThreadTom\TomTrait;
use App\Repositories\UserRepository;
use Discuz\Qcloud\QcloudTrait;
use App\Common\Utils;

trait ThreadTrait
{
    use TomTrait;

    use QcloudTrait;

    use PostNoticesTrait;

    private $loginUserData = [];

    public function packThreadDetail($user, $group, $thread, $post, $tomInputIndexes, $analysis = false, $tags = [], $loginUserData = [], $userStickIds = [])
    {
        $loginUser = $this->user;
        $this->loginUserData = $loginUserData;
        $userField = $this->getUserInfoField($loginUser, $user, $thread);
        $groupField = $this->getGroupInfoField($loginUser, $group, $thread);
        $likeRewardField = $this->getLikeRewardField($thread, $post);//列表页传参
        $payType = $this->threadPayStatus($loginUser, $thread, $paid);
        $canViewTom = $this->canViewTom($loginUser, $thread, $payType, $paid);
        $canFreeViewTom = $this->canFreeViewTom($loginUser, $thread);
        $contentField = $this->getContentField($loginUser, $thread, $post, $tomInputIndexes, $payType, $paid, $canViewTom, $canFreeViewTom);
        $canViewThreadVideo = $this->canViewThreadVideo($loginUser, $thread);
        if (!$canViewThreadVideo) {
            $contentField = $this->filterThreadVideo($contentField);
        }
        $result = [
            'threadId' => $thread['id'],
            'postId' => $post['id'],
            'userId' => $thread['user_id'],
            'categoryId' => $thread['category_id'],
            'parentCategoryId' => $this->getParentCategory($thread['category_id'])['parentCategoryId'],
            'topicId' => $thread['topic_id'] ?? 0,
            'categoryName' => $this->getCategoryNameField($thread['category_id']),
            'parentCategoryName' => $this->getParentCategory($thread['category_id'])['parentCategoryName'],
            'title' => $thread['title'],
            'viewCount' => empty($thread['view_count']) ? 0 : $thread['view_count'],
            'isApproved' => $thread['is_approved'],
            'isStick' => $thread['is_sticky'],
            'isDraft' => boolval($thread['is_draft']),
            'isSite' => boolval($thread['is_site']),
            'isAnonymous' => $thread['is_anonymous'],
            'isFavorite' => $this->getFavoriteField($thread['id'], $loginUser),
            'price' => floatval($thread['price']),
            'attachmentPrice' => floatval($thread['attachment_price']),
            'payType' => $payType,
            'paid' => $paid,
            'isLike' => $this->isLike($loginUser, $post),
            'isReward' => $this->isReward($loginUser, $thread),
            'createdAt' => date('Y-m-d H:i:s', strtotime($thread['created_at'])),
            //修改创建时间为变更时间
            'issueAt' => date('Y-m-d H:i:s', strtotime($thread['issue_at'])),
            'updatedAt' => date('Y-m-d H:i:s', strtotime($thread['updated_at'])),
            'diffTime' => Utils::diffTime($thread['created_at']),
            'user' => $userField,
            'group' => $groupField,
            'likeReward' => $likeRewardField,
            'displayTag' => $this->getDisplayTagField($thread, $tags),
            'position' => [
                'longitude' => $thread['longitude'],
                'latitude' => $thread['latitude'],
                'address' => $thread['address'],
                'location' => $thread['location']
            ],
            'ability' => $this->getAbilityField($loginUser, $thread),
            'content' => $contentField,
            'freewords' => $thread['free_words'],
            'userStickStatus' => in_array($thread['id'], $userStickIds) ? true : false
        ];
        if ($analysis) {
            $concatString = $thread['title'] . $post['content'];
            list($searches, $replaces) = ThreadHelper::getThreadSearchReplace($concatString);
            $result['title'] = str_replace($searches, $replaces, $result['title']);
//            $result['content']['text'] = str_replace($searches, $replaces, $result['content']['text']);
        }
        return $result;
    }

    private function canViewTom($user, $thread, $payType, $paid)
    {
        $userRepo = app(UserRepository::class);
        $canEditThread = $userRepo->canEditThread($user, $thread);
        if ($payType != Thread::PAY_FREE) {//付费贴
            $canFreeViewThreadDetail = $this->canFreeViewTom($user, $thread);
            if ($canFreeViewThreadDetail || $paid || $canEditThread) {
                return true;
            } else {
                return false;
            }
        } else {
            $repo = new UserRepository();
            $canViewThreadDetail = $repo->canViewThreadDetail($user, $thread);
            if ($canViewThreadDetail || $canEditThread) {
                return true;
            } else {
                return false;
            }
        }
    }

    private function canViewThreadVideo($user, $thread): bool
    {
        $repo = new UserRepository();
        return $repo->canViewThreadVideo($user, $thread);
    }

    private function canFreeViewTom($user, $thread)
    {
        $repo = new UserRepository();
        return $repo->canFreeViewPosts($user, $thread);
    }

    private function getFavoriteField($threadId, $loginUser)
    {
        $userId = $loginUser->id;
        return $this->loginDataExists($this->loginUserData, ThreadHelper::EXIST_THREAD_USERS, $threadId, function () use ($userId, $threadId) {
            return ThreadUser::query()->where(['user_id' => $userId, 'thread_id' => $threadId])->exists();
        });
//        return DzqCache::exists(CacheKey::LIST_THREADS_V3_THREAD_USERS . $userId, $threadId, function () use ($userId, $threadId) {
//            return ThreadUser::query()->where(['thread_id' => $threadId, 'user_id' => $userId])->exists();
//        });
    }

    private function getCategoryNameField($categoryId)
    {
        $categories = Category::getCategories();
        $categories = array_column($categories, null, 'id');
        return $categories[$categoryId]['name'] ?? null;
    }

    private function getParentCategory($categoryId)
    {
        $categories = Category::getCategories();
        $categories = array_column($categories, null, 'id');
        $parentCategoryId = !empty($categories[$categoryId]['parentid']) ? $categories[$categoryId]['parentid'] : 0;
        $parentCategoryName = !empty($parentCategoryId) ? $categories[$parentCategoryId]['name'] : '';
        return [
            'parentCategoryId' => $parentCategoryId,
            'parentCategoryName' => $parentCategoryName
        ];
    }

    /**
     * @desc 获取操作权限
     * @param User $loginUser
     * @param $thread
     * @return bool[]
     */
    private function getAbilityField(User $loginUser, $thread)
    {
        /** @var UserRepository $userRepo */
        $userRepo = app(UserRepository::class);

        return [
            'canEdit' => $userRepo->canEditThread($loginUser, $thread),
            'canDelete' => $userRepo->canHideThread($loginUser, $thread),
            'canEssence' => $userRepo->canEssenceThread($loginUser, $thread),
            'canPoster' => $userRepo->canPosterThread($loginUser, $thread),
            'canStick' => $userRepo->canStickThread($loginUser),
            'canReply' => $userRepo->canReplyThread($loginUser, $thread['category_id']),
            'canViewPost' => $userRepo->canViewThreadDetail($loginUser, $thread),
            'canFreeViewPost' => $userRepo->canFreeViewPosts($loginUser, $thread),
            'canViewVideo' => $userRepo->canViewThreadVideo($loginUser, $thread),
            'canViewAttachment' => $userRepo->canViewThreadAttachment($loginUser, $thread),
            'canDownloadAttachment' => $userRepo->canDownloadThreadAttachment($loginUser, $thread['user_id'])
        ];
    }

    private function threadPayStatus($loginUser, $thread, &$paid)
    {
        $payType = Thread::PAY_FREE;
        $userId = $loginUser->id;
        $threadId = $thread['id'];
        $thread['price'] > 0 && $payType = Thread::PAY_THREAD;
        $thread['attachment_price'] > 0 && $payType = Thread::PAY_ATTACH;
        $canFreeViewTom = $this->canFreeViewTom($loginUser, $thread);
        //检查是否有编辑权限
        /** @var UserRepository $userRepo */
        $userRepo = app(UserRepository::class);
        $canEdit =  $userRepo->canEditThread($loginUser, $thread);

        if ($payType == Thread::PAY_FREE) {
            $paid = null;
        } elseif ($payType != Thread::PAY_FREE && ($canFreeViewTom||$canEdit)) {
            $paid = true;
        } else {
            $paid = $this->loginDataExists($this->loginUserData, ThreadHelper::EXIST_PAY_ORDERS, $threadId, function () use ($userId, $threadId) {
                return Order::query()
                    ->where([
                        'thread_id' => $threadId,
                        'user_id' => $userId,
                        'status' => Order::ORDER_STATUS_PAID
                    ])->whereIn('type', [Order::ORDER_TYPE_THREAD, Order::ORDER_TYPE_ATTACHMENT])->exists();
            });
//            $paid = DzqCache::exists(CacheKey::LIST_THREADS_V3_USER_PAY_ORDERS . $userId, $threadId, function () use ($userId, $threadId) {
//                return Order::query()
//                    ->where([
//                        'thread_id' => $threadId,
//                        'user_id' => $userId,
//                        'status' => Order::ORDER_STATUS_PAID
//                    ])->whereIn('type', [Order::ORDER_TYPE_THREAD, Order::ORDER_TYPE_ATTACHMENT])->exists();
//            });
        }
        return $payType;
    }

    /**
     * @desc 显示在帖子上的标签，目前支持 付费/精华/红包/悬赏 四种
     * @param $thread
     * @param $tags
     * @return bool[]
     */
    private function getDisplayTagField($thread, $tags)
    {
        $obj = [
            
            'isPoster' => false,
            'isEssence' => false,
            'isRedPack' => null,
            'isReward' => null,
            'isVote' => false
        ];
        if ($thread['price'] > 0 || $thread['attachment_price'] > 0) {
            $obj['isPrice'] = true;
        }
        if ($thread['is_essence']) {
            $obj['isEssence'] = true;
        }
        if ($thread['is_poster']) {
            $obj['isPoster'] = true;
        }
        $tags = array_column($tags, 'tag');
        if (!empty($tags)) {
            if (in_array(TomConfig::TOM_REDPACK, $tags)) {
                $obj['isRedPack'] = true;
            }
            if (in_array(TomConfig::TOM_REWARD, $tags)) {
                $obj['isReward'] = true;
            }
            if (in_array(TomConfig::TOM_VOTE, $tags)) {
                $obj['isVote'] = true;
            }
        }
        return $obj;
    }

    private function getContentField($loginUser, $thread, $post, $tomInput, $payType, $paid, $canViewTom, $canFreeViewTom)
    {
        $content = [
            'text' => null,
            'indexes' => null,
            'poster' => $this->changeContentImgSrc($content, $body, $canViewTom),
        ];
        if ($payType == Thread::PAY_FREE || $loginUser->id == $thread['user_id']) {
            $content['text'] = $post['content'];
            $content['indexes'] = $this->tomDispatcher($tomInput, $this->SELECT_FUNC, $thread['id'], null, $canViewTom);
        } else {
            if ($paid || $canFreeViewTom) {
                $content['text'] = $post['content'];
                $content['indexes'] = $this->tomDispatcher($tomInput, $this->SELECT_FUNC, $thread['id'], null, true);
            } else {
                $text = $post['content'];
                if (in_array($payType, [Thread::PAY_ATTACH, Thread::PAY_THREAD])) {
                    $freeWords = floatval($thread['free_words']);
                    if ($freeWords >= 0 && $freeWords < 1) {
                        $text = strip_tags($post['content']);
                        $freeLength = mb_strlen($text) * $freeWords;
//                        $text = mb_substr($text, 0, $freeLength) . Post::SUMMARY_END_WITH;
                        $text = self::truncateHTML($post['content'], $freeLength, Post::SUMMARY_END_WITH);
                        //针对最后的表情被截断的情况做截断处理
                        $text = preg_replace('/([^\w])\:\w*\.\.\./s', '$1...', $text);
                        //处理内容开头是表情，表情被截断的情况
                        $text = preg_replace('/^\:\w*\.\.\./s', '...', $text);
                    }
                }
                $content['text'] = $text;
                //如果有红包和图片，则只显示红包和图片
                /*
                $tomConfig = [];
                isset($tomInput[TomConfig::TOM_REDPACK]) && $tomConfig += [TomConfig::TOM_REDPACK => $tomInput[TomConfig::TOM_REDPACK]];
                isset($tomInput[TomConfig::TOM_IMAGE]) && $tomConfig += [TomConfig::TOM_IMAGE => $tomInput[TomConfig::TOM_IMAGE]];
                isset($tomInput[TomConfig::TOM_VOTE]) && $tomConfig += [TomConfig::TOM_VOTE => $tomInput[TomConfig::TOM_VOTE]];
                isset($tomInput[TomConfig::TOM_REWARD]) && $tomConfig += [TomConfig::TOM_REWARD => $tomInput[TomConfig::TOM_REWARD]];
                isset($tomInput[TomConfig::TOM_DOC]) && $tomConfig += [TomConfig::TOM_DOC => $tomInput[TomConfig::TOM_DOC]];
                isset($tomInput[TomConfig::TOM_VIDEO]) && $tomConfig += [TomConfig::TOM_VIDEO => $tomInput[TomConfig::TOM_VIDEO]];
                */
                $content['indexes'] = $this->tomDispatcher(
                    $tomInput,
                    $this->SELECT_FUNC,
                    $thread['id'],
                    null,
                    $canViewTom
                );
            }
        }
        $content['text'] = str_replace(['<r>', '</r>', '<t>', '</t>'], ['', '', '', ''], $content['text']);
        //考虑到升级V3，帖子的type 都要转为 99，所以针对 type 为 99 的也需要处理图文混排
        if (!empty($content['text'])) {
            $xml = $content['text'];
            $body = '';
            if (!empty($content['indexes'])) {
                foreach ($content['indexes'] as $key => $val) {
                    if ($val && $val['tomId'] == TomConfig::TOM_IMAGE) {
                        $body = $val['body'];
                    }
                }
            }

            if (!empty($body) || strpos($content['text'], '<iframe') !== false && $this->canViewThreadVideo($loginUser, $thread)) {
                $content['text'] = $this->changeContentIframeSrc($xml);
            }

            if (!empty($body) || strpos($content['text'], '<img') !== false) {
                $content = $this->changeContentImgSrc($content, $body, $canViewTom);
            }
        }

        return $content;
    }

    private function getGroupInfoField($loginUser, $group, $thread)
    {
        $groupResult = null;
        if (!empty($group) && $group['groups']['is_display']) {
            if ($thread['is_anonymous'] == Thread::IS_ANONYMOUS && $loginUser['id'] != $thread['user_id']) {
                return $groupResult;
            }
            $groupResult = [
                'groupId' => $group['group_id'],
                'groupName' => $group['groups']['name'],
                'groupIcon' => $group['groups']['icon'],
                'isDisplay' => $group['groups']['is_display'],
                'level' =>  $group['groups']['level']
            ];
        }
        return $groupResult;
    }

    private function getUserInfoField($loginUser, $user, $thread)
    {
        $userResult = [
            'nickname' => '匿名用户'
        ];
        //非匿名用户
        if ((!$thread['is_anonymous'] && !empty($user)) || $loginUser->id == $thread['user_id']) {
            $userResult = [
                'userId' => $user['id'],
                'nickname' => !empty($user['nickname']) ? $user['nickname'] : $user['username'],
                'avatar' => $user['avatar'],
                'threadCount' => $user['thread_count'],
                'followCount' => $user['follow_count'],
                'fansCount' => $user['fans_count'],
                'likedCount' => $user['liked_count'],
                'questionCount' => $user['question_count'],
                'isRealName' => !empty($user['realname']),
                'joinedAt' => date('Y-m-d H:i:s', strtotime($user['joined_at']))
            ];
        }
        return $userResult;
    }

    private function getLikeRewardField($thread, $post)
    {
        $ret = [
            'users' => [],
            'likePayCount' => $post['like_count'] + $thread['rewarded_count'] + $thread['paid_count'],
            'shareCount' => $thread['share_count'],
            'postCount' => $thread['post_count'] - 1
        ];
        $threadId = $thread['id'];
        $postId = $post['id'];
        $postUsers = DzqCache::hGet(CacheKey::LIST_THREADS_V3_POST_USERS, $threadId, function ($threadId) use ($postId, $post) {
            $ret = ThreadHelper::getThreadLikedDetail($threadId, $postId, $post, false);
            return $ret[$threadId] ?? [];
        });
        $ret['users'] = $postUsers;
        return $ret;
    }

    /**
     * @desc 查询是否需要审核
     * @param $title
     * @param $text
     * @param null $isApproved 是否进审核
     * @return array
     */
    private function boolApproved($title, $text, &$isApproved = null)
    {
        /** @var Censor $censor */
        $censor = app(Censor::class);
        $sep = '__' . mt_rand(111111, 999999) . '__';
        $contentForCheck = $title . $sep . $text;
        $split = explode($sep, $censor->checkText($contentForCheck));
        if (count($split) >= 2) {
            $newTitle = $split[0];
            $newContent = $split[1];
        } else {
            $newTitle = '';
            $newContent = $split[0];
        }
        $isApproved = $censor->isMod;
        return [$newTitle, $newContent];
    }

    private function isReward($loginUser, $thread)
    {
        if (empty($loginUser) || empty($thread)) {
            return false;
        }
        $userId = $loginUser->id;
        $threadId = $thread['id'];
        return $this->loginDataExists($this->loginUserData, ThreadHelper::EXIST_REWARD_ORDERS, $threadId, function () use ($userId, $threadId) {
            return Order::query()->where(['user_id' => $userId, 'type' => Order::ORDER_TYPE_REWARD, 'thread_id' => $threadId, 'status' => Order::ORDER_STATUS_PAID])->exists();
        });
        /*    return DzqCache::exists(CacheKey::LIST_THREADS_V3_USER_REWARD_ORDERS . $userId, $threadId, function () use ($userId, $threadId) {
                return Order::query()->where(['user_id' => $userId, 'type' => Order::ORDER_TYPE_REWARD, 'thread_id' => $threadId, 'status' => Order::ORDER_STATUS_PAID])->exists();
            });*/
    }

    private function loginDataExists($loginUserData, $type, $key, callable $callBack)
    {
        if (isset($loginUserData[$type])) {
            return isset($loginUserData[$type][$key]);
        } else {
            return $callBack();
        }
    }

    private function isLike($loginUser, $post)
    {
        if (empty($loginUser) || empty($post)) {
            return false;
        }
        $userId = $loginUser->id;
        $postId = $post['id'];
        return $this->loginDataExists($this->loginUserData, ThreadHelper::EXIST_POST_USERS, $postId, function () use ($userId, $postId) {
            return PostUser::query()->where('post_id', $postId)->where('user_id', $userId)->exists();
        });
        /*        return DzqCache::exists(CacheKey::LIST_THREADS_V3_POST_LIKED . $userId, $postId, function () use ($userId, $postId) {
                    return PostUser::query()->where('post_id', $postId)->where('user_id', $userId)->exists();
                });*/
    }

    private function saveTopic($thread, $content)
    {
        $threadId = $thread['id'];
        $topics = $this->optimizeTopics($content['text']);
        $userId = $this->user->id;
        $topicIds = [];
        foreach ($topics as $topicItem) {
            $topicName = str_replace('#', '', $topicItem);

            $topic = Topic::query()->where('content', $topicName)->first();
            if (empty($topic)) {
                //话题名称长度超过20就不创建了
                if (mb_strlen($topicName) > 18) {
                    \Discuz\Common\Utils::outPut(ResponseCode::INVALID_PARAMETER, '创建话题长度不能超过18个字符');
                }
                $topic = new Topic();
                $topic->user_id = $userId;
                $topic->content = $topicName;
                $topic->thread_count = 1;
                $topic->save();
            } else {
                $topic->increment('thread_count');
            }
            $topicId = $topic->id;
            $topicIds[] = $topicId;
            $attr = ['thread_id' => $threadId, 'topic_id' => $topicId];
            ThreadTopic::query()->where($attr)->firstOrCreate($attr);

            $html = sprintf('<span id="topic" value="%s">#%s#</span>', $topic->id, $topic->content);
            if (!strpos($content['text'], $html)) {
                $content['text'] = str_replace($topicItem, $html, $content['text']);
            }
        }

        if (empty($topicIds)) {
            ThreadTopic::query()->where('thread_id', $threadId)->delete();
        } else {
            ThreadTopic::query()->where('thread_id', $threadId)->whereNotIn('topic_id', $topicIds)->delete();
        }

        return $content;
    }

    //发帖@用户发送通知消息
    private function sendNews($thread, $post)
    {
        //如果是草稿或需要审核 不发送消息
        if ($thread->is_draft == Thread::IS_DRAFT || $thread->is_approved == Thread::UNAPPROVED || empty($post->parsedContent)) {
            return;
        }
        $this->sendRelated($post, $this->user);
    }

    /*
     * @desc 前端新编辑器只能上传完整url的emoji
     * 后端需要将其解析出代号进行存储
     * @param $text
     */
    private function optimizeEmoji($text)
    {
//        $text = '<r>' . $text . '</r>';
        preg_match_all('/<img((?![<|>]).)*?emoji\/qq((?![<|>]).)*?>/i', $text, $m1);
        $searches = $m1[0];
        $replaces = [];
        foreach ($searches as $search) {
            preg_match('/:[a-z]+?:/i', $search, $m2);
            $emoji = $m2[0];
            $replaces[] = $emoji;
        }
        $text = str_replace($searches, $replaces, $text);
        return $text;
    }

    private function optimizeTopics($text)
    {
        preg_match_all(DzqRegex::$topic, $text, $m1);
        $topics = $m1[0];
        $topics = array_values($topics);
        return $topics;
    }

    private function getPendingOrderInfo($thread)
    {
        return Order::query()
            ->where('thread_id', $thread['id'])
            ->where('status', Order::ORDER_STATUS_PENDING)
            ->whereIn('type', [Order::ORDER_TYPE_REDPACKET, Order::ORDER_TYPE_QUESTION_REWARD, Order::ORDER_TYPE_MERGE])
            ->select(['payment_sn', 'order_sn', 'amount', 'type', 'id', 'status'])
            ->first();
    }

    /**
     * 获取红包/悬赏/混合支付对应的订单，一对一
     * @param $thread
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    private function getOrderInfo($thread)
    {
        return Order::query()
            ->where('thread_id', $thread['id'])
            ->whereIn('type', [Order::ORDER_TYPE_REDPACKET, Order::ORDER_TYPE_TEXT, Order::ORDER_TYPE_LONG, Order::ORDER_TYPE_QUESTION_REWARD, Order::ORDER_TYPE_MERGE])
            ->select(['payment_sn', 'order_sn', 'amount', 'type', 'id', 'status'])
            ->first();
    }

    private function renderTopic($text)
    {
        preg_match_all(DzqRegex::$topic, $text, $topic);
        if (empty($topic)) {
            return $text;
        }
        $topic = $topic[0];
        $topic = str_replace('#', '', $topic);
        $topics = Topic::query()->select('id', 'content')->whereIn('content', $topic)->get()->map(function ($item) {
            $item['content'] = '#' . $item['content'] . '#';
            $item['html'] = sprintf('<span id="topic" value="%s">%s</span>', $item['id'], $item['content']);
            return $item;
        })->toArray();
        foreach ($topics as $val) {
            $text = preg_replace("/{$val['content']}/", $val['html'], $text, 1);
        }
        return $text;
    }

    private function renderCall($text)
    {
        preg_match_all('/@.+?(\s|\<)/', $text, $call);
        if (empty($call)) {
            return $text;
        }
        $call = $call[0];
        $call = str_replace(['@', ' ', '<'], '', $call);
        $ats = User::query()->select('id', 'nickname')->whereIn('nickname', $call)->get()->map(function ($item) {
            $item['nickname'] = '@' . $item['nickname'];
            $item['html'] = sprintf('<span id="member" value="%s">%s</span>', $item['id'], $item['nickname']);
            return $item;
        })->toArray();
        foreach ($ats as $val) {
            $val['nickname'] = preg_quote($val['nickname']);
            $text = preg_replace("/{$val['nickname']}/", "{$val['html']}", $text, 1);
        }
        return $text;
    }

    public function checkThreadPrice($price, $attachmentPrice)
    {
        $limitMoney = Thread::PRICE_LIMIT;
        if ($price > 0 && $attachmentPrice > 0) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '只可选择一种付费类型');
        }
        if ($price != round($price, 2) || $attachmentPrice != round($attachmentPrice, 2)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '价格设置小数点后不得超过2位');
        }
        if ($price > $limitMoney || $attachmentPrice > $limitMoney) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '价格设置不能超过' . $limitMoney . '元');
        }
    }

    private function filterThreadVideo($content)
    {
        if (!empty($content['indexes']) && is_array($content['indexes'])) {
            foreach ($content['indexes'] as $key => &$val) {
                $key == TomConfig::TOM_VIDEO && $val['body']['mediaUrl'] = '';
            }
        }
        return $content;
    }

    private function getThreadUserStick($thread, $user)
    {
        if (empty($thread) || empty($user)) {
            return 0;
        }

        $userThreadStick = ThreadUserStickRecord::query()->where('user_id', $user->id)->first();
        if (empty($userThreadStick)) {
            return 0;
        }

        if ($userThreadStick->thread_id == $thread->id) {
            return 1;
        }
        return 0;
    }

    private function changeContentIframeSrc($content)
    {
        if (strpos($content, 'videoId') !== false) {
            preg_match_all('/videoId-(\d+)/', $content, $videoIds_all);
        }
        $videoData = [];
        if (!empty($videoIds_all[1])) {
            $content_videos = ThreadVideo::query()->whereIn('id', $videoIds_all[1])->get();
            if (!empty($content_videos)) {
                $content_videos->map(function ($item) use (&$videoData) {
                    $mediaUrl = explode('?', $item['media_url']);
                    $item->media_url = $mediaUrl[0];
                    $videoData[$item->id] = $item->getMediaUrl($item);
                });
            }
        }

        if (!empty($videoData)) {
            foreach ($videoData as $key => $value) {
                $oldIframe = 'iframe src="" alt="videoId-' . $key . '"';
                $newIframe = 'iframe src="' . $value . '" alt="videoId-' . $key . '" scrolling="no" border="0" frameborder="no" framespacing="0" allowfullscreen="true"';
                $content = str_replace($oldIframe, $newIframe, $content);
            }
        }
        return $content;
    }

    private function changeContentImgSrc($content, $body, $canViewTom)
    {
        $xml = $content['text'];
        $attachments_body = $body;
        $attachments = [];
        if (!empty($body)) {
            $attachments = array_combine(array_column($attachments_body, 'id'), array_column($attachments_body, 'url'));
        }
        $isset_attachment_ids = [];
        //这里增加 前端拖拽图片的图文混排的形式
        if (strpos($xml, 'attachmentId') !== false) {
            preg_match_all('/attachmentId-(\d+)/', $xml, $attachmentIds_all);
        }
        if (!empty($attachmentIds_all[1])) {
            $content_attachments = Attachment::query()->whereIn('id', $attachmentIds_all[1])->get();
            if (!empty($content_attachments)) {
                $serializer = $this->app->make(AttachmentSerializer::class);
                foreach ($content_attachments as $val) {
                    if ($val->is_remote) {
                        $attachments[$val->id] = $serializer->getImgUrl($val);
                    }
                }
            }
        }

        if (!empty($attachments)) {
            preg_match_all('/<img.*?alt=[\"|\']?(.*?)[\"|\']?\s.*?>/i', $xml, $imagesSrc);
            if (!empty($imagesSrc[1])) {
                foreach ($imagesSrc[1] as $key => $value) {
                    $id = substr($value, strrpos($value, '-') + 1);
                    if (isset($attachments[$id])) {
                        $newImageSrc = '<img src="' . $attachments[$id] . '" alt="attachmentId-' . $id . '" title="' . $id . '" />';
                        $xml = str_replace($imagesSrc[0][$key], $newImageSrc, $xml);
                    }
                }
            }

            $xml_attachments = $xml_attachments_ids = [];
            $serializer = $this->app->make(AttachmentSerializer::class);
            if (!$canViewTom && !empty($attachments_body)) {       //如果没有权限查看的，则图文混排中的图片还是取清晰的
                $attachments_ids = array_column($attachments_body, 'id');
                $x_attachments = Attachment::query()->whereIn('id', $attachments_ids)->get();
                $xml_attachments = $x_attachments->keyBy('id');
                $xml_attachments_ids = $xml_attachments->pluck('id')->all();
            }
            $xml = preg_replace_callback(
                '<img src="(.*?)" alt="(.*?)" title="(\d+)">',
                function ($m) use ($attachments, &$isset_attachment_ids, $xml_attachments, $xml_attachments_ids, $canViewTom, $serializer) {
                    if (!empty($m)) {
                        $id = trim($m[3], '"');
                        $isset_attachment_ids[] = $id;
                        $replace_url = $attachments[$id];
                        if (!$canViewTom && in_array($id, $xml_attachments_ids)) {
                            $replace_url = $serializer->getImgUrl($xml_attachments[$id]);
                        }
                        return 'img src="' . $replace_url . '" alt="' . $m[2] . '" title="' . $id . '"';
                    }
                },
                $xml
            );
        }

        //针对图文混排的情况，这里要去掉外部图片展示
//                if (!empty($tom_image_key)) unset($content['indexes'][$tom_image_key]);
        $content['text'] = $xml;
        if (!empty($isset_attachment_ids) && isset($content['indexes'][TomConfig::TOM_IMAGE]['body'])) {
            foreach ($content['indexes'][TomConfig::TOM_IMAGE]['body'] as $k => $v) {
                if (in_array($v['id'], $isset_attachment_ids)) {
                    unset($content['indexes'][TomConfig::TOM_IMAGE]['body'][$k]);
                }
            }
        }
        if (!empty($content['indexes'][TomConfig::TOM_IMAGE]) && !empty($content['indexes'][TomConfig::TOM_IMAGE]['body'])) {
            $content['indexes'][TomConfig::TOM_IMAGE]['body'] = array_values($content['indexes'][TomConfig::TOM_IMAGE]['body']);
        }

        return $content;
    }

    //检查发帖和更新帖子的内容权限
    private function checkThreadPluginAuth(UserRepository $userRepo)
    {
        $price = floatval($this->inPut('price'));
        $attachmentPrice = floatval($this->inPut('attachmentPrice'));
        $position = $this->inPut('position');
        $isAnonymous = $this->inPut('anonymous');
        $content = $this->inPut('content');
        $user = $this->user;
        if (empty($user)) {
            $this->outPut(ResponseCode::USER_LOGIN_STATUS_NOT_NULL);
        }
        if (($price > 0 || $attachmentPrice > 0) && !$userRepo->canInsertPayToThread($user)) {
            $this->outPut(ResponseCode::UNAUTHORIZED, '没有添加付费项权限');
        }
        if (!empty($position) && !$userRepo->canInsertPositionToThread($user)) {
            $this->outPut(ResponseCode::UNAUTHORIZED, '没有插入位置信息权限');
        }
        if (!empty($isAnonymous) && !$userRepo->canCreateThreadAnonymous($user)) {
            $this->outPut(ResponseCode::UNAUTHORIZED, '没有匿名发帖权限');
        }
        if (!empty($content) && !empty($content['indexes'])) {
            $indexes = $content['indexes'];
            if (!is_array($indexes)) {
                return;
            }
            foreach ($indexes as $k => $v) {
                $appId = $v['tomId'];
                $config = TomConfig::$map[$appId] ?? null;
                $isAllowed = true;
                if (is_null($config)) {
                    $pluginList = \Discuz\Common\Utils::getPluginList();
                    $config = $pluginList[$appId] ?? null;
                    (!is_null($config) && !PluginGroupPermission::hasPluginPermission($appId, $this->user->groupId)) && $isAllowed = false;
                } else {
                    $func = 'canInsert' . $config['name_en'] . 'ToThread';
                    (method_exists($userRepo, $func) && !$userRepo->$func($user)) && $isAllowed = false;
                }
                !$isAllowed &&  $this->outPut(ResponseCode::UNAUTHORIZED, "没有插入'" . $config['name_cn'] . "'权限");
            }
        }
    }

    public function truncateHTML($html_string, $length, $append = '', $is_html = true)
    {
        $html_string = trim($html_string);
        $append = (mb_strlen(strip_tags($html_string)) > $length) ? $append : '';
        $i = 0;
        $tags = [];

        if ($is_html) {
            $this->preg_match_all_mb('/<[^>]+>([^<]*)/', $html_string, $tag_matches);

            foreach ($tag_matches as $tag_match) {
                if ($tag_match[0][1] - $i >= $length) {
                    break;
                }
                $tag = mb_substr(strtok($tag_match[0][0], " \\\t\\\0\\\x0B>"), 1);
                if ($tag[0] != '/') {
                    if (!in_array($tag, ['img'])) {       //针对有些标签是单标签的情况，不需要成对出现
                        $tags[] = $tag;
                    }
                } elseif (end($tags) == mb_substr($tag, 1)) {
                    array_pop($tags);
                } else {      // </*> 匹配对应的tag标签，然后去掉 tags 中对应的标签
                    while (end($tags) != substr($tag, 1)) {
                        array_pop($tags);
                    }
                    array_pop($tags);
                }
                $i += $tag_match[1][1] - $tag_match[0][1];
            }
        }

        return mb_substr($html_string, 0, $length = min(mb_strlen($html_string), $length + $i)) . $append . (count($tags = array_reverse($tags)) ? '</' . implode('></', $tags) . '>' : '');
    }

    public function preg_match_all_mb($pattern, $subject, array &$matches = null)
    {
        preg_match_all($pattern, $subject, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        $strlen0 = 0;
        foreach ($matches as &$match) {
            $matchDt = $match[1][1]-$match[0][1];
            $match[0][1] = $strlen0;
            $match[1][1] = $match[0][1]+$matchDt;

            $strlen0 += mb_strlen($match[0][0]);
        }
    }
}
