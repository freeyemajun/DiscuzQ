<?php
/**
 *@OA\Get(
 *    path = "/api/v3/posts.list",
 *    summary = "评论列表",
 *    description = "Discuz! Q 评论列表",
 *    tags ={"发布与展示"},
 *@OA\Parameter(ref = "#/components/parameters/bear_token"),
 *@OA\Parameter(ref = "#/components/parameters/page"),
 *@OA\Parameter(ref = "#/components/parameters/perPage"),
 *@OA\Parameter(
 *     name="filter[thread]",
 *     in="query",
 *     required=false,
 *     description="帖子id",
 *     @OA\Schema(
 *          type="integer",
 *          default=1
 *      )
 * ),
 *@OA\Parameter(
 *     name="sort",
 *     in="query",
 *     required=false,
 *     description="排序字段",
 *     @OA\Schema(
 *          type="string",
 *          default="createdAt"
 *      ),
 *),
 *
 * @OA\Response(
 *        response = 200,
 *        description = "返回关注/粉丝列表",
 *        @OA\JsonContent(allOf ={
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(@OA\Property(property = "Data", type = "object",allOf={
 *                    @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *                    @OA\Schema( title="评论列表", description="评论列表", @OA\Property(property = "pageData", type = "array", @OA\Items(type = "object",allOf={
 *                              @OA\Schema(ref="#/components/schemas/dzq_post_detail"),
 *                              @OA\Schema( title="最近3条评论", description="最近3条评论", @OA\Property(property = "lastThreeComments", type="array", @OA\Items(type="object",
 *                                      ref="#/components/schemas/dzq_post_detail"
 *                              )))
 *                   })))
 *                }))
 *       })
 *    )
 *)
 *
 *
 *
 *
 */

