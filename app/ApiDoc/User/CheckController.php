<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/username.check",
 *     summary="用户名、昵称重名检测",
 *     description="用户名、昵称是否存在",
 *     tags={"注册登录"},
 *     @OA\RequestBody(
 *         description = "检测参数",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\property(property="username",type="string",description="用户名"),
 *             @OA\property(property="nickname",type="string",description="昵称"),
 *         )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回登录态相关信息",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *        })
 *     )
 * )
 */
