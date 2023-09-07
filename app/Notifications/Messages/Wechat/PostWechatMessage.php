<?php

namespace App\Notifications\Messages\Wechat;

use App\Models\NotificationTiming;
use App\Models\Post;
use App\Models\Thread;
use Discuz\Notifications\Messages\SimpleMessage;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Arr;

/**
 * Post 通知 - 微信
 */
class PostWechatMessage extends SimpleMessage
{
    protected $actor;

    protected $data;

    /**
     * @var Post $post
     */
    protected $post;

    /**
     * @var UrlGenerator
     */
    protected $url;

    public function __construct(UrlGenerator $url)
    {
        $this->url = $url;
    }

    public function setData(...$parameters)
    {
        [$firstData, $actor, $data] = $parameters;
        // set parent tpl data
        $this->firstData = $firstData;
        $this->actor = $actor;
        $this->data = $data;

        // set post model
        if (isset($this->data['post'])) {
            $this->post = $data['post'];
            $this->template();
        }
    }

    public function template()
    {
        return ['content' => $this->getWechatContent($this->data)];
    }

    protected function titleReplaceVars()
    {
        return [];
    }

    public function contentReplaceVars($data)
    {
        $noticeId = !empty($this->data['noticeId']) ? $this->data['noticeId'] : '';
        $receiveUserId = !empty($this->data['receiveUserId']) ? $this->data['receiveUserId'] : 0;

        if (!($this->post instanceof Post) && isset($this->data['post'])) {
            $this->post = Post::query()->where('id', '=', $data['post']->id)->first();
        }

        $threadPostContent = $this->post->getSummaryContent(Post::NOTICE_LENGTH, true)['first_content'];
        $threadTitle = $this->post->thread->getContentByType(Thread::CONTENT_LENGTH, true);

        /**
         * 设置父类 模板数据
         * @parem $user_id 帖子创建人ID
         * @parem $user_name 帖子创建人
         * @parem $nick_name 帖子创建人
         * @parem $actor_name 当前操作人(一般为管理员)
         * @parem $actor_nickname 当前操作人昵称(一般为管理员)
         * @parem $message_change 修改帖子的内容
         * @parem $thread_id 主题ID （可用于跳转参数）
         * @parem $thread_title 主题标题/首帖内容 (如果有title是title，没有则是首帖内容)
         * @parem $notify_type 内容操作状态 (修改/不通过/通过/精华/置顶/删除)
         * @parem $reason 原因
         * @parem $notification_num 通知条数
         */
        $this->setTemplateData([
            '{$user_id}'        => $this->post->user->id,
            '{$user_name}'      => $this->post->user->username,
            '{$nick_name}'      => $this->post->user->nickname,
            '{$actor_name}'     => $this->actor->username,
            '{$actor_nickname}' => $this->actor->nickname,
            '{$message_change}' => $this->strWords(Arr::get($data, 'message', '')),
            '{$thread_id}'      => $this->post->thread->id,
            '{$thread_title}'   => $this->strWords($threadTitle),
            '{$notify_type}'    => Post::enumNotifyType($this->data['notify_type']),
            '{$reason}'         => Arr::get($data, 'refuse', '无'),
            '{$notification_num}'    => NotificationTiming::getLastNotificationNum($noticeId, $receiveUserId),
        ]);

        // redirect_url
        // 判断如果是删除通知，帖子被删除后无法跳转到详情页，threadId 清空跳主页
        if ($data['notify_type'] == Post::NOTIFY_DELETE_TYPE) {
            $redirectUrl = '';
        } else {
            $redirectUrl = '/topic/index?id=' . $this->post->thread_id;
        }
        $expand['redirect_url'] = $this->url->to($redirectUrl);

        // build data
        return $this->compiledArray($expand);
    }

}
