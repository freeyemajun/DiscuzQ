<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/manage.submit.review",
 *     summary = "审核主题/评论",
 *     description = "审核主题/评论",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "data", type = "array", description = "审核内容", @OA\Items(type = "object", allOf = {
 *                @OA\Schema(@OA\Property(property = "id", type = "integer", description = "主题/评论id")),
 *                @OA\Schema(@OA\Property(property = "isDeleted", type = "boolean", description = "删除")),
 *                @OA\Schema(@OA\Property(property = "isApproved", type = "integer", description = "是否通过审核,0不通过,1通过,2忽略", enum = {0, 1, 2})),
 *                @OA\Schema(@OA\Property(property = "message", type = "string", description = "操作理由")),
 *            })),
 *            @OA\Property(property = "type", type = "integer", description = "审核类型,1主题审核,2回复审核", enum = {1, 2})
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "",
 *        @OA\JsonContent(allOf = {
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property = "Data", type = "array", @OA\Items()))
 *       })
 *     )
 * )
 */
