<?php
/**
 * @OA\Get(
 *     path="/api/backAdmin/statistic.financeChart",
 *     summary="财务统计",
 *     description="获取盈利图表数据",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token_true"),
 *     @OA\Parameter(
 *        name="type",
 *        in="query",
 *        required=false,
 *        description = "统计方式（1 日 2 周 3 月）",
 *        @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *        name="createdAtBegin",
 *        in="query",
 *        required=false,
 *        description = "时间大于",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *        name="createdAtEnd",
 *        in="query",
 *        required=false,
 *        description = "时间小于",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回绑定scheme码",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(
 *                @OA\Property(property="Data",type="object",
 *                    @OA\Property(property="id",type="number",description="数据 id"),
 *                    @OA\Property(property="income",type="number",description="用户充值金额"),
 *                    @OA\Property(property="withdrawal",type="number",description="用户提现金额"),
 *                    @OA\Property(property="orderCount",type="number",description="订单数量"),
 *                    @OA\Property(property="orderAmount",type="number",description="订单金额"),
 *                    @OA\Property(property="totalProfit",type="number",description="平台总盈利"),
 *                    @OA\Property(property="registerProfit",type="number",description="注册加入收入"),
 *                    @OA\Property(property="masterPortion",type="number",description="打赏提成收入"),
 *                    @OA\Property(property="withdrawalProfit",type="number",description="提现手续费收入"),
 *                    @OA\Property(property="createdAt",type="string",description="创建时间"),
 *                    @OA\Property(property="date",type="string",description="创建时间"),
 *                )
 *            )
 *        })
 *     )
 * )
 */
