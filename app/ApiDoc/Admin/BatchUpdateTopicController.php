<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/topics.batch.update",
 *     summary = "批量推荐话题",
 *     description = "批量推荐话题",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "ids", type = "string", description = "话题id，多个请用英文逗号间隔"),
 *            @OA\Property(property = "isRecommended", type = "integer", description = "推荐话题,0取消推荐,1推荐", enum = {0, 1})
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "",
 *        @OA\JsonContent(allOf = {
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property = "Data", type = "array", @OA\Items()))
 *       })
 *     )
 * )
 */
