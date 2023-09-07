<?php
/**
 *@OA\Post(
 *    path = "/api/v3/order.create",
 *    summary = "创建订单",
 *    description = "Discuz! Q 创建订单",
 *    tags ={"支付钱包"},
 *    @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required=true,
 *        description = "创建订单",
 *        @OA\JsonContent(
 *           @OA\Property(property="amount",type="number",description="订单金额"),
 *           @OA\Property(property="isAnonymous",type="boolean",description="是否匿名（必填）"),
 *           @OA\Property(property="title",type="string",description="订单名称"),
 *           @OA\Property(property="redAmount", type="number",description="红包金额"),
 *           @OA\Property(property="rewardAmount", type="number",description="悬赏金额"),
 *           @OA\Property(property="threadId", type="integer",description="帖子id"),
 *           @OA\Property(property="groupId", type="integer",description="用户组id（必填type类型：4）"),
 *           @OA\Property(property="payeeId", type="integer",description="收款人id"),
 *           @OA\Property(property="type", type="integer",description="交易类型；
 * 1：注册、2：打赏、3：付费主题、4：付费用户组、5：问答提问、6：问答围观、7：付费附件、8：站点续费、9：红包、10：悬赏、11：合并支付、20：文字贴红包、21：长文贴红包（必填）、30：充值"),
 *        )
 *     ),
 *
 * @OA\Response(
 *        response = 200,
 *        description = "订单状态",
 *        @OA\JsonContent(allOf ={
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(title="订单状态", description="刚才提交的订单返回的状态", @OA\Property(property="Data",type="object",
 *                      @OA\Property(property="orderSn", type="string", description="订单编号"),
 *                      @OA\Property(property="paymentSn", type="string", description="支付订单编号"),
 *                      @OA\Property(property="status", type="integer", description="订单状态；0：待付款、1：已付款、4：订单已过期"),
 *                ))
 *            }
 *        )
 *    )
 *)
 *
 *
 *
 *
 */

