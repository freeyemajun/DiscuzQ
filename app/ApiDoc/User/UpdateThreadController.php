<?php
/**
 * @OA\Post(
 *     path="/api/v3/thread.update",
 *     summary="编辑更新主题",
 *     description="统一更新主题接口",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "帖子原始内容",
 *         @OA\JsonContent(
 *             @OA\Property(property="threadId",type="integer",description="帖子id"),
 *             @OA\Property(property="title",type="string",description="帖子标题"),
 *             @OA\Property(property="categoryId",type="integer",description="分类id"),
 *             @OA\Property(property="price",type="number",description="付费贴价格"),
 *             @OA\Property(property="freeWords",type="number",description="免费字数百分比"),
 *             @OA\Property(property="attachmentPrice",type="number",description="附件价格"),
 *             @OA\Property(property="draft",type="integer",enum={0,1}, description="是否草稿"),
 *             @OA\Property(property="anonymous",type="integer",enum={0,1}, description="是否匿名"),
 *             @OA\Property(property="content",type="object",description="帖子正文",
 *             @OA\Property(property="text",type="string",description="正文内容"),
 *             @OA\Property(property="indexes",type="object",description="帖子插件发帖结构",
 *                 @OA\Property(property = "101", type = "object", description = "图片",ref="#/components/schemas/local_plugin_input"),
 *                 @OA\Property(property = "102", type = "object", description = "语音",ref="#/components/schemas/local_plugin_input"),
 *                 @OA\Property(property = "103", type = "string", description = "视频",ref="#/components/schemas/local_plugin_input"),
 *                 @OA\Property(property = "104", type = "string", description = "商品",ref="#/components/schemas/local_plugin_input"),
 *                 @OA\Property(property = "106", type = "string", description = "红包",ref="#/components/schemas/local_plugin_input"),
 *                 @OA\Property(property = "107", type = "string", description = "悬赏",ref="#/components/schemas/local_plugin_input"),
 *                 @OA\Property(property = "108", type = "string", description = "文件附件",ref="#/components/schemas/local_plugin_input"),
 *                 @OA\Property(property = "109", type = "string", description = "投票",ref="#/components/schemas/local_plugin_input"),
 *             ),
 *             @OA\property(property="captchaTicket",type="string",description="开启腾讯云验证码服务时，前端回调函数返回的用户验证票据(非必需)"),
 *             @OA\property(property="captchaRandStr",type="string",description="开启腾讯云验证码服务时，前端回调函数返回的随机字符串(非必需)")
 *           )
 *        )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回帖子详情",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(@OA\Property(property="Data",type="object", ref="#/components/schemas/dzq_thread"))
 *        })
 *     )
 * )
 */
