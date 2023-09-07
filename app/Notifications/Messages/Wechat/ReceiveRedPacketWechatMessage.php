<?php

namespace App\Notifications\Messages\Wechat;

use App\Models\NotificationTiming;
use App\Models\Order;
use Discuz\Notifications\Messages\SimpleMessage;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Arr;

/**
 * 得到红包通知 - 微信
 *
 * @package App\Notifications\Messages\Wechat
 */
class ReceiveRedPacketWechatMessage extends SimpleMessage
{
    public $tplId = 'wechat.red_packet.gotten';

    protected $model;

    protected $actor;

    protected $data;

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
        [$firstData, $actor, $model, $data] = $parameters;
        // set parent tpl data
        $this->firstData = $firstData;

        $this->actor = $actor;
        $this->model = $model;
        $this->data = $data;

        $this->template();
    }

    public function template()
    {

        /*$build =  [
            'title' => $this->getTitle(),
            'content' => $this->getContent($this->data),
            'raw' => Arr::get($this->data, 'raw'),
        ];
        Arr::set($build, 'raw.tpl_id', $this->firstData->id);

        return $build;*/

        return ['content' => $this->getWechatContent()];
    }

    protected function titleReplaceVars()
    {
        return [];
    }

    public function contentReplaceVars($data)
    {
        $noticeId = !empty($this->data['noticeId']) ? $this->data['noticeId'] : '';
        $receiveUserId = !empty($this->data['receiveUserId']) ? $this->data['receiveUserId'] : 0;

        $message = Arr::get($this->data, 'message', '');
        $threadId = Arr::get($this->data, 'raw.thread_id', 0);
        $actualAmount = Arr::get($this->data, 'raw.actual_amount', 0); // 实际金额

        // 获取支付类型
        $orderName = Order::enumType(Arr::get($this->data, 'raw.type', 0), function ($args) {
            return $args['value'];
        });

        $actorName = Arr::get($this->data, 'raw.actor_username', '');  // 发送人姓名
        $nickname = Arr::get($this->data, 'raw.actor_nickname', '');  // 发送人姓名

        // 主题ID为空时跳转到首页
        if (empty($threadId)) {
            $threadUrl = $this->url->to('');
        } else {
            $threadUrl = $this->url->to('/topic/index?id=' . $threadId);
        }

        /**
         * 设置父类 模板数据
         * @parem $user_name
         * @parem $nick_name
         * @parem $order_type_name
         * @parem $actual_amount
         * @parem $content
         * @parem $notification_num 通知条数
         */
        $this->setTemplateData([
            '{$username}'           => $actorName,
            '{$nickname}'           => $nickname,
            '{$order_type_name}'     => $orderName,
            '{$actual_amount}'       => $actualAmount,
            '{$content}'             => $this->strWords($message),
            '{$thread_id}'           => $threadId,
            '{$notification_num}'    => NotificationTiming::getLastNotificationNum($noticeId, $receiveUserId),
        ]);
        // build data
        $expand = [
            'redirect_url' => $threadUrl,
        ];

        return $this->compiledArray($expand);
    }

}
