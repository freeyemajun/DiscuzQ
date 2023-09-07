<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/emoji.list",
 *     summary = "获取表情列表",
 *     description = "获取表情列表",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\Response(response = 200, description = "获取表情列表", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(
 *                  @OA\Property(property = "Data", type = "array", @OA\Items(type = "object", allOf = {
 *                      @OA\Schema(@OA\Property(property = "id", type = "integer", description = "id")),
 *                      @OA\Schema(@OA\Property(property = "category", type = "string", description = "类别")),
 *                      @OA\Schema(@OA\Property(property = "url", type = "string", description = "url地址")),
 *                      @OA\Schema(@OA\Property(property = "code", type = "string", description = "code")),
 *                      @OA\Schema(@OA\Property(property = "order", type = "string", description = "排序")),
 *                      @OA\Schema(@OA\Property(property = "createdAt", type = "string",format="datetime",description = "创建时间")),
 *                      @OA\Schema(@OA\Property(property = "updatedAt", type = "string",format="datetime",description = "更新时间")),
 *                  }))
 *          )
 *    }))
 * )
 */
