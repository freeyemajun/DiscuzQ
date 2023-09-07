<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/export/users",
 *     summary = "导出用户信息",
 *     description = "导出用户信息",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "ids", type = "string", description = "用户id，多个请用英文逗号间隔；不传则导出全部用户信息")
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "",
 *        @OA\JsonContent(allOf = {
 *           @OA\Schema(@OA\Property(property = "文件", type = "string"))
 *       })
 *     )
 * )
 */
