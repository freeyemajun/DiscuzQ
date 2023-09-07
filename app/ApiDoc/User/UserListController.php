<?php
/**
 *@OA\Get(
 *    path = "/api/v3/users.list",
 *    summary = "用户列表",
 *    description = "Discuz! Q 用户列表",
 *    tags ={"发布与展示"},
 *@OA\Parameter(ref = "#/components/parameters/bear_token"),
 *@OA\Parameter(ref = "#/components/parameters/page"),
 *@OA\Parameter(ref = "#/components/parameters/perPage"),
 *@OA\Parameter(
 *     name="filter[hot]",
 *     in="query",
 *     required=false,
 *     description="是否为活跃用户；1：活跃、0：随机",
 *     @OA\Schema(
 *      type="integer", default=1
 * )
 * ),
 *
 * @OA\Response(
 *        response = 200,
 *        description = "返回用户列表",
 *        @OA\JsonContent(allOf ={
 *                 @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                 @OA\Schema(@OA\Property(property = "Data", type = "object",allOf={
 *                    @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *                    @OA\Schema(title="用户列表", description="用户列表", @OA\Property(property="pageData", type="array", @OA\Items(type="object",
 *                      @OA\Property(property = "avatar", type = "string", description = "用户头像url"),
 *                      @OA\Property(property = "userId", type = "integer", description = "用户id"),
 *                      @OA\Property(property = "nickname", type = "string", description = "用户所属组"),
 *                      @OA\Property(property = "groupName", type = "string", description = "话题引用帖子数量"),
 *                      @OA\Property(property = "followCount", type = "integer", description = "关注数"),
 *                      @OA\Property(property = "isFollow", type = "boolean", description = "是否关注"),
 *                      @OA\Property(property = "isMutualFollow", type = "boolean", description = "是否互关"),
 *                      @OA\Property(property = "likedCount", type = "integer", description = "点赞数"),
 *                      @OA\Property(property = "questionCount", type = "integer", description = "问题数"),
 *                      @OA\Property(property = "threadCount", type = "integer", description = "主题数"),
 *                      @OA\Property(property = "level", type = "integer", description = "用户层级"),
 *                    )))
 *                }))
 *            }
 *        )
 *    )
 *)
 *
 *
 */

