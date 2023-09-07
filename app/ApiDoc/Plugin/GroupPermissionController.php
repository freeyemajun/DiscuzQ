<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/plugin/permission.switch",
 *     summary="插件权限控制",
 *     description="如果插件关联用户组权限，需要配置用户组下的插件权限开启或关闭",
 *     tags={"插件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="groupId",type="integer",description="用户组id"),
 *             @OA\Property(property="permissions",type="array",description="插件权限开关",@OA\Items(
 *                 @OA\Property(property="appId",type="string",description="插件id"),
 *                 @OA\Property(property="status",type="integer",enum={0,1}, description="0：关闭 1：开启")
 *             )),
 *         )
 *     ),
 *     @OA\Response(response=200,description="返回插件操作结果",@OA\JsonContent(allOf={
 *         @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *         @OA\Schema(@OA\Property(property = "Data", type = "object",
 *             @OA\Property(property="groupId",type="integer",description="用户组id"),
 *             @OA\Property(property="permissions",type="array",description="插件权限开关",@OA\Items(
 *                 @OA\Property(property="appId",type="string",description="插件id"),
 *                 @OA\Property(property="status",type="integer",enum={0,1}, description="0：关闭 1：开启")
 *             )),
 *         ))
 *     }))
 * )
 */
