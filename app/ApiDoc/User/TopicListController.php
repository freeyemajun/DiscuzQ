<?php
/**
 *@OA\Get(
 *    path = "/api/v3/topics.list",
 *    summary = "话题列表",
 *    description = "Discuz! Q 话题列表",
 *    tags ={"发布与展示"},
 *    @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *    @OA\Parameter(ref = "#/components/parameters/page"),
 *    @OA\Parameter(ref = "#/components/parameters/perPage"),
 *    @OA\Parameter(
 *        name="filter[hot]",
 *        in="query",
 *        required=true,
 *        description="潮流话题标识,1潮流话题(热度排序),0话题列表(时间倒序)",
 *        @OA\Schema(type="integer", default=1)
 *    ),
 *    @OA\Parameter(
 *        name="filter[topicId]",
 *        in="query",
 *        required=false,
 *        description="话题ID，获取该话题详情及相关主题",
 *        @OA\Schema(type="integer")
 *    ),
 *    @OA\Parameter(
 *        name="filter[content]",
 *        in="query",
 *        required=false,
 *        description="话题搜索内容",
 *        @OA\Schema(type="string")
 *    ),
 *
 * @OA\Response(
 *        response = 200,
 *        description = "返回话题列表",
 *        @OA\JsonContent(allOf ={
 *                 @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                 @OA\Schema(@OA\Property(property = "Data", type = "object",allOf={
 *                    @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *                    @OA\Schema(title="话题列表",description="话题列表", @OA\Property(property="pageData",type="array", @OA\Items(type="object",
 *                          @OA\Property(property = "topicId", type = "integer", description = "话题id"),
 *                          @OA\Property(property = "userId", type = "integer", description = "话题创建人id"),
 *                          @OA\Property(property = "nickname", type = "string", description = "话题创建人昵称"),
 *                          @OA\Property(property = "viewCount", type = "integer", description = "话题查看数量"),
 *                          @OA\Property(property = "threadCount", type = "integer", description = "话题引用帖子数量"),
 *                          @OA\Property(property = "recommended", type = "integer", description = "是否推荐；true：推荐、false：不推荐"),
 *                          @OA\Property(property = "content", type = "string", description = "话题内容"),
 *                          @OA\Property(property = "recommendAt", type = "string", format="datetime", description = "推荐时间"),
 *                       ))
 *                  )}))
 *            }
 *        )
 *    )
 *)
 *
 *
 */

