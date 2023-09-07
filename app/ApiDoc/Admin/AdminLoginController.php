<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/login",
 *     summary = "管理员登录",
 *     description = "管理员登录",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "username", type = "string", description = "用户名"),
 *            @OA\Property(property = "password", type = "string", description = "密码")
 *        )
 *     ),
 *     @OA\Response(response=200,description="返回登录态相关信息",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property="Data",type="object",
 *                 @OA\Property(property="tokenType",type="string",description="token 类型"),
 *                 @OA\Property(property="expiresIn",type="integer",description="过期时间(秒),30天过期"),
 *                 @OA\Property(property="accessToken",type="string",description="token"),
 *                 @OA\Property(property="id",type="integer",description="用户id")
 *             )),
 *         })
 *     )
 * )
 */
