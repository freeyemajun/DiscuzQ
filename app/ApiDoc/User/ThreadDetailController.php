<?php
/**
 * @OA\Get(
 *     path="/api/v3/thread.detail",
 *     summary="主题详情",
 *     description="主题详情",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *         name="threadId",
 *         in="query",
 *         required=true,
 *         description = "主题id",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="返回主题详情数据",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property="Data",type="object", ref="#/components/schemas/dzq_thread"))
 *         })
 *     )
 * )
 */
