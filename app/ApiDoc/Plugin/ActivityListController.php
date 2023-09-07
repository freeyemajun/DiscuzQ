<?php
/**
 * @OA\Get(
 *     path="/plugin/activity/api/register/list",
 *     summary="分页获取参与人列表",
 *     description="获取报名用户列表",
 *     tags={"插件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *         name="perPage",
 *         in="query",
 *         required=true,
 *         description = "每页显示数据条数",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=true,
 *         description = "当前页",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="activityId",
 *         in="query",
 *         required=true,
 *         description = "活动id",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="返回取消报名结果",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property = "Data", type = "object",allOf={
 *                 @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *                 @OA\Schema(@OA\Property(property="pageData",type="array",description="用户列表",@OA\Items(
 *                     @OA\Property(property="userId",type="integer",description="用户id"),
 *                     @OA\Property(property="avatar",type="string",description="用户头像"),
 *                     @OA\Property(property="nickname",type="string",description="用户昵称")
 *                 )))
 *             }))
 *         }))
 *     )
 * )
 */
