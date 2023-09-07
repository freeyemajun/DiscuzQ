<?php
/**
 *@OA\Post(
 *    path = "/api/v3/posts.create",
 *    summary = "发表评论",
 *    description = "Discuz! Q 发表评论",
 *    tags ={"发布与展示"},
 *    @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        description = "发表评论",
 *        required=true,
 *        @OA\JsonContent(
 *           @OA\Property(property="attachments",type="array",description="附件", @OA\Items(type="string")),
 *           @OA\Property(property="content",type="string",description="评论内容"),
 *           @OA\Property(property="id",type="integer",description="帖子id"),
 *           @OA\Property(property="isComment", type="boolean", description="是否是评论"),
 *           @OA\Property(property="replyId", type="integer", description="评论id"),
 *           @OA\property(property="captchaTicket",type="string",description="开启腾讯云验证码服务时，前端回调函数返回的用户验证票据(非必需)"),
 *           @OA\property(property="captchaRandStr",type="string",description="开启腾讯云验证码服务时，前端回调函数返回的随机字符串(非必需)")
 *        ),
 *     ),
 *
 * @OA\Response(
 *        response = 200,
 *        description = "",
 *        @OA\JsonContent(allOf ={
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(@OA\Property(property="Data",type="object", ref="#/components/schemas/dzq_post_detail"))
 *            }
 *        )
 *    )
 *)
 *
 *
 *
 *
 */

