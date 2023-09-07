<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/threads.batch",
 *     summary = "批量修改帖子",
 *     description = "批量修改帖子",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "ids", type = "string", description = "帖子id，多个帖子id用英文逗号间隔"),
 *            @OA\Property(property = "categoryId", type = "integer", description = "批量移动到的分类id"),
 *            @OA\Property(property = "isSticky", type = "integer", description = "批量置顶：1置顶，0取消置顶"),
 *            @OA\Property(property = "isEssence", type = "integer", description = "批量设置精华：1精华，0取消精华"),
 *            @OA\Property(property = "isSite", type = "integer", description = "批量推送到付费首页：1推送，0取消推送"),
 *            @OA\Property(property = "isDeleted", type = "integer", description = "批量删除")
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "",
 *        @OA\JsonContent(allOf = {
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {}))
 *       })
 *     )
 * )
 */
