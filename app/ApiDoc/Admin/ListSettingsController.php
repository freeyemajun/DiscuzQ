<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/settings",
 *     summary = "获取站点设置",
 *     description = "获取站点设置",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\Parameter(name = "key", in = "query", required = false, description = "设置项key", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "tag", in = "query", required = false, description = "设置项tag", @OA\Schema(type = "string")),
 *     @OA\Response(response = 200, description = "返回站点设置列表", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *                @OA\Schema(@OA\Property(property = "key", type = "string", description = "设置项key")),
 *                @OA\Schema(@OA\Property(property = "value", type = "string", description = "设置项value")),
 *                @OA\Schema(@OA\Property(property = "tag", type = "string", description = "设置项tag")),
 *         }))
 *    }))
 * )
 */
