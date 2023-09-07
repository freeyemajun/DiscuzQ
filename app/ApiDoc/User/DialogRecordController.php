<?php
/**
 * @OA\Get(
 *     path = "/api/v3/dialog.record",
 *     summary = "获取与某一用户的对话框",
 *     description = "获取与某一用户的对话框",
 *     tags = {"私信与消息"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *        name = "userId",
 *        in = "query",
 *        required = true,
 *        description = "用户id",
 *        @OA\Schema(type = "string")
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "返回对话框id，若dialogId为空，说明此前未曾与该用户私信",
 *        @OA\JsonContent(allOf = {
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *                    @OA\Schema(@OA\Property(property = "dialogId", type = "integer", description = "对话id"))
 *                }))
 *            }
 *        )
 *     )
 * )
 */
