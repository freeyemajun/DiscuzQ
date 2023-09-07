<?php
/**
 * @OA\Post(
 *     path="/api/v3/thread.share",
 *     summary="记录分享数",
 *     description="单个帖子的分享数累计",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(required=true,description = "入参body",@OA\JsonContent(
 *         @OA\Property(property="threadId",type="integer",description="分享的主题id"),
 *     )),
 *     @OA\Response(response=200,description="返回删除结果",@OA\JsonContent(ref="#/components/schemas/dzq_layout"))
 * )
 */
