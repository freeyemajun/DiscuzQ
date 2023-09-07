<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/settings/logo",
 *     summary = "上传站点logo",
 *     description = "上传站点logo",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "type", type = "string", description = "类型"),
 *            @OA\Property(property = "logo", type = "string", description = "文件")
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "返回信息",
 *        @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *                @OA\Schema(@OA\Property(property = "key", type = "string", description = "设置项key")),
 *                @OA\Schema(@OA\Property(property = "value", type = "string", description = "设置项value")),
 *                @OA\Schema(@OA\Property(property = "tag", type = "string", description = "设置项tag")),
 *         }))
 *       })
 *     )
 * )
 */
