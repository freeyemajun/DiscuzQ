<?php
/**
 * @OA\Post(
 *     path="/api/v3/attachment.relation",
 *     summary="写入前端上传文件的参数",
 *     description="写入前端上传文件的参数",
 *     tags={"附件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          description = "",
 *          @OA\JsonContent(
 *              @OA\Property(property="cosUrl",type="string",description="cos链接"),
 *              @OA\Property(property="type",type="integer",description = "附件类型(0帖子附件，1帖子图片，2帖子视频，3帖子音频，4消息图片)",
 *          enum={0,1,2,3,4},),
 *              @OA\Property(property="fileName",type="string",description = "文件原名称，含后缀(不是别名)")
 *          )
 *     ),
 *
 *     @OA\Response(
 *          response=200,
 *          description="返回更新结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *                  @OA\Schema(@OA\Property(property="Data",type="object",
 *                  @OA\Property(property="uuid",type="string",description = ""),
 *                  @OA\Property(property="userId",type="integer",description = "用户ID"),
 *                   @OA\Property(property="type",type="integer",description = "附件类型"),
 *                   @OA\Property(property="isApproved",type="integer",description = "1正常，0待审核"),
 *                   @OA\Property(property="attachment",type="string",description = "别名"),
 *                   @OA\Property(property="filePath",type="string",description = "路径"),
 *                   @OA\Property(property="fileName",type="string",description = "文件原名"),
 *                   @OA\Property(property="fileSize",type="integer",description = "尺寸大小"),
 *                  @OA\Property(property="fileWidth",type="integer",description = "宽"),
 *                  @OA\Property(property="fileHeight",type="integer",description = "高"),
 *                  @OA\Property(property="fileType",type="string",description = "文件类型"),
 *                  @OA\Property(property="isRemote",type="boolean",description = "是否远程附件"),
 *                  @OA\Property(property="ip",type="string",description = ""),
 *                  @OA\Property(property="updatedAt",type="string",description = ""),
 *                  @OA\Property(property="createdAt",type="string",description = ""),
 *                  @OA\Property(property="id",type="integer",description = ""),
 *                  @OA\Property(property="thumbUrl",type="string",description = ""),
 *                  @OA\Property(property="url",type="string",description = "")
 *                  ))
 *              }
 *     ))
 * )
 */
