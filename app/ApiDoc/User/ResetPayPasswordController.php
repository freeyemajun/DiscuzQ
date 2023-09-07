<?php
/**
 * @OA\Post(
 *     path = "/api/v3/users/pay-password/reset",
 *     summary = "获取修改支付密码凭证（验证token）",
 *     description = "获取修改支付密码凭证（验证token）",
 *     tags = {"支付钱包"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *           @OA\Property(property = "payPassword", type = "string", description = "原支付密码")
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "",
 *        @OA\JsonContent(allOf = {
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *              @OA\Schema(@OA\Property(property = "sessionId", type = "string", description = "凭证（验证token）"))
 *          }))
 *       })
 *     )
 * )
 */
