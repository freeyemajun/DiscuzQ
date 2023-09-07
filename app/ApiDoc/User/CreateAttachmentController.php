<?php
/**
 * @OA\Post(
 *     path="/api/v3/attachments.create",
 *     summary="附件上传",
 *     description="主题详情",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "文件上传附加信息",
 *         @OA\JsonContent(
 *            @OA\Property(property="file",type="string",description="文件"),
 *            @OA\Property(property="name",type="string",description="文件名"),
 *            @OA\Property(property="type",type="integer",description="附件类型	0帖子附件，1帖子图片，2帖子视频，3帖子音频，4消息图片5回答图片"),
 *            @OA\Property(property="dialogMessageId",type="integer",description="对话id"),
 *            @OA\Property(property="order",type="string",description="关联订单号"),
 *            @OA\Property(property="mediaId",type="string",description="微信内上传微信接口返回的mediaId"),
 *            @OA\Property(property="fileUrl",type="string",description="以url形式上传")
 *         )
 *     ),
 *     @OA\Response(response=200,description="返回附件详情",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property="Data",type="object",
 *                 @OA\Property(property="attachment",type="string",description="唯一文件名"),
 *                 @OA\Property(property="extension",type="string",description="文件后缀"),
 *                 @OA\Property(property="fileHeight",type="string",description="文件[图片]高度"),
 *                 @OA\Property(property="fileName",type="string",description="原始文件名"),
 *                 @OA\Property(property="filePath",type="string",description="存储路径"),
 *                 @OA\Property(property="fileSize",type="string",description="文件大小"),
 *                 @OA\Property(property="fileType",type="string",description="文件类型"),
 *                 @OA\Property(property="fileWidth",type="string",description="文件[图片]宽度"),
 *                 @OA\Property(property="id",type="string",description="附件id"),
 *                 @OA\Property(property="isApproved",type="string",description="通过审核"),
 *                 @OA\Property(property="isRemote",type="string",description="是否存储在cos"),
 *                 @OA\Property(property="order",type="string",description="关联订单号"),
 *                 @OA\Property(property="thumbUrl",type="string",description="缩略图地址"),
 *                 @OA\Property(property="type",type="integer",enum={0,1,2,3,4,5}, description="附件类型	0帖子附件，1帖子图片，2帖子视频，3帖子音频，4消息图片5回答图片"),
 *                 @OA\Property(property="typeId",type="string",description="类型对应原表的id"),
 *                 @OA\Property(property="url",type="string",description="文件地址"),
 *             )),
 *         })
 *     )
 * )
 */
