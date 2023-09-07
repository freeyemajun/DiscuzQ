<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/users/examine",
 *     summary = "审核用户",
 *     description = "审核用户",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "data", type = "array", description = "参数",@OA\Items(
 *               @OA\Property(property = "id", type = "integer", description = "用户id"),
 *               @OA\Property(property = "status", type = "integer", description = "审核状态 0正常,1禁用,2审核中,3审核拒绝,4审核忽略"),
 *               @OA\Property(property = "rejectReason", type = "string", description = "拒绝理由")
 *               ))
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "返回信息",
 *        @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(@OA\Property(property = "Data", type = "array",@OA\Items(
 *               @OA\Property(property = "id", type = "integer", description = "用户id"),
 *               @OA\Property(property = "status", type = "integer", description = "审核状态 0正常,1禁用,2审核中,3审核拒绝,4审核忽略"),
 *               @OA\Property(property = "rejectReason", type = "string", description = "拒绝理由")
 *               )))
 *        })
 *     )
 * )
 */
