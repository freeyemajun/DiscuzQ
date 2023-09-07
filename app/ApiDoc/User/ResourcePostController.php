<?php
/**
 * @OA\Get(
 *     path="/api/v3/posts.detail",
 *     summary="评论详情",
 *     description="评论详情",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *        name="postId",
 *        in="query",
 *        required=true,
 *        description = "评论id",
 *        @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="订单详情",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(title="评论详情", description="评论详情",@OA\Property(property="Data",type="object", allOf={
                    @OA\Schema( ref="#/components/schemas/dzq_post_detail"),
 *                  @OA\Schema( title="评论详情附加项", description="评论详情附加项",
 *                      @OA\Property(property="commentPosts", type="array", @OA\Items(
                            type="object", ref="#/components/schemas/dzq_post_detail")
 *                      ),
 *                      @OA\Property(property="likeUsers", type="array", @OA\Items(
                            type="object",
 *                          @OA\Property(property="id", type="integer", description="点赞用户id"),
 *                          @OA\Property(property="nickname", type="string", description="点赞用户昵称"),
 *                          @OA\Property(property="avatar", type="string", description="点赞用户头像"))
 *                      )
 *                  )
 *            }))
 *        })
 *     )
 * )
 *
 *
 */
