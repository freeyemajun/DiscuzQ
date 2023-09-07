<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/check.crawler.process",
 *     summary = "获取数据爬取进度",
 *     description = "获取数据爬取进度",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\Response(response = 200, description = "获取数据爬取进度", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(
 *                  @OA\Property(property = "Data", type = "array", @OA\Items(type = "object", allOf = {
 *                      @OA\Schema(@OA\Property(property = "status", type = "integer", description = "状态")),
 *                      @OA\Schema(@OA\Property(property = "progress", type = "integer", description = "进度")),
 *                      @OA\Schema(@OA\Property(property = "startCrawlerTime", type = "integer", description = "爬取开始时间")),
 *                      @OA\Schema(@OA\Property(property = "runtime", type = "integer", description = "用时")),
 *                      @OA\Schema(@OA\Property(property = "topic", type = "string", description = "主题")),
 *                      @OA\Schema(@OA\Property(property = "totalDataNumber", type = "integer", description = "总数据量")),
 *                  }))
 *          )
 *    }))
 * )
 */
