<?php
/**
 * @OA\Get(
 *     path = "/api/v3/posts.reply",
 *     summary = "查询单条评论的最新回复评论",
 *     description = "查询单条评论的最新回复评论",
 *     tags = {"发布与展示"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *        name = "postId",
 *        in = "query",
 *        required = true,
 *        description = "评论id",
 *        @OA\Schema(type = "integer")
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "返回评论最新回复数据",
 *        @OA\JsonContent(allOf = {
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *                    @OA\Schema(@OA\Property(property = "id", type = "integer", description = "评论id")),
 *                    @OA\Schema(@OA\Property(property = "userId", type = "integer", description = "评论作者id")),
 *                    @OA\Schema(@OA\Property(property = "replyPostId", type = "integer", description = "最新回复id")),
 *                    @OA\Schema(@OA\Property(property = "replyUserId", type = "integer", description = "最新回复作者id")),
 *                    @OA\Schema(@OA\Property(property = "commentPostId", type = "integer", description = "评论回复id")),
 *                    @OA\Schema(@OA\Property(property = "commentUserId", type = "integer", description = "评论回复作者id")),
 *                    @OA\Schema(@OA\Property(property = "summaryText", type = "string", description = "评论摘要")),
 *                    @OA\Schema(@OA\Property(property = "content", type = "string", description = "评论内容")),
 *                    @OA\Schema(@OA\Property(property = "replyCount", type = "integer", description = "关联回复数")),
 *                    @OA\Schema(@OA\Property(property = "likeCount", type = "integer", description = "点赞数")),
 *                    @OA\Schema(@OA\Property(property = "createdAt", type = "string", description = "创建时间")),
 *                    @OA\Schema(@OA\Property(property = "updatedAt", type = "string", description = "更新时间")),
 *                    @OA\Schema(@OA\Property(property = "isApproved", type = "integer", description = "是否已审核(0审核中，1正常)", enum = {0, 1})),
 *                    @OA\Schema(@OA\Property(property = "canApprove", type = "boolean", description = "是否可审核")),
 *                    @OA\Schema(@OA\Property(property = "canDelete", type = "boolean", description = "是否可删除")),
 *                    @OA\Schema(@OA\Property(property = "canHide", type = "boolean", description = "是否可删除")),
 *                    @OA\Schema(@OA\Property(property = "contentAttachIds", type = "array", description = "内容附件id", @OA\Items())),
 *                    @OA\Schema(@OA\Property(property = "parseContentHtml", type = "string", description = "评论内容-html")),
 *                    @OA\Schema(@OA\Property(property = "ip", type = "string", description = "ip地址")),
 *                    @OA\Schema(@OA\Property(property = "port", type = "integer", description = "端口")),
 *                    @OA\Schema(@OA\Property(property = "isDeleted", type = "boolean", description = "是否已删除")),
 *                    @OA\Schema(@OA\Property(property = "isFirst", type = "boolean", description = "是否首个回复")),
 *                    @OA\Schema(@OA\Property(property = "isComment", type = "boolean", description = "是否是回复回帖的内容")),
 *                    @OA\Schema(@OA\Property(property = "isLiked", type = "boolean", description = "是否已点赞")),
 *                    @OA\Schema(@OA\Property(property = "user", type = "object", description = "评论作者信息", allOf = {@OA\Schema(ref = "#/components/schemas/user_detail_output")})),
 *                    @OA\Schema(@OA\Property(property = "replyUser", type = "object", description = "回复作者信息", allOf = {@OA\Schema(ref = "#/components/schemas/user_detail_output")})),
 *                    @OA\Schema(@OA\Property(property = "commentUser", type = "object", description = "评论回复作者信息", allOf = {@OA\Schema(ref = "#/components/schemas/user_detail_output")})),
 *                    @OA\Schema(@OA\Property(property = "attachments", type = "array", description = "评论图片信息", @OA\Items(ref = "#/components/schemas/attachment_detail_output")))
 *                }))
 *            }
 *        )
 *     )
 * )
 */
