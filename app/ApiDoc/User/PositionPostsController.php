<?php
/**
 * @OA\Get(
 *     path = "/api/v3/posts.postion",
 *     summary = "获取评论位置",
 *     description = "获取评论位置",
 *     tags = {"发布与展示"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *        name = "filter[threadId]",
 *        in = "query",
 *        required = true,
 *        description = "帖子id",
 *        @OA\Schema(type = "integer")
 *     ),
 *     @OA\Parameter(
 *        name = "filter[postId]",
 *        in = "query",
 *        required = true,
 *        description = "评论id",
 *        @OA\Schema(type = "integer")
 *     ),
 *     @OA\Parameter(
 *        name = "filter[pageSize]",
 *        in = "query",
 *        required = false,
 *        description = "每页数量(该字段在帖子详情中获取评论位置时必传)",
 *        @OA\Schema(type = "integer")
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "返回评论位置",
 *        @OA\JsonContent(allOf = {
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *                    @OA\Schema(
 *                      @OA\Property(property = "page", type = "integer", description = "页码"),
 *                      @OA\Property(property = "location", type = "integer", description = "该页第几个位置"),
 *                      @OA\Property(property = "pageSize", type = "integer", description = "每页数量")
 *                    )
 *                }))
 *            }
 *        )
 *     )
 * )
 */
