<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/recommend.users",
 *     summary = "获取推荐用户信息",
 *     description = "获取推荐用户信息",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\Response(response = 200, description = "返回推荐用户信息", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(
 *                  @OA\Property(property = "Data", type = "array", @OA\Items(type = "object", allOf = {
 *                      @OA\Schema(@OA\Property(property = "userId", type = "integer", description = "用户id")),
 *                      @OA\Schema(@OA\Property(property = "username", type = "string", description = "用户名")),
 *                      @OA\Schema(@OA\Property(property = "nickname", type = "string", description = "用户昵称")),
 *                  }))
 *          )
 *    }))
 * )
 */

