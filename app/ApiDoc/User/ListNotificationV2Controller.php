<?php
/**
 * @OA\Get(
 *     path = "/api/v3/notification",
 *     summary = "用户消息列表",
 *     description = "获取用户消息列表",
 *     tags = {"私信与消息"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Parameter(ref = "#/components/parameters/notification_type_detail"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_page"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_perPage"),
 *     @OA\Response(
 *        response = 200,
 *        description = "获取用户消息列表",
 *        @OA\JsonContent(allOf = {
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *                    @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *                    @OA\Schema(ref = "#/components/schemas/notification_item")
 *                }))
 *            }
 *        )
 *     )
 * )
 */
