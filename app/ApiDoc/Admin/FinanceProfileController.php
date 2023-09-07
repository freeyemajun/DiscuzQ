<?php
/**
 * @OA\Get(
 *     path="/api/backAdmin/statistic.finance",
 *     summary="财务统计",
 *     description="获取资金概况",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token_true"),
 *     @OA\Response(
 *        response=200,
 *        description="返回绑定scheme码",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(
 *                @OA\Property(property="Data",type="object",
 *                    @OA\Property(property="totalIncome",type="number",description="用户总充值(用户通过支付接口，充值进平台的总金额，不考虑充值手续费（目前是 注册加入 和 打赏时 产生充值 ）)"),
 *                    @OA\Property(property="totalWithdrawal",type="number",description="用户总提现(用户通过提现功能，从钱包提现到个人账户的成功总金额，以用户发起提现的金额计算，不考虑用户提现手续费)"),
 *                    @OA\Property(property="totalWallet",type="number",description="用户钱包总金额(所有用户的 可提现 + 冻结中 的金额总数)"),
 *                    @OA\Property(property="totalProfit",type="number",description="平台总盈利(注册加入收入+打赏提成收入+提现手续费收入)"),
 *                    @OA\Property(property="withdrawalProfit",type="number",description="提现手续费收入(用户总提现 * 提现手续费百分比)"),
 *                    @OA\Property(property="orderRoyalty",type="number",description="打赏提成收入(打赏订单给平台分成的收入之和)"),
 *                    @OA\Property(property="totalRegisterProfit",type="number",description="注册加入收入(注册加入平台，支付给平台的收入之和)"),
 *                    @OA\Property(property="orderCount",type="integer",description="用户订单总数(所有交易订单的总数量)"),
 *                )
 *            )
 *        })
 *     )
 * )
 */
