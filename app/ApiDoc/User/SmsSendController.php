<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/sms.send",
 *     summary="短信码发送",
 *     description="需要用到手机号短信验证码时",
 *     tags={"注册登录"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         description = "请求参数",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\property(property="mobile",type="string",description="手机号码"),
 *             @OA\property(property="type",type="string",description="类型"),
 *             @OA\property(property="captchaTicket",type="string",description="验证码参数"),
 *             @OA\property(property="captchaRandStr",type="string",description="验证码参数"),
 *         )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回间隔时间",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(
 *                @OA\Property(property="Data",type="object",@OA\Property(property="interval",type="integer",description="间隔时间"))
 *            )
 *        })
 *     )
 * )
 */
