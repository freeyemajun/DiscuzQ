<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/sms.reset.pay.pwd",
 *     summary="重置密码",
 *     description="重置密码接口",
 *     tags={"个人中心"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "重置密码  ",
 *         @OA\JsonContent(
 *             @OA\Property(property="mobile",type="string",description="手机号"),
 *             @OA\Property(property="code",type="boolean",description="验证码"),
 *             @OA\Property(property="payPassword",type="string", description="支付密码"),
 *             @OA\Property(property="payPasswordConfirmation",type="string", description="支付密码确认"),
 *             )
 *           ),
 * @OA\Response(response=200,
 *        description="重置密码接口返回",
 *        @OA\JsonContent(allOf ={
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout")
 *     })
 *     )
 * )
 *        )
 *     )
 */
