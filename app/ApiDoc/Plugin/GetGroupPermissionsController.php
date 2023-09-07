<?php
/**
 * @OA\Get(
 *     path="/api/backAdmin/plugin/permissionlist",
 *     summary="插件权限状态",
 *     description="获取所有插件权限设置",
 *     tags={"插件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *         name="groupId",
 *         in="query",
 *         required=true,
 *         description = "用户组id",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="返回取消报名结果",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property = "Data", type = "array",@OA\Items(
 *                 @OA\Property(property="appId",type="string",description="插件应用id"),
 *                 @OA\Property(property="authority",type="object",description="权限字段",
 *                     @OA\Property(property="title",type="string",description="权限描述"),
 *                     @OA\Property(property="permission",type="string",description="权限字段名称"),
 *                     @OA\Property(property="canUsePlugin",type="boolean",description="是否有使用该插件权限"),
 *                 ),
 *                 @OA\Property(property="name",type="string",description="插件中文名称"),
 *                 @OA\Property(property="description",type="string",description="描述"),
 *             )))
 *         }))
 *     )
 * )
 */
