<?php
/**
 * @OA\Post(
 *     path = "/api/v3/users/real",
 *     summary = "用户实名认证",
 *     description = "用户实名认证",
 *     tags = {"个人中心"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "实名信息",
 *        @OA\JsonContent(
 *           @OA\Property(property = "realName", type = "string", description = "姓名"),
 *           @OA\Property(property = "identity", type = "string", description = "身份证号")
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "返回用户信息",
 *        @OA\JsonContent(allOf = {
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *              @OA\Schema(ref = "#/components/schemas/user_detail_output")
 *          }))
 *       })
 *     )
 * )
 */
