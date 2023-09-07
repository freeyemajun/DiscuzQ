<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/delete/users/avatar",
 *     summary = "删除用户头像",
 *     description = "删除用户头像",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "aid", type = "integer", description = "用户id")
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "返回用户信息",
 *        @OA\JsonContent(allOf = {
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property = "Data", type = "array", @OA\Items()))
 *       })
 *     )
 * )
 */
