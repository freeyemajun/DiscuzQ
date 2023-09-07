<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/users/avatar",
 *     summary = "修改用户头像",
 *     description = "修改用户头像",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "aid", type = "integer", description = "用户id"),
 *            @OA\Property(property = "avatar", type = "string", description = "头像文件")
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "返回用户信息",
 *        @OA\JsonContent(allOf = {
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property = "Data", type = "array", @OA\Items(type = "object", allOf = {
 *              @OA\Schema(@OA\Property(property = "id", type = "integer", description = "用户id")),
 *              @OA\Schema(@OA\Property(property = "username", type = "string", description = "用户名")),
 *              @OA\Schema(@OA\Property(property = "avatarUrl", type = "string", description = "头像地址"))
 *           })))
 *       })
 *     )
 * )
 */
