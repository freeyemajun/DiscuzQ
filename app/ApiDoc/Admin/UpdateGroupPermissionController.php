<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/permission.update",
 *     summary="权限管理",
 *     description="权限修改",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         description = "入参",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\property(property="groupId",type="integer",description="用户组ID"),
 *             @OA\property(property="permissions",type="array",@OA\Items(type = "string")),
 *         )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="出参",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *        })
 *     )
 * )
 */
