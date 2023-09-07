<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/adminInvite.link.create",
 *     summary = "邀请朋友生成code",
 *     description = "邀请朋友生成code",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\Parameter(name = "groupId", in = "query", required = true, description = "类别id", @OA\Schema(type = "integer")),
 *     @OA\Response(response = 200, description = "返回推荐话题信息", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(
 *                  @OA\Property(property = "Data", type = "array", @OA\Items(type = "object", allOf = {
 *                      @OA\Schema(@OA\Property(property = "userId", type = "integer", description = "用户id")),
 *                      @OA\Schema(@OA\Property(property = "code", type = "string", description = "code")),
 *                  }))
 *          )
 *    }))
 * )
 */
