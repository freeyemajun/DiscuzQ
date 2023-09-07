<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/username.register",
 *     summary="用户名密码注册",
 *     description="用户名密码注册",
 *     tags={"注册登录"},
 *     @OA\RequestBody(
 *         description = "用户名密码模式注册参数",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\property(property="username",type="string",description="用户名"),
 *             @OA\property(property="password",type="string",description="密码"),
 *             @OA\property(property="passwordConfirmation",type="string",description="密码"),
 *             @OA\property(property="code",type="string",description="注册邀请码"),
 *             @OA\property(property="nickname",type="string",description="昵称"),
 *             @OA\property(property="captchaTicket",type="string",description="验证码参数"),
 *             @OA\property(property="captchaRandStr",type="string",description="验证码参数"),
 *         )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回登录态相关信息",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(ref="#/components/schemas/dzq_login_token"),
 *        })
 *     )
 * )
 */
