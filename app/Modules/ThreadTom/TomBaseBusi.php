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

namespace App\Modules\ThreadTom;

use App\Common\ResponseCode;
use App\Models\Attachment;
use App\Models\Order;
use App\Models\Thread;
use App\Models\ThreadRedPacket;
use App\Models\ThreadReward;
use App\Models\ThreadTom;
use App\Models\ThreadVideo;
use App\Models\ThreadVote;
use App\Models\User;
use Discuz\Common\Utils;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @method  create()
 * @method  update()
 * @method  select()
 * @method  userfunc()
 */
abstract class TomBaseBusi
{
    const RED_LIMIT_TIME = 1;           //红包帖创建时间间隔

    const NEED_PAY = false;

    public $tomId = null;

    public $operation = null;

    public $body = [];

    public $permissions = [];

    public $threadId = null;

    public $postId = null;

    public $user = null;

    public $key = null;

    public $app = null;

    public $db = null;

    public $canViewTom = true;

    public $plugin = null;

    public $priceIds = [];

    //用户是否购买了部分付费
    public $isPaySub = false;

    public function __construct(User $user, $threadId, $postId, $tomId, $key, $operation, $body, $canViewTom)
    {
        $this->app = app();
        $this->operation = $operation;
        $this->body = $body;
        $this->tomId = $tomId;
        $this->threadId = $threadId;
        $this->postId = $postId;
        $this->user = $user;
        $this->key = $key;
        $this->canViewTom = $canViewTom;
        $this->db = app('db');
        $this->operationValid();
        $this->priceIds = !empty($this->getParams('priceIds')) ? $this->getParams('priceIds') : [];
        $thread = Thread::query()->find($threadId);
        //判断用户是否有权或者购买了部分付费
        //1、超管，自己可查看
        if ($user->isAdmin() || ($thread->user_id == $user->id)) {
            $this->isPaySub = true;
        }
        if (!$this->isPaySub) {
            //2、具有部分付费订单支付记录
            $order = Order::query()->where(['thread_id' => $threadId, 'status' => Order::ORDER_STATUS_PAID, 'user_id' => $user->id, 'type' => Order::ORDER_TYPE_ATTACHMENT])->first();
            if ($order) {
                $this->isPaySub = true;
            }
        }
        //针对 update、create 操作防越权
        if (in_array($this->operation, ['create', 'update'])) {
            $isset = true;
            switch ($this->tomId){
                // 图片帖
                case TomConfig::TOM_IMAGE:
                    $imageIds = $this->getParams('imageIds');
                    if(!empty($imageIds)){
                        $isset = Attachment::query()->whereIn('id', $imageIds)->where('user_id', $this->user->id)->exists();
                    }
                    break;
                // 音频帖
                case TomConfig::TOM_AUDIO:
                    $audioId = $this->getParams('audioId');
                    if (!empty($audioId)){
                        $isset = ThreadVideo::query()->where(['id' => $audioId, 'user_id' => $this->user->id])->exists();
                    }
                    break;
                // 视频
                case TomConfig::TOM_VIDEO:
                    $videoId = $this->getParams('videoId');
                    if(!empty($videoId)){
                        $isset = ThreadVideo::query()->where(['id' => $videoId, 'user_id' => $this->user->id])->exists();
                    }
                    break;
                // 红包帖
                case TomConfig::TOM_REDPACK:
                    $thread_red_packet_id = $this->getParams('id');
                    if(!empty($thread_red_packet_id)){
                        if(empty($this->threadId)){
                            $isset = false;
                            break;
                        }
                        $isset = ThreadRedPacket::query()->where(['id' => $thread_red_packet_id, 'thread_id' => $this->threadId])->exists();
                    }
                    break;
                // 悬赏
                case TomConfig::TOM_REWARD:
                    $thread_reward_id = $this->getParams('id');
                    if(!empty($thread_reward_id)){
                        $isset = ThreadReward::query()->where(['id' => $thread_reward_id, 'user_id' => $this->user->id])->exists();
                    }
                    break;
                // 附件
                case TomConfig::TOM_DOC:
                    $docIds = $this->getParams('docIds');
                    if(!empty($docIds)){
                        $isset = Attachment::query()->whereIn('id', $docIds)->where('user_id', $this->user->id)->exists();
                    }
                    break;
                // 投票
                case TomConfig::TOM_VOTE:
                    $voteId = $this->getParams('voteId');
                    if(!empty($voteId)){
                        if(empty($this->threadId)){
                            $isset = false;
                            break;
                        }
                        $isset = ThreadVote::query()->where(['id' => $voteId, 'thread_id' => $this->threadId])->exists();
                    }
                    break;
                default:
                    break;
            }
            if(!$isset){
                $this->outPut(ResponseCode::UNAUTHORIZED);
            }

        }

    }

    private function operationValid()
    {
        if (!method_exists($this, $this->operation)) {
            $this->outPut(ResponseCode::INTERNAL_ERROR, sprintf('operation [%s] not exist in [%s]', $this->operation, static::class));
        }
    }

    /**
     * @desc 帖子对象存储获取对象入参
     * @param $key
     * @return array|\ArrayAccess|mixed
     */
    public function getParams($key)
    {
        return Arr::get($this->body, $key);
    }

    /**
     * @desc 输出结果写入到thread_tom表的value值
     * @param $array
     * @return array
     */
    public function jsonReturn($array)
    {
        $pFunc = null;
        $lastStacks = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? null;
        if (!empty($lastStacks)) {
            $pFunc = $lastStacks['function'];
        }
        //增加 priceList 字段返回
        $thread_tom = ThreadTom::query()->where('thread_id', $this->threadId)->get()->toArray();
        $priceList = [];
        foreach ($thread_tom as $val){
            if($val['tom_type'] == $this->tomId){
                $priceList = json_decode($val['price_ids'], 1);
            }
        }
        $ret = [
            'tomId' => $this->tomId,
            'operation' => $this->operation,
            'body' => $array,
            'priceList' => $priceList
        ];
        $plugin = $this->body['_plugin'] ?? null;
        if ($pFunc == 'select') {
            $ret['_plugin'] = $plugin;
        } else {
            !empty($plugin) && $ret['body']['_plugin'] = $plugin;
        }
        if (!empty($this->threadId)) {
            $ret['threadId'] = $this->threadId;
        }
        return $ret;
    }

    /*
     * 接口出参
     */
    public function outPut($code, $msg = '', $data = [])
    {
        Utils::outPut($code, $msg, $data, Str::uuid(), date('Y-m-d H:i:s'));
    }

    /*
     * 入参判断
     */
    public function dzqValidate($inputArray, array $rules, array $messages = [], array $customAttributes = [])
    {
        try {
            $validate = app('validator');
            $validate->validate($inputArray, $rules, $messages, $customAttributes);
        } catch (\Exception $e) {
            $validate_error = $e->validator->errors()->first();
            $error_message = !empty($validate_error) ? $validate_error : $e->getMessage();
            $this->outPut(ResponseCode::INVALID_PARAMETER, $error_message);
        }
    }

    public function camelData($arr, $ucfirst = false)
    {
        if (is_object($arr) && is_callable([$arr, 'toArray'])) {
            $arr = $arr->toArray();
        }
        if (!is_array($arr)) {
            //如果非数组原样返回
            return $arr;
        }
        $temp = [];
        foreach ($arr as $key => $value) {
            $key1 = Str::camel($key);           // foo_bar  --->  fooBar
            if ($ucfirst) {
                $key1 = Str::ucfirst($key1);
            }
            $value1 = self::camelData($value);
            $temp[$key1] = $value1;
        }
        return $temp;
    }

    public function delete()
    {
        ThreadTom::deleteTom($this->threadId, $this->tomId, $this->key);
        return $this->jsonReturn(false);
    }

    public function getRedOrderInfo($threadId)
    {
        return Order::query()
            ->where('thread_id', $threadId)
            ->whereIn('type', [Order::ORDER_TYPE_REDPACKET, Order::ORDER_TYPE_QUESTION_REWARD, Order::ORDER_TYPE_MERGE])
            ->select(['payment_sn', 'order_sn', 'amount', 'type', 'id', 'status'])
            ->first();
    }
}
