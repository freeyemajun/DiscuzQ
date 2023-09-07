<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/manage.posts.list",
 *     summary = "回复(评论)列表",
 *     description = "回复(评论)列表",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_page"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_perPage"),
 *     @OA\Parameter(name = "isDeleted", in = "query", required = true, description = "回收站，isDeleted=yes表示展示被删除的回复", @OA\Schema(type = "string", enum = {"yes", "no"})),
 *     @OA\Parameter(name = "isApproved", in = "query", required = false, description = "内容审核，展示应审核的回复", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "q", in = "query", required = false, description = "搜索内容", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "nickname", in = "query", required = false, description = "作者昵称", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "createdAtBegin", in = "query", required = false, description = "发布时间-开始", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "createdAtEnd", in = "query", required = false, description = "发布时间-结束", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "sort", in = "query", required = true, description = "排序", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "highlight", in = "query", required = false, description = "显示敏感词，ues表示显示", @OA\Schema(type = "string", enum = {"yes", "no"})),
 *     @OA\Response(response = 200, description = "返回评论列表", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *              @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *              @OA\Schema(
 *                  @OA\Property(property = "pageData", type = "array", @OA\Items(type = "object", allOf = {
 *                      @OA\Schema(@OA\Property(property = "postId", type = "integer", description = "评论id")),
 *                      @OA\Schema(@OA\Property(property = "threadId", type = "integer", description = "主题id")),
 *                      @OA\Schema(@OA\Property(property = "replyPostId", type = "integer", description = "回复评论id")),
 *                      @OA\Schema(@OA\Property(property = "userId", type = "integer", description = "评论者id")),
 *                      @OA\Schema(@OA\Property(property = "content", type = "object", description = "评论内容", allOf = {
 *                          @OA\Schema(@OA\Property(property = "text", type = "string", description = "内容")),
 *                          @OA\Schema(@OA\Property(property = "indexes", type = "array", description = "关联内容", @OA\Items()))
 *                      })),
 *                      @OA\Schema(@OA\Property(property = "ip", type = "string", description = "ip")),
 *                      @OA\Schema(@OA\Property(property = "updatedAt", type = "string", description = "更新时间")),
 *                      @OA\Schema(@OA\Property(property = "title", type = "string", description = "标题")),
 *                      @OA\Schema(@OA\Property(property = "nickname", type = "string", description = "评论者昵称")),
 *                      @OA\Schema(@OA\Property(property = "deletedUserArr", type = "object", description = "删除者信息", allOf = {
 *                           @OA\Schema(ref = "#/components/schemas/deleted_user_detail")
 *                      })),
 *                      @OA\Schema(@OA\Property(property = "lastDeletedLog", type = "object", description = "", allOf = {
 *                           @OA\Schema(@OA\Property(property = "message", type = "string", description = "操作备注"))
 *                      }))
 *                  }))
 *              )
 *          }))
 *     })
 * ))
 */