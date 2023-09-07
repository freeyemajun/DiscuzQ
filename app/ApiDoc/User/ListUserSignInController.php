<?php
/**
 * @OA\Get (
 *     path="/api/v3/user/signinfields.list",
 *     summary="查询扩展字段列表（用户注册后显示）",
 *     description="查询扩展字段列表（用户注册后显示）",
 *     tags={"注册登录"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token_true"),
 *     @OA\Response(
 *        response=200,
 *        description="返回字段",
 *        @OA\JsonContent(allOf={
 *           @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property="Data",type="object",
 *              @OA\Property(property = "id", type = "string", description = "数据id"),
 *              @OA\Property(property = "name", type = "string", description = "字段名称"),
 *              @OA\Property(property = "type", type = "integer", description = "字段类型"),
 *              @OA\Property(property = "fieldsExt", type = "string", description = "单选或复选类型的字段选项"),
 *              @OA\Property(property = "fieldsDesc", type = "string", description = "字段介绍"),
 *              @OA\Property(property = "sort", type = "integer", description = "字段排序"),
 *              @OA\Property(property = "status", type = "integer", description = "是否启用;0:不启用 1:启用"),
 *              @OA\Property(property = "required", type = "integer", description = "是否必填;0:不必填 1:必填"),
 *           ))
 *       })
 *     )
 * )
 */
