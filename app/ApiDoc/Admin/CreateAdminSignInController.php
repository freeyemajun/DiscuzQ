<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/signinfields.create",
 *     summary="注册与登录",
 *     description="编辑注冊扩展列表",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *        required=true,
 *        description = "请求参数",
 *        @OA\JsonContent(
 *           @OA\Property(property="fieldsDesc",type="string",description="字段介绍"),
 *           @OA\Property(property="fieldsExt",type="string",description="单选或复选类型的字段选项"),
 *           @OA\Property(property="id",type="string",description="数据id"),
 *           @OA\Property(property="name",type="string",description="字段名称"),
 *           @OA\Property(property="required",type="string",description="是否必填;0:不必填 1:必填"),
 *           @OA\Property(property="sort",type="integer",description="字段排序"),
 *           @OA\Property(property="status",type="integer",description="是否启用;0:不启用 1:启用"),
 *           @OA\Property(property="type",type="integer",description="字段类型"),
 *        )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回数据",
 *        @OA\JsonContent(allOf={
 *           @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property="Data",type="object",
 *              @OA\Property(property = "userId", type = "integer", description = "用户id"),
 *              @OA\Property(property = "name", type = "string", description = "字段名称"),
 *              @OA\Property(property = "type", type = "integer", description = "字段类型"),
 *              @OA\Property(property = "fieldsExt", type = "string", description = "单选或复选类型的字段选项"),
 *              @OA\Property(property = "fieldsDesc", type = "string", description = "字段介绍"),
 *              @OA\Property(property = "sort", type = "integer", description = "字段排序"),
 *              @OA\Property(property = "status", type = "integer", description = "是否启用;0:不启用 1:启用"),
 *              @OA\Property(property = "required", type = "integer", description = "是否必填;0:不必填 1:必填"),
 *              @OA\Property(property = "updatedAt", type = "string", description = "更新时间"),
 *              @OA\Property(property = "createdAt", type = "string", description = "创建时间"),
 *              @OA\Property(property = "id", type = "integer", description = "数据id")
 *           ))
 *       })
 *     )
 * )
 */
