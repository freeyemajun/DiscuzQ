<?php
/**
 * @OA\Post(
 *     path = "/api/v3/dialog.create",
 *     summary = "创建对话框",
 *     description = "创建对话框（与某用户首次对话）",
 *     tags = {"私信与消息"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "对话信息",
 *        @OA\JsonContent(
 *           @OA\Property(property = "recipientUserId", type = "string", description = "接收人用户id"),
 *           @OA\Property(property = "messageText", type = "string", description = "消息内容"),
 *           @OA\Property(property = "imageUrl", type = "string", description = "消息图片链接"),
 *           @OA\Property(property = "attachmentId", type = "integer", description = "附件id"),
 *           @OA\Property(property = "isImage", type = "boolean", description = "是否为图片消息")
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "返回对话id",
 *        @OA\JsonContent(allOf = {
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *              @OA\Schema(@OA\Property(property = "dialogId", type = "integer", description = "对话id")),
 *              @OA\Schema(@OA\Property(property = "dialogMessageId", type = "integer", description = "消息id"))
 *          }))
 *       })
 *     )
 * )
 */
