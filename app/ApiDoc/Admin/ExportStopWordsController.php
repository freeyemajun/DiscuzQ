<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/stopWords/export",
 *     summary = "导出过滤词库",
 *     description = "导出过滤词库",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\Parameter(name = "keyword", in = "query", required = true, description = "关键字", @OA\Schema(type = "string")),
 *     @OA\Response(response = 200, description = "过滤词库", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout")
 *    }))
 * )
 */
