<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/reports/batch",
 *     summary = "批量修改举报反馈",
 *     description = "批量修改举报反馈",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "data", type = "array", description = "参数",@OA\Items(
 *               @OA\Property(property = "id", type = "integer", description = "举报记录ID"),
 *               @OA\Property(property = "status", type = "integer", description = "状态"),
 *               ))
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "",
 *        @OA\JsonContent(allOf = {
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property = "Data", type = "string"))
 *       })
 *     )
 * )
 */
