<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/refresh.token",
 *     summary = "刷新token",
 *     description = "刷新token接口",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "返回token信息",
 *        @OA\JsonContent(
 *            @OA\Property(property = "refreshToken", type = "string", description = "refresh token"),
 *        )
 *     ),
 *     @OA\Response(response=200,description="返回用户信息",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property="Data",type="object",
 *                 @OA\Property(property="tokenType",type="string", description = "token 类型"),
 *                 @OA\Property(property="expiresIn",type="integer", description = "过期时间(秒)"),
 *                 @OA\Property(property="accessToken",type="string", description = "访问令牌"),
 *                 @OA\Property(property="refreshToken",type="string", description ="刷新令牌"),
 *             ))
 *         })
 *     )
 * )
 */
