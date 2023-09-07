<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/users/update.user",
 *     summary = "修改用户信息",
 *     description = "修改用户信息",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "id", type = "integer", description = "用户id"),
 *            @OA\Property(property = "username", type = "string", description = "用户名"),
 *            @OA\Property(property = "newPassword", type = "string", description = "新密码"),
 *            @OA\Property(property = "mobile", type = "string", description = "手机号码"),
 *            @OA\Property(property = "status", type = "integer", description = "用户状态;0正常 1禁用 2审核中 3审核拒绝 4审核忽略"),
 *            @OA\Property(property = "refuseMessage", type = "string", description = "审核拒绝原因"),
 *            @OA\Property(property = "registerReason", type = "string", description = "注册原因"),
 *            @OA\Property(property = "groupId", type = "integer", description = "所属用户组"),
 *            @OA\Property(property = "expiredAt", type = "string", description = "过期时间"),
 *            @OA\Property(property = "nickname", type = "string", description = "昵称")
 *        )
 *     ),
 *     @OA\Response(response=200,description="返回用户信息",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property="Data",type="object",
 *                 @OA\Property(property="id",type="integer",description="用户id"),
 *                 @OA\Property(property="username",type="string",description="用户名"),
 *                 @OA\Property(property="nickname",type="string",description="昵称"),
 *                 @OA\Property(property="avatar",type="string",description="头像"),
 *                 @OA\Property(property="createdAt",type="string",description="创建时间"),
 *                 @OA\Property(property="expiredAt",type="string",description="过期时间"),
 *                 @OA\Property(property="lastLoginIp",type="string",description="最后登录ip"),
 *                 @OA\Property(property="loginAt",type="string",description="登录时间"),
 *                 @OA\Property(property="mobile",type="string",description="手机号"),
 *                 @OA\Property(property="registerIp",type="string",description="注册ip"),
 *                 @OA\Property(property="status",type="integer",description="状态")
 *             ))
 *         })
 *     )
 * )
 */
