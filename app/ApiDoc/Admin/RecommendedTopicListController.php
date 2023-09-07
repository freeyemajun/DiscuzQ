<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/recommend.topics",
 *     summary = "获取推荐话题信息",
 *     description = "获取推荐话题信息",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\Response(response = 200, description = "返回推荐话题信息", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(
 *                  @OA\Property(property = "Data", type = "array", @OA\Items(type = "object", allOf = {
 *                      @OA\Schema(@OA\Property(property = "topicId", type = "integer", description = "话题id")),
 *                      @OA\Schema(@OA\Property(property = "topicTitle", type = "string", description = "话题名称")),
 *                  }))
 *          )
 *    }))
 * )
 */
