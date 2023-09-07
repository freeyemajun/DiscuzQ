<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/user/signinfields",
 *     summary = "获取用户注册扩展信息",
 *     description = "获取用户注册扩展信息",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *        name = "userId",
 *        in = "query",
 *        required = true,
 *        description = "用户id",
 *        @OA\Schema(type = "integer")
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "",
 *        @OA\JsonContent(allOf = {
 *            @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *            @OA\Schema(@OA\Property(property = "Data", type = "array", @OA\Items(type = "object", allOf = {
 *                @OA\Schema(@OA\Property(property = "id", type = "integer", description = "id")),
 *                @OA\Schema(@OA\Property(property = "userId", type = "integer", description = "用户id")),
 *                @OA\Schema(@OA\Property(property = "name", type = "string", description = "扩展信息字段名称")),
 *                @OA\Schema(@OA\Property(property = "type", type = "integer", description = "0:单行文本框 1:多行文本框 2:单选 3:复选 4:图片上传 5:附件上传")),
 *                @OA\Schema(@OA\Property(property = "fieldsExt", type = "string", description = "字段扩展信息，Json表示选项内容")),
 *                @OA\Schema(@OA\Property(property = "fieldsDesc", type = "string", description = "字段介绍")),
 *                @OA\Schema(@OA\Property(property = "remark", type = "string", description = "审核意见")),
 *                @OA\Schema(@OA\Property(property = "sort", type = "integer", description = "自定义显示顺序")),
 *                @OA\Schema(@OA\Property(property = "status", type = "integer", description = "0:废弃 1:待审核 2:驳回 3:审核通过")),
 *                @OA\Schema(@OA\Property(property = "required", type = "integer", description = "是否必填项 0:否 1:是")),
 *                @OA\Schema(@OA\Property(property = "createdAt", type = "string", description = "创建时间")),
 *                @OA\Schema(@OA\Property(property = "updatedAt", type = "string", description = "更新时间"))
 *             })))
 *            }
 *        )
 *     )
 * )
 */