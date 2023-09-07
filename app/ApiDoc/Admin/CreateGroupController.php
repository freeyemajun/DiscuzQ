<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/groups.create",
 *     summary="创建用户组",
 *     description="创建用户组",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "参数",
 *         @OA\JsonContent(
 *            @OA\Property(property="name",type="string",description="名称"),
 *            @OA\Property(property="type",type="string",description="类型"),
 *            @OA\Property(property="default",type="boolean",description="是否是默认组true是false不是"),
 *            @OA\Property(property="isDisplay",type="boolean",description="是否显示在用户名后true是false不是"),
 *            @OA\Property(property="isPaid",type="integer",description="是否是付费组0不是1是"),
 *            @OA\Property(property="fee",type="string",description="付费组 加入费用"),
 *            @OA\Property(property="days",type="integer",description="付费组 有效天数"),
 *            @OA\Property(property="level",type="integer",description="付费组 等级"),
 *            @OA\Property(property="description",type="string",description="特权描述"),
 *            @OA\Property(property="notice",type="string",description="购买须知"),
 *         )
 *     ),
 *     @OA\Response(response=200,description="返回用户组信息",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property="Data",type="object",
 *                 @OA\Property(property="id",type="integer",description="用户组id"),
 *                 @OA\Property(property="name",type="string",description="用户组名称"),
 *                 @OA\Property(property="type",type="string",description="类型"),
 *                 @OA\Property(property="color",type="string",description="颜色"),
 *                 @OA\Property(property="icon",type="string",description="icon"),
 *                 @OA\Property(property="default",type="boolean",description="是否默认"),
 *                 @OA\Property(property="isDisplay",type="boolean",description="是否显示在用户名后"),
 *                 @OA\Property(property="isPaid",type="integer",description="是否收费：0不收费，1收费"),
 *                 @OA\Property(property="fee",type="string",description="收费金额"),
 *                 @OA\Property(property="days",type="string",description="付费获得天数"),
 *                 @OA\Property(property="scale",type="string",description="分成比例"),
 *                 @OA\Property(property="isSubordinate",type="boolean",description="是否可以推广下线"),
 *                 @OA\Property(property="isCommission",type="boolean",description="是否可以收入提成"),
 *                 @OA\Property(property="level",type="integer",description="付费组 等级"),
 *                 @OA\Property(property="description",type="string",description="特权描述"),
 *                 @OA\Property(property="notice",type="string",description="购买须知"),
 *             ))
 *         })
 *     )
 * )
 */
