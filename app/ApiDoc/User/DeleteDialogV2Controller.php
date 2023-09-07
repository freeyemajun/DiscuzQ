<?php
/**
 * @OA\Post(
 *     path = "/api/v3/dialog.delete",
 *     summary = "删除对话框",
 *     description = "删除对话框",
 *     tags = {"私信与消息"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *           @OA\Property(property = "id", type = "integer", description = "对话id")
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
