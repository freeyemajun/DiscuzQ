<?php
/**
 *@OA\Post(
 *    path = "/api/v3/posts.update",
 *    summary = "点赞/采纳评论",
 *    description = "Discuz! Q 点赞/采纳评论",
 *    tags ={"发布与展示"},
 *    @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        description = "点赞/采纳评论",
 *        required=true,
 *        @OA\JsonContent(
 *           @OA\Property(property="postId",type="integer",description="评论id"),
 *           @OA\Property(property="data",type="object",
 *              @OA\Property(property="attributes", type="object",
 *                  @OA\Property(property="isLiked", type="boolean", description="是否点赞")
 *              )
 *          ),
 *        ),
 *     ),
 *
 * @OA\Response(
 *        response = 200,
 *        description = "",
 *        @OA\JsonContent(allOf ={
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(title="已更新评论详情", description="返回已更新评论详情",@OA\Property(property="Data",type="object",
 *                      @OA\Property(property="canLike", type="boolean", description="能否点赞"),
 *                      @OA\Property(property="canFavorite", type="boolean", description="能否采纳"),
 *                      @OA\Property(property="isApproved", type="integer", description="是否通过"),
 *                      @OA\Property(property="isFavorite", type="boolean", description="是否采纳"),
 *                      @OA\Property(property="isFirst", type="boolean", description="是否是帖子内容"),
 *                      @OA\Property(property="isLiked", type="boolean", description="是否点赞"),
 *                      @OA\Property(property="likeCount", type="integer", description="点赞数量"),
 *                      @OA\Property(property="postId", type="integer", description="评论id"),
 *                      @OA\Property(property="likePayCount", type="integer", description="点赞数量+采纳数量+付费数量"),
 *                      @OA\Property(property="redPacketAmount", type="number", description="红包金额"),
 *                      @OA\Property(property="replyCount", type="integer", description="回复数量"),
 *                      @OA\Property(property="rewards", type="number", description="采纳金额"),
 *                      @OA\Property(property="threadId", type="integer", description="帖子id")))
 *            }
 *        )
 *    )
 *)
 *
 *
 *
 *
 *
 */

