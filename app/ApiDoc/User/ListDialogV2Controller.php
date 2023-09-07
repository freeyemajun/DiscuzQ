<?php
/**
 * @OA\Get(
 *     path = "/api/v3/dialog",
 *     summary = "我的私信-列表",
 *     description = "我的私信-列表",
 *     tags = {"私信与消息"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_page"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_perPage"),
 *     @OA\Response(
 *        response = 200,
 *        description = "我的私信-列表",
 *        @OA\JsonContent(allOf = {
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *                    @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *                    @OA\Schema(@OA\Property(property = "pageData", type = "array", @OA\Items(type = "object", allOf = {
 *                          @OA\Schema(@OA\Property(property = "id", type = "integer", description = "私信对话id")),
 *                          @OA\Schema(@OA\Property(property = "dialogMessageId", type = "integer", description = "最新消息id")),
 *                          @OA\Schema(@OA\Property(property = "senderUserId", type = "integer", description = "发送人id")),
 *                          @OA\Schema(@OA\Property(property = "recipientUserId", type = "integer", description = "接收人id")),
 *                          @OA\Schema(@OA\Property(property = "senderReadAt", type = "string", description = "发送人已读消息时间")),
 *                          @OA\Schema(@OA\Property(property = "recipientReadAt", type = "string", description = "接收人已读消息时间")),
 *                          @OA\Schema(@OA\Property(property = "updatedAt", type = "string", description = "更新时间")),
 *                          @OA\Schema(@OA\Property(property = "createdAt", type = "string", description = "发送时间")),
 *                          @OA\Schema(@OA\Property(property = "sender", type = "object", description = "发送人用户信息", allOf = {
 *                              @OA\Schema(ref = "#/components/schemas/user_detail_output")
 *                          })),
 *                          @OA\Schema(@OA\Property(property = "recipient", type = "object", description = "接收人用户信息", allOf = {
 *                              @OA\Schema(ref = "#/components/schemas/user_detail_output")
 *                          })),
 *                          @OA\Schema(@OA\Property(property = "dialogMessage", type = "object", description = "最新消息内容", allOf = {
 *                              @OA\Schema(ref = "#/components/schemas/dialog_message_detail_output")
 *                          }))
 *                    })))
 *                }))
 *            }
 *        )
 *     )
 * )
 */
