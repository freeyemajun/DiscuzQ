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

namespace App\Listeners\Order;

use App\Events\Order\Updated;
use App\Models\Order;
use App\Models\SiteInfoDaily;
use Carbon\Carbon;
use Discuz\Contracts\Setting\SettingsRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Events\Dispatcher;

class OrderSubscriber
{
    public function subscribe(Dispatcher $events)
    {
        /**
         * 订单支付成功
         *
         * @see SendNotifyAfterPaySuccessful 触发对应通知内容
         */
        $events->listen(Updated::class, [$this, 'whenOrderPaid']);
        $events->listen(Updated::class, SendNotifyAfterPaySuccessful::class);
    }

    /**
     * 支付完成时
     *
     * @param Updated $event
     * @throws BindingResolutionException
     */
    public function whenOrderPaid(Updated $event)
    {
        $order = $event->order;

        // 付费加入站点的订单，支付成功后修改用户信息
        if ($order->type == Order::ORDER_TYPE_REGISTER && $order->status == Order::ORDER_STATUS_PAID) {
            $day = app()->make(SettingsRepository::class)->get('site_expire');
            // 修改用户过期时间、订单过期时间,如果没有有效期，订单过期时间设置为null
            if (empty($day)) {
                $day = 365*99;
            }
            $expired = Carbon::now()->addDays($day);
            $order->user->expired_at = $expired;
            $order->expired_at = $expired;
            $order->user->save();
            $order->save();
        }

        // 续费站点的订单，支付成功后修改用户信息
        if ($order->type == Order::ORDER_TYPE_RENEW && $order->status == Order::ORDER_STATUS_PAID) {
            $day = app()->make(SettingsRepository::class)->get('site_expire');
            // 如果没有设置有效期，则设置有效期为 99 年
            if (empty($day)) {
                $day = 365 * 99;
            }
            $expiredDate = date('Y-m-d H:i:s', time() + $day * 86400 - 1);
            // 过期时间未到时，则续费时间加剩余时间
            if (!empty($order->user->expired_at) && (strtotime($order->user->expired_at) > time())) {
                $remainTime = strtotime($order->user->expired_at) - time();
                $reNewTime = time() + $remainTime + $day * 86400 - 1;
                $expiredDate = date('Y-m-d H:i:s', $reNewTime);
            }
            $order->user->expired_at = $expiredDate;
            $order->expired_at = $expiredDate;
            $order->user->save();
            $order->save();
        }

        // 打赏主题的订单
        if ($order->type == Order::ORDER_TYPE_REWARD && $order->status == Order::ORDER_STATUS_PAID) {
            // 更新主题打赏数
            $order->thread->refreshRewardedCount()->save();
        }

        // 更新主题付费数(主题付费、附件付费)
        if (
            ($order->type == Order::ORDER_TYPE_THREAD || $order->type == Order::ORDER_TYPE_ATTACHMENT) &&
            $order->status == Order::ORDER_STATUS_PAID
        ) {
            $order->thread->refreshPaidCount()->save();
        }

        //更新 site_info_dailies 订单情况
        $today = date('Y-m-d', time());
        $site_info_daily = SiteInfoDaily::query()->where('date', $today)->first();
        if (empty($site_info_daily)) {
            $site_info_daily = new SiteInfoDaily();
            $site_info_daily->date = $today;
            $site_info_daily->orders_count = 0;
            $site_info_daily->orders_money = 0;
            $site_info_daily->order_royalty = 0;
            $site_info_daily->total_register_profit = 0;
        }
        $site_info_daily->orders_count += 1;
        $site_info_daily->orders_money += $order->amount;
        $site_info_daily->order_royalty += $order->master_amount;
        if ($order->type == Order::ORDER_TYPE_REGISTER) {
            $site_info_daily->total_register_profit += $order->amount;
        }
        $site_info_daily->save();
    }
}
