<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/plugin/operate",
 *     summary = "插件操作",
 *     description = "插件操作",
 *     tags = {"插件"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "appId", type = "string", description = "插件appid"),
 *            @OA\Property(property = "operate", type = "integer", description = "操作类型：1发布2下线3删除")
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "返回信息",
 *        @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout")
 *       })
 *     )
 * )
 */
