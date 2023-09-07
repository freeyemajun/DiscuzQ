<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/manage.thread.list",
 *     summary = "帖子列表",
 *     description = "帖子列表",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_page"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_perPage"),
 *     @OA\Parameter(name = "nickname", in = "query", required = false, description = "主题作者昵称", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "threadType", in = "query", required = false, description = "主题类型：1置顶主题，2精华主题，3置顶+精华主题，4付费首页主题", @OA\Schema(type = "integer", enum = {1, 2, 3, 4})),
 *     @OA\Parameter(name = "viewCountGt", in = "query", required = false, description = "浏览次数大于", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "viewCountLt", in = "query", required = false, description = "浏览次数小于", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "postCountGt", in = "query", required = false, description = "被回复数大于", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "postCountLt", in = "query", required = false, description = "被回复数小于", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "isApproved", in = "query", required = false, description = "内容审核，展示应审核的主题", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "threadId", in = "query", required = false, description = "主题id", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "q", in = "query", required = false, description = "搜索内容", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "isDeleted", in = "query", required = false, description = "回收站，isDeleted=yes表示展示被删除的主题", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "createdAtBegin", in = "query", required = false, description = "发布时间-开始", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "createdAtEnd", in = "query", required = false, description = "发布时间-结束", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "categoryId", in = "query", required = false, description = "搜索分类", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "sort", in = "query", required = true, description = "排序", @OA\Schema(type = "string")),
 *     @OA\Response(response = 200, description = "返回帖子列表", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *              @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *              @OA\Schema(
 *                  @OA\Property(property = "pageData", type = "array", @OA\Items(type = "object", allOf = {
 *                      @OA\Schema(ref = "#/components/schemas/dzq_thread"),
 *                      @OA\Schema(@OA\Property(property = "deletedUserArr", type = "object", description = "删除者信息", allOf = {
 *                           @OA\Schema(ref = "#/components/schemas/deleted_user_detail")
 *                      })),
 *                      @OA\Schema(@OA\Property(property = "lastDeletedLog", type = "object", description = "", allOf = {
 *                           @OA\Schema(@OA\Property(property = "message", type = "string", description = "操作备注"))
 *                      })),
 *                      @OA\Schema(@OA\Property(property = "lastPostedUser", type = "object", description = "最新回复作者", allOf = {
 *                           @OA\Schema(@OA\Property(property = "userId", type = "integer", description = "用户id")),
 *                           @OA\Schema(@OA\Property(property = "lastNickname", type = "string", description = "昵称")),
 *                           @OA\Schema(@OA\Property(property = "createdAt", type = "string", description = "创建时间"))
 *                      }))
 *                  }))
 *              )
 *         }))
 *     }))
 * )
 */