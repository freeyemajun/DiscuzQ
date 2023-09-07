<?php
/**
 * @OA\Post(
 *     path="/plugin/activity/api/register/cancel",
 *     summary="取消报名活动",
 *     description="取消报名活动",
 *     tags={"插件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="activityId",type="integer",default=666, description="活动id"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="返回取消报名结果",
 *         @OA\JsonContent(ref="#/components/schemas/dzq_layout")
 *     )
 * )
 */
