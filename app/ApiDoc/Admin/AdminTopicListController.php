<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/topics.list",
 *     summary = "话题列表",
 *     description = "话题列表",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_page"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_perPage"),
 *     @OA\Parameter(name = "filter[recommended]", in = "query", required = false, description = "是否推荐", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "filter[username]", in = "query", required = false, description = "作者，展示应审核的回复", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "filter[content]", in = "query", required = false, description = "搜索内容", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "filter[createdAtBegin]", in = "query", required = false, description = "创建时间范围-起始", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "filter[createdAtEnd]", in = "query", required = false, description = "创建时间范围-结束", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "filter[threadCountBegin]", in = "query", required = false, description = "主题数介于-起始数", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "filter[threadCountEnd]", in = "query", required = false, description = "主题数介于-截止数", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "filter[viewCountBegin]", in = "query", required = false, description = "热度数范围-起始数", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "filter[viewCountEnd]", in = "query", required = false, description = "热度数范围-截止数", @OA\Schema(type = "integer")),
 *     @OA\Response(response = 200, description = "返回话题列表", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *              @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *              @OA\Schema(
 *                  @OA\Property(property = "pageData", type = "array", @OA\Items(type = "object", allOf = {
 *                      @OA\Schema(@OA\Property(property = "topicId", type = "integer", description = "话题id")),
 *                      @OA\Schema(@OA\Property(property = "userId", type = "integer", description = "作者id")),
 *                      @OA\Schema(@OA\Property(property = "username", type = "string", description = "作者用户名")),
 *                      @OA\Schema(@OA\Property(property = "content", type = "string", description = "话题内容")),
 *                      @OA\Schema(@OA\Property(property = "viewCount", type = "integer", description = "热度数")),
 *                      @OA\Schema(@OA\Property(property = "threadCount", type = "integer", description = "主题数")),
 *                      @OA\Schema(@OA\Property(property = "createdAt", type = "string", description = "创建时间")),
 *                      @OA\Schema(@OA\Property(property = "recommended", type = "boolean", description = "是否推荐")),
 *                      @OA\Schema(@OA\Property(property = "recommendedAt", type = "string", description = "推荐时间")),
 *                      @OA\Schema(@OA\Property(property = "threads", type = "array", description = "关联主题", @OA\Items()))
 *                  }))
 *              )
 *         }))
 *    }))
 * )
 */