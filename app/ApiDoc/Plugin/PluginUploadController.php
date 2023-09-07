<?php
/**
 * @OA\Post(
 *     path = "/api/backAdmin/plugin/upload",
 *     summary = "上传插件压缩包",
 *     description = "上传插件压缩包",
 *     tags = {"插件"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *            @OA\Property(property = "file", type = "string", description = "zip文件(注意目录一定要正确，config.json需要放在zip的第一级)")
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
