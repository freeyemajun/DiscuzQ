<?php
/**
 * @OA\Post(
 *     path="/api/v3/thread.delete",
 *     summary="删除主题",
 *     description="软删除主题",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "待删除的主题id",
 *         @OA\JsonContent(
 *            @OA\Property(property="threadId",type="integer",description="主题id"),
 *         )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回帖子详情",
 *        @OA\JsonContent(ref="#/components/schemas/dzq_layout")
 *     )
 * )
 */
