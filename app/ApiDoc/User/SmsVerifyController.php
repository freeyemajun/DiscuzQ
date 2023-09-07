<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/sms.verify",
 *     summary="验证手机号是否为当前用户",
 *     description="数据库有此手机用户，验证码一码一用",
 *     tags={"注册登录"},
 *     @OA\RequestBody(
 *         description = "请求参数",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\property(property="mobile",type="string",description="手机号码"),
 *             @OA\property(property="code",type="string",description="验证码"),
 *         )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回用户信息",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(ref="#/components/schemas/dzq_user_info"),
 *        })
 *     )
 * )
 */
