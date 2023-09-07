<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/thread.optimize",
 *     summary = "一键开启/关闭帖子敏感数据",
 *     description = "一键开启/关闭帖子敏感数据",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "一键开启/关闭帖子敏感数据",
 *        @OA\JsonContent(
 *            @OA\Property(property = "openViewCount", type = "string", description = "openViewCount"),
 *        )
 *     ),
 *     @OA\Response(response=200,description="返回数据",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *             @OA\Schema(
 *                  @OA\Property(property = "Data", type = "array", @OA\Items(type = "object", allOf = {
 *
 *                  }))
 *          )
 *         })
 *     )
 * )
 */

