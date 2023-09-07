<?php
/**
 * @OA\Get(
 *     path="/api/v3/order.detail",
 *     summary="订单详情",
 *     description="订单详情",
 *     tags={"支付钱包"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *        name="orderSn",
 *        in="query",
 *        required=true,
 *        description = "订单编号",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="订单详情",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(title="订单详情", description="订单详情", @OA\Property(property="Data",type="object",
 *                  @OA\Property(property="amount", type="number", description="订单金额"),
 *                  @OA\Property(property="createdAt", type="string", format="datetime", description="订单创建时间"),
 *                  @OA\Property(property="groupId", type="integer", description="组id"),
 *                  @OA\Property(property="id", type="integer", description="订单id"),
 *                  @OA\Property(property="orderSn", type="string", description="订单编号"),
 *                  @OA\Property(property="status", type="integer", description="订单状态；0：待付款、1：已付款、4：订单已过期"),
 *                  @OA\Property(property="threadId", type="integer", description="帖子id"),
 *                  @OA\Property(property="type", type="integer", description="交易类型；
 * 1：注册、2：打赏、3：付费主题、4：付费用户组、5：问答提问、6：问答围观、7：付费附件、8：站点续费、9：红包、10：悬赏、11：合并支付、20：文字贴红包、21：长文贴红包"),
 *            ))
 *        })
 *     )
 * )
 *
 */
