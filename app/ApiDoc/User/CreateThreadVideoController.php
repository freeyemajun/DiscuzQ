<?php
/**
 * @OA\Post(
 *     path="/api/v3/thread/video",
 *     summary="写入视频信息",
 *     description="云点播上传完视频，需要提交视频和帖子的关联关系等数据",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "视频写入",
 *         @OA\JsonContent(
 *             @OA\Property(property="fileId",type="string",description="云点播返回的fileId"),
 *             @OA\Property(property="fileName",type="string",description="视频/音频文件名称"),
 *             @OA\Property(property="type",type="integer",enum={0,1},default=0, description="类型：0 视频（默认）1 音频时必传"),
 *             @OA\Property(property="mediaUrl",type="string",description="云点播返回的视频或音频url"),
 *         )
 *     ),
 *     @OA\Response(response=200,description="返回音视频详情",@OA\JsonContent(allOf={
 *         @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *         @OA\Schema(@OA\Property(property="Data",type="object",
 *             @OA\Property(property="coverUrl",type="string",description="封面地址"),
 *             @OA\Property(property="createdAt",type="string",description="创建时间"),
 *             @OA\Property(property="fileId",type="string",description="云点播返回的文件id"),
 *             @OA\Property(property="fileName",type="string",description="原始文件名"),
 *             @OA\Property(property="id",type="integer",description="dzq对应的thread_video表的id"),
 *             @OA\Property(property="mediaUrl",type="string",description="云点播音视频地址"),
 *             @OA\Property(property="postId",type="integer",description="正文id"),
 *             @OA\Property(property="status",type="integer",enum={0,1,2}, description="0:转码中 1:转码完成 2:转码失败"),
 *             @OA\Property(property="threadId",type="integer",description="主题id"),
 *             @OA\Property(property="type",type="integer",enum={0,1},description="0:视频 1:音频"),
 *             @OA\Property(property="updatedAt",type="string",format="datetime", description="更新时间"),
 *             @OA\Property(property="userId",type="integer",description="视频发布人的用户id")
 *        ))})
 *     )
 * )
 */
