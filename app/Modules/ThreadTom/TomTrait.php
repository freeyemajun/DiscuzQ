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

use App\Common\CacheKey;
use App\Common\PluginEnum;
use App\Models\Order;
use App\Models\OrderChildren;
use App\Models\ThreadRedPacket;
use App\Models\ThreadReward;
use Discuz\Base\DzqCache;
use App\Models\ThreadTom;
use Discuz\Base\DzqLog;
use Illuminate\Support\Arr;

trait TomTrait
{
    private $CREATE_FUNC = 'create';

    private $DELETE_FUNC = 'delete';

    private $UPDATE_FUNC = 'update';

    private $SELECT_FUNC = 'select';

    /**
     * @desc 支持一次提交包含新建或者更新或者删除等各种类型混合
     * @param $tomContent
     * @param null $operation
     * @param null $threadId
     * @param null $postId
     * @param bool $canViewTom
     * @return array
     * @throws \ReflectionException
     */
    private function tomDispatcher($tomContent, $operation = null, $threadId = null, $postId = null, $canViewTom = true)
    {
        $config = $this->threadPluginList();
        $tomJsons = [];
        $indexes = $this->getContentIndexes($tomContent);
        if (empty($indexes)) {
            return $tomJsons;
        }
        $tomList = [];
        if (!empty($threadId) && empty($operation)) {
            $tomList = DzqCache::hGet(CacheKey::LIST_THREADS_V3_TOMS, $threadId, function ($threadId) {
                return ThreadTom::query()->where(['thread_id' => $threadId, 'status' => ThreadTom::STATUS_ACTIVE])->get()->toArray();
            });
        }
        foreach ($indexes as $key => $tomJson) {
            $this->setOperation($threadId, $operation, $key, $tomJson, $tomList);
            if (isset($tomJson['tomId']) && isset($tomJson['operation']) &&
                in_array($tomJson['operation'], [$this->CREATE_FUNC, $this->DELETE_FUNC, $this->UPDATE_FUNC, $this->SELECT_FUNC])) {
                $tomId = strval($tomJson['tomId']);
                $op = $tomJson['operation'];
                $body = $tomJson['body'] ?? false;
                if (isset($config[$tomId])) {
                    $busiClass = $config[$tomId]['service'];
                } else {
                    $busiClass = \App\Modules\ThreadTom\Busi\DefaultBusi::class;
                }
                $service = new \ReflectionClass($busiClass);
                if (empty($tomJson['threadId'])) {
                    $service = $service->newInstanceArgs([$this->user, $threadId, $postId, $tomId, $key, $op, $body, $canViewTom]);
                } else {
                    $service = $service->newInstanceArgs([$this->user, $tomJson['threadId'], $postId, $tomId, $key, $op, $body, $canViewTom]);
                }
                $opResult = $service->$op();
                if (method_exists($service, $op) && is_array($opResult)) {
                    $tomJsons[$key] = $opResult;
                }
            }
        }
        return $tomJsons;
    }

    /**
     * @desc 原生插件和外部插件混合
     */
    private function threadPluginList()
    {
        $config = TomConfig::$map;
        $pluginList = \Discuz\Common\Utils::getPluginList();
        foreach ($pluginList as $item) {
            if ($item['type'] == PluginEnum::PLUGIN_THREAD) {
                $config[$item['app_id']] = [
                    'name_en' => $item['name_en'],
                    'service' => $item['busi']
                ];
            }
        }
        return $config;
    }

    private function getContentIndexes($tomContent)
    {
        if (isset($tomContent['indexes'])) {
            $indexes = $tomContent['indexes'];
        } else {
            if (isset($tomContent['text'])) {
                $indexes = [];
            } else {
                $indexes = $tomContent;
            }
        }
        return $indexes;
    }

    /**
     * @desc 识别当前的操作类型
     * @param $threadId
     * @param $operation
     * @param $key
     * @param $tomJson
     * @param $tomList
     * @return mixed
     */
    private function setOperation($threadId, $operation, $key, &$tomJson, $tomList)
    {
        !empty($operation) && $tomJson['operation'] = $operation;
        if (!isset($tomJson['operation'])) {
            if (empty($tomJson['body'])) {
                $tomJson['operation'] = $this->DELETE_FUNC;
            } else {//create/update
                if (empty($threadId)) {
                    $tomJson['operation'] = $this->CREATE_FUNC;
                } else {
                    $isUpdate = false;
                    foreach ($tomList as $item) {
                        if ($item['tom_type'] == $tomJson['tomId'] && $item['key'] == $key) {
                            $isUpdate = true;
                            break;
                        }
                    }
                    if ($isUpdate) {
                        $tomJson['operation'] = $this->UPDATE_FUNC;
                    } else {
                        $tomJson['operation'] = $this->CREATE_FUNC;
                    }
                }
            }
        }
        return $tomJson;
    }

    private function buildTomJson($threadId, $tomId, $operation, $body)
    {
        return [
            'threadId' => $threadId,
            'tomId' => $tomId,
            'operation' => $operation,
            'body' => $body
        ];
    }

    /**
     * 检测是否有需要支付的 tom
     *
     * @param array $tomJsons
     * @return bool
     */
    private function needPay($tomJsons)
    {
        if (empty($tomJsons)) {
            return false;
        }
        $tomTypes = array_keys($tomJsons);
        foreach ($tomTypes as $tomType) {
            $tomService = Arr::get(TomConfig::$map, $tomType . '.service');
            if (class_exists($tomService) && constant($tomService . '::NEED_PAY')) {
                return true;
            } else {
                DzqLog::info('service_not_exist', [$tomService, $tomJsons, $tomTypes]);
            }
        }
        return false;
    }

    /**
     * @param $threadId
     * @param bool $isDeleteRedOrder 删除红包相关数据
     * @param bool $isDeleteRewardOrder 删除悬赏相关数据
     */
    public function delRedRelations($threadId, $isDeleteRedOrder = false, $isDeleteRewardOrder = false)
    {
        //将对应的 order、orderChildren、threadRedPacket、threadReward 与 原帖 脱离关系
        $order = self::getRedOrderInfo($threadId);
        if (empty($order) || $order->staus != Order::ORDER_STATUS_PAID) {         //订单未支付的情况下才删除数据
            if ($isDeleteRedOrder) {      //删除之前的order、orderChildren、$threadRedPacket
                Order::query()->where('thread_id', $threadId)->update(['thread_id' => 0]);
                if ($order->type == Order::ORDER_TYPE_MERGE) {
                    OrderChildren::query()->where(['order_sn' => $order->order_sn, 'thread_id' => $threadId])->update(['thread_id' => 0]);
                }
                ThreadRedPacket::query()->where(['thread_id' => $threadId])->update(['thread_id' => 0, 'post_id' => 0]);
            }
            if ($isDeleteRewardOrder) {
                Order::query()->where('thread_id', $threadId)->update(['thread_id' => 0]);
                if ($order->type == Order::ORDER_TYPE_MERGE) {
                    OrderChildren::query()->where(['order_sn' => $order->order_sn, 'thread_id' => $threadId])->update(['thread_id' => 0]);
                }
                ThreadReward::query()->where(['thread_id' => $threadId])->update(['thread_id' => 0, 'post_id' => 0]);
            }
        }
    }

    public function getRedOrderInfo($threadId)
    {
        return Order::query()->where('thread_id', $threadId)
            ->whereIn('type', [Order::ORDER_TYPE_REDPACKET, Order::ORDER_TYPE_QUESTION_REWARD, Order::ORDER_TYPE_MERGE])
            ->first();
    }
}
