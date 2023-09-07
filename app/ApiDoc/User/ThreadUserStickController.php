<?php
/**
 *
 * @OA\Post(
 *     path="/api/v3/user/thread.stick",
 *     summary="个人中心帖子置顶",
 *     description="个人中心帖子置顶",
 *     tags={"个人中心"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "",
 *         @OA\JsonContent(
 *             @OA\Property(property="threadId",type="integer",description="主题id"),
 *             @OA\Property(property="status",type="integer",enum={0,1},description="状态,1置顶0取消置顶"),
 *         )
 *     ),
 *      @OA\Response(
 *         response=200,
 *         description="置顶提示",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property="Data", type="object",
 *                     @OA\Property(property="threadId", type="number",description="帖子id"),
 *                     @OA\Property(property="status",type="integer",enum={0,1},description="状态,1置顶0取消置顶"),
 *             ))
 *         })
 *     )
 * )
 *
 */
