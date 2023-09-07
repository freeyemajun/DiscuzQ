<?php
/**
 *@OA\Post(
 *    path = "/api/v3/trade/notify/wechat",
 *    summary = "微信支付回调",
 *    description = "Discuz! Q 微信支付回调",
 *    tags ={"支付钱包"},
 *     @OA\RequestBody(
 *        required=true,
 *        description = "支付回调",
 *        @OA\JsonContent(
 *           @OA\Property(property="appid",type="string",description="微信商户appid"),
 *           @OA\Property(property="bank_type",type="string",description="银行类型"),
 *           @OA\Property(property="cash_fee",type="number",description="现金支付金额订单现金支付金额"),
 *           @OA\Property(property="fee_type", type="string",description="币种"),
 *           @OA\Property(property="is_subscribe", type="string",description="是否关注公众账号	；Y-关注，N-未关注"),
 *           @OA\Property(property="mch_id", type="string",description="商户id"),
 *           @OA\Property(property="nonce_str", type="string",description="随机字符串"),
 *           @OA\Property(property="openid", type="string",description="用户在商户appid下的唯一标识"),
 *           @OA\Property(property="out_trade_no", type="string",description="订单编号"),
 *           @OA\Property(property="result_code", type="string",description="支付结果；SUCCESS/FAIL"),
 *           @OA\Property(property="return_code", type="string",description="返回状态码 SUCCESS/FAIL"),
 *           @OA\Property(property="sign", type="string",description="签名"),
 *           @OA\Property(property="time_end", type="string",description="支付完成时间"),
 *           @OA\Property(property="total_fee", type="number",description="订单总金额，单位为分"),
 *           @OA\Property(property="trade_type", type="string",description="交易类型；JSAPI、NATIVE、APP"),
 *           @OA\Property(property="transaction_id", type="string",description="微信支付订单号")
 *       ),
 *     ),
 *
 * @OA\Response(
 *        response = 200,
 *        description = "",
 *        @OA\JsonContent(
 *          @OA\Property(property="return_code", type="string", description="返回状态码  SUCCESS/FAIL"),
 *          @OA\Property(property="return_msg", type="string", description="返回信息")
 *     )

 *    )
 *)
 *
 *
 *
 */

