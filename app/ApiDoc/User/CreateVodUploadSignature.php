<?php
/**
 * @OA\Get(
 *     path="/api/v3/signature",
 *     summary="云点播回调",
 *     description="云点播回调",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *         name="data",
 *         in="query",
 *         required=true,
 *         description = "回调数据",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="云点播回调返回",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout")
 *         })
 *     )
 * )
 */
