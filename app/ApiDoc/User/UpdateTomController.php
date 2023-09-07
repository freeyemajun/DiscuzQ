<?php
/**
 * @OA\Post(
 *     path="/api/v3/tom.update",
 *     summary="更新主题内扩展对象详情",
 *     description="主题内扩展对象详情，如图片、视频、投票等【官方暂时未使用该接口】",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "更新扩展插件对象数据",
 *         @OA\JsonContent(
 *             @OA\Property(property="threadId",type="integer",description="主题id"),
 *             @OA\Property(property="content",type="object",description="扩展对象集合",ref="#/components/schemas/thread_indexes_input"),
 *         )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回帖子详情",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(@OA\Property(property = "Data",type = "object",
 *            @OA\Property(property="content",type="object",description="扩展对象输出集合",ref="#/components/schemas/thread_indexes_output")))
 *        })
 *     )
 * )
 */
