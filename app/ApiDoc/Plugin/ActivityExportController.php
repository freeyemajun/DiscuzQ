<?php
/**
 * @OA\Get(
 *     path="/plugin/activity/api/register/export",
 *     summary="活动报名人员信息导出",
 *     description="活动报名人员信息导出",
 *     tags={"插件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *         name="activityId",
 *         in="query",
 *         required=true,
 *         description = "活动id",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="excel文件"
 *     )
 * )
 */
