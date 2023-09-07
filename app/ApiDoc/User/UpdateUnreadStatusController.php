<?php
/**
 * @OA\Post(
 *     path = "/api/v3/dialog.update",
 *     summary = "更新对话私信已读状态",
 *     description = "更新对话私信已读状态",
 *     tags = {"私信与消息"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "",
 *        @OA\JsonContent(
 *           @OA\Property(property = "dialogId", type = "integer", description = "对话id")
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
