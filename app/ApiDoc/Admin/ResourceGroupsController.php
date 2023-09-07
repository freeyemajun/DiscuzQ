<?php
/**
 * @OA\Get(
 *     path="/api/backAdmin/groups.resource",
 *     summary="用户角色",
 *     description="查询用户组（单条）",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token_true"),
 *     @OA\Parameter(
 *        name="id",
 *        in="query",
 *        required=true,
 *        description = "用户组id",
 *        @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *        name="include",
 *        in="query",
 *        required=false,
 *        description = "包含项permission",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="出参",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(
 *                @OA\Property(property="Data",type="object",
 *                    @OA\Property(property="id",type="number",description="用户组 id"),
 *                    @OA\Property(property="name",type="string",description="用户组名称"),
 *                    @OA\Property(property="type",type="string",description="类型"),
 *                    @OA\Property(property="color",type="string",description="颜色"),
 *                    @OA\Property(property="icon",type="string",description="icon"),
 *                    @OA\Property(property="default",type="boolean",description="是否默认"),
 *                    @OA\Property(property="isDisplay",type="boolean",description="是否显示在用户名后"),
 *                    @OA\Property(property="isPaid",type="number",description="是否收费：0不收费，1收费"),
 *                    @OA\Property(property="fee",type="string",description="收费金额"),
 *                    @OA\Property(property="days",type="number",description="付费获得天数"),
 *                    @OA\Property(property="scale",type="string",description="分成比例"),
 *                    @OA\Property(property="isSubordinate",type="boolean",description="是否可以 推广下线"),
 *                    @OA\Property(property="isCommission",type="boolean",description="是否可以 收入提成"),
 *                    @OA\Property(property = "level", type = "integer",description = "付费组 等级"),
 *                    @OA\Property(property = "description", type = "string",description = "特权描述"),
 *                    @OA\Property(property = "notice", type = "string",description = "购买须知"),
 *                )
 *            )
 *        })
 *     )
 * )
 */
