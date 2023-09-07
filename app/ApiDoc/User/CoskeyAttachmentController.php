<?php

/**
 * @OA\Post(
 *     path="/api/v3/coskey",
 *     summary="上传文件临时参数",
 *     description="上传文件临时参数",
 *     tags={"附件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          description = "",
 *          @OA\JsonContent(
 *              @OA\Property(property="type",type="integer",description = "附件类型(0帖子附件，1帖子图片，2帖子视频，3帖子音频，4消息图片)",
 *          enum={0,1,2,3,4},),
 *              @OA\Property(property="fileName",type="string",description = "原始文件名，含后缀"),
 *              @OA\Property(property="attachment",type="string",description = "加密文件名，含后缀"),
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
 *                  @OA\Property(property="expiredTime",type="integer",description = ""),
 *                  @OA\Property(property="expiration",type="string",description = ""),
 *                   @OA\Property(property="credentials",type="array",description = "",
 *                       @OA\Items(type="object",
 *                            @OA\Property(property="sessionToken", type="string",description=""),
 *                            @OA\Property(property="tmpSecretId",type="string",description=""),
 *                            @OA\Property(property="tmpSecretKey", type="string",description="")
 *                        )),
 *                   @OA\Property(property="requestId",type="string",description = ""),
 *                   @OA\Property(property="startTime",type="integer",description = ""),
 *                   @OA\Property(property="attachment",type="string",description = "文件系统生成的名称,暂由前端自生成，后端不返回"),
 *                   @OA\Property(property="filePath",type="string",description = "cos文件应上传的路径,暂由前端自生成，后端不返回"),
 *                  ))
 *              }
 *     ))
 * )
 */
