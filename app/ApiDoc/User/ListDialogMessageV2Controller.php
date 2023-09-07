<?php
/**
 * @OA\Get(
 *     path = "/api/v3/dialog/message",
 *     summary = "私信消息列表",
 *     description = "私信消息列表(单个对话框内的消息列表)",
 *     tags = {"私信与消息"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_page"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_perPage"),
 *     @OA\Parameter(
 *          name="filter[dialogId]",
 *          in="query",
 *          required=true,
 *          description="私信对话ID",
 *          @OA\Schema(type="integer")
 *      ),
 *     @OA\Response(
 *        response = 200,
 *        description = "私信消息列表",
 *        @OA\JsonContent(allOf = {
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *                    @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *                    @OA\Schema(@OA\Property(property = "pageData", type = "array", @OA\Items(type = "object", allOf = {
 *                          @OA\Schema(ref = "#/components/schemas/dialog_message_detail_output"),
 *                          @OA\Schema(@OA\Property(property = "user", type = "object", description = "发送人用户信息", allOf = {
 *                              @OA\Schema(ref = "#/components/schemas/user_detail_output")
 *                          }))
 *                    })))
 *                }))
 *            }
 *        )
 *     )
 * )
 */
