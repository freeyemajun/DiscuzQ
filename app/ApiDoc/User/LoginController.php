<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/username.login",
 *     summary="用户名密码登录",
 *     description="普通账号密码登录",
 *     tags={"注册登录"},
 *     @OA\RequestBody(
 *         description = "用户名密码模式登录参数",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\property(property="username",type="string",description="用户名"),
 *             @OA\property(property="password",type="string",description="密码"),
 *             @OA\property(property="type",type="string",description="请求类型"),
 *             @OA\property(property="sessionToken",type="string",description="用户sessionToken"),
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
