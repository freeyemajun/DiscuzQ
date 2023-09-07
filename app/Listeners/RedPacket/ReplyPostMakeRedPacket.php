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

namespace App\Listeners\RedPacket;

use App\Commands\RedPacket\ReceiveRedPacket;
use App\Events\Post\Saved;
use App\Models\Thread;
use App\Models\Post;
use App\Models\RedPacket;
use App\Models\ThreadRedPacket;
use App\Models\ThreadTom;
use App\Models\UserWalletLog;
use App\Modules\ThreadTom\TomConfig;
use Discuz\Foundation\EventsDispatchTrait;
use Exception;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\ConnectionInterface;

class ReplyPostMakeRedPacket
{
    use EventsDispatchTrait;

    /**
     * @var Dispatcher
     */
    protected $bus;

    /**
     * @var ConnectionInterface
     */
    protected $connection;

    public $baseInfo = '';

    public $debugInfo = false; // false:默认不输出调试信息到日志上

    public function __construct(Dispatcher $bus, ConnectionInterface $connection)
    {
        $this->bus = $bus;
        $this->connection = $connection;
    }

    /**
     * @param Saved $event
     * @throws Exception
     */
    public function handle(Saved $event)
    {
        $post = $event->post;
        $actor = $event->actor;
        $data = $event->data;
        $type = $event->post->getRelations()['thread']['type'];
        $this->baseInfo =   '访问用户id:'  . $actor->id . '(' . $actor->username . ')'.
                            ',访问帖子id:' . $post->thread->id.
                            ',post_id:'   . $post->id.
                            ',msg:';
        $thread = Thread::query()->where('id', $post->thread->id)->first();
        $compareTime = date('Y-m-d H:i:s', time() - RedPacket::EXPIRE_TIME);
        $threadRedPacket = ThreadRedPacket::query()
            ->where('thread_id', $post->thread->id)
            ->where('created_at', '>', $compareTime)
            ->first();
        if ($thread['is_red_packet'] == 0 && empty($threadRedPacket)) {
            $this->outDebugInfo('回复的帖子不是红包帖');
            return;
        }
        if ($post->user_id != $actor->id) {
            $this->outDebugInfo('回复领红包：不是回复的主人领取红包');
            return;
        }

        if ($post->is_approved == Post::UNAPPROVED) {
            $this->outDebugInfo('回复领红包：该帖未审核');
            return;
        }

        if ($post->is_first == 1 || $post->is_comment == 1) {
            $this->outDebugInfo('回复领红包：该帖不为首帖、第一条评论');
            return;
        }
        // Start Transaction
        $this->connection->beginTransaction();
        try {
            $redPacket = RedPacket::query() ->where([   'thread_id' => $post->thread_id,
                'status' => RedPacket::RED_PACKET_STATUS_VALID,
                'condition' => 0])
                ->lockForUpdate()
                ->first();
            if (empty($redPacket) || empty($redPacket['remain_money']) || empty($redPacket['remain_number'])) {
                $this->connection->rollback();
                $this->outDebugInfo('回复领红包：该红包帖无剩余金额和个数');
                return;
            }

            //领取过红包的用户不再领取
            $currentPostUser = Post::query()->where(['id' => $post->id])->first();
            if (empty($currentPostUser)) {
                $this->connection->rollback();
                $this->outDebugInfo('回复领红包：post_id为空');
                return;
            }
            $thread = Thread::query()->where(['id' => $currentPostUser['thread_id']])->first();
            if (empty($thread)) {
                $this->connection->rollback();
                $this->outDebugInfo('回复领红包：thread_id为空');
                return;
            }
//        if ($thread['type'] == Thread::TYPE_OF_TEXT) {
//            $change_type = UserWalletLog::TYPE_INCOME_TEXT;
//        } else {
//            $change_type = UserWalletLog::TYPE_INCOME_LONG;
//        }

            $redPacketTom = ThreadTom::query()->where('thread_id', $thread['id'])
                ->where('tom_type', TomConfig::TOM_REDPACK)
                ->first();
            if ($redPacketTom) {
                $change_type = UserWalletLog::TYPE_REDPACKET_INCOME;
            } else {
                $change_type = 0;
            }

            $isReceive = UserWalletLog::query()->where([
                'user_id' => $actor['id'],
                'change_type' => $change_type,
                'thread_id' => $thread['id']
            ])->lockForUpdate()->first();
            if (!empty($isReceive)) {
                $this->connection->rollback();
                $this->outDebugInfo('回复领红包：该用户已经领取过红包了');
                return;
            }

            $this->bus->dispatch(new ReceiveRedPacket($thread, $post, $redPacket, $event->post->thread->user, $actor));
            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollback();
            app('log')->info('红包ID:'.$threadRedPacket['id'].'领取异常:' . $e->getMessage());
            throw $e;
        }
    }

    public function outDebugInfo($info)
    {
        if ($this->debugInfo) {
            app('log')->info($this->baseInfo . $info);
        }
    }
}
