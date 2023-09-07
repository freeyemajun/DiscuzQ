<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/settings.create",
 *     summary = "设置setting",
 *     description = "设置setting",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "data", type = "array", description = "参数",@OA\Items(
 *               @OA\Property(property = "key", type = "string", description = "设置项key"),
 *               @OA\Property(property = "tag", type = "string", description = "设置项tag"),
 *               @OA\Property(property = "value", type = "string", description = "设置项value"),
 *               ))
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
