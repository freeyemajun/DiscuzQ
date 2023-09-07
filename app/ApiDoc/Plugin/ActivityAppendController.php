<?php
/**
 * @OA\Post(
 *     path="/plugin/activity/api/register/append",
 *     summary="参加报名活动",
 *     description="参加报名活动",
 *     tags={"插件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="activityId",type="integer",default=666,description="活动id"),
 *             @OA\Property(property="additionalInfo",type="object",description="报名附加项信息",
 *                  @OA\Property(property = "name", type = "string", description = "姓名"),
 *                  @OA\Property(property = "mobile", type = "string", description = "手机号"),
 *                  @OA\Property(property = "weixin", type = "string", description = "微信号"),
 *                  @OA\Property(property = "address", type = "string", description = "地址")
 *              ),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="返回报名结果",
 *         @OA\JsonContent(ref="#/components/schemas/dzq_layout")
 *     )
 * )
 */
