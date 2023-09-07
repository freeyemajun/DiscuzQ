<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/sms.login",
 *     summary="手机号登录",
 *     description="用户发送手机验证码进行登录，不存在此手机时将自动注册账户",
 *     tags={"注册登录"},
 *     @OA\RequestBody(
 *         description = "请求参数",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\property(property="mobile",type="string",description="手机号码"),
 *             @OA\property(property="code",type="string",description="验证码"),
 *             @OA\property(property="inviteCode",type="string",description="邀请码"),
 *             @OA\property(property="type",type="string",description="请求类型"),
 *         )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回用户信息",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(ref="#/components/schemas/dzq_login_token"),
 *        })
 *     )
 * )
 */
