<?php
/**
 * @OA\Post(
 *     path = "/api/v3/notification.delete",
 *     summary = "删除消息",
 *     description = "删除消息",
 *     tags = {"私信与消息"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "消息id",
 *        @OA\JsonContent(@OA\Property(property = "id", type = "string", description = "消息id，多个id用英文逗号隔开"))
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "",
 *        @OA\JsonContent(allOf = {
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property = "Data", type = "object"))
 *       })
 *     )
 * )
 */
