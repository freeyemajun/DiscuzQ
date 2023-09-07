<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/create.crawler",
 *     summary = "数据爬取",
 *     description = "数据爬取",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\Response(response = 200, description = "数据爬取", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *    }))
 * )
 */
