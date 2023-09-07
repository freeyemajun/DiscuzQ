<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/reports/delete",
 *     summary = "批量删除举报",
 *     description = "批量删除举报",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "ids", type = "string", description = "举报id，多个请用英文逗号间隔")
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
