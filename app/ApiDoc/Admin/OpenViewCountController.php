<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/open.view.count",
 *     summary = "接口层日志",
 *     description = "接口层日志",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "返回token信息",
 *        @OA\JsonContent(
 *            @OA\Property(property = "openViewCount", type = "string", description = "openViewCount"),
 *        )
 *     ),
 *     @OA\Response(response=200,description="接口层日志",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property="Data",type="object",
 *                 @OA\Property(property="key",type="string", description = "key"),
 *                 @OA\Property(property="value",type="integer", description = "value"),
 *                 @OA\Property(property="tag",type="string", description = "tag"),
 *             ))
 *         })
 *     )
 * )
 */
