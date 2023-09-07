<?php
/**
 *@OA\Post(
 *    path = "/api/v3/trade/pay/order",
 *    summary = "钱包支付",
 *    description = "Discuz! Q 钱包支付",
 *    tags ={"支付钱包"},
 *    @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required=true,
 *        description = "钱包支付",
 *        @OA\JsonContent(
 *           @OA\Property(property="orderSn",type="string",description="订单编号"),
 *           @OA\Property(property="payPassword",type="string",description="支付密码"),
 *           @OA\Property(property="paymentType",type="integer",description="支付类型：
 * 10：微信扫码支付、11：微信h5支付、12：微信网页、公众号支付、 13：微信小程序支付、 20：钱包支付"),
 *     ),
 *     ),
 *
 * @OA\Response(
 *        response = 200,
 *        description = "钱包支付响应",
 *        @OA\JsonContent(allOf ={
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(title="支付订单数据", description="支付订单数据", @OA\Property(property="Data",type="object",
 *                  @OA\Property(property="desc", type="string", description="描述"),
 *                  @OA\Property(property="walletPayResult", type="object", description="钱包支付结果"),
 *                  @OA\Property(property="message", type="string", description="结果描述"),
 *                  @OA\Property(property="result", type="string", description="success/failed")
 *                  )
 *               )
 *            })
 *        )
 *    )
 *)
 *
 *
 *
 *
 */

