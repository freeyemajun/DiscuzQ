<?php
/**
 * @OA\Post(
 *     path = "/api/v3/dialog/message.create",
 *     summary = "创建对话消息",
 *     description = "创建对话消息",
 *     tags = {"私信与消息"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *        required = true,
 *        description = "对话消息",
 *        @OA\JsonContent(
 *           @OA\Property(property = "dialogId", type = "integer", description = "对话id"),
 *           @OA\Property(property = "messageText", type = "string", description = "消息内容"),
 *           @OA\Property(property = "imageUrl", type = "string", description = "消息图片链接"),
 *           @OA\Property(property = "isImage", type = "boolean", description = "是否为图片消息")
 *        )
 *     ),
 *     @OA\Response(
 *        response = 200,
 *        description = "返回私信消息id",
 *        @OA\JsonContent(allOf = {
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *              @OA\Schema(@OA\Property(property = "dialogMessageId", type = "integer", description = "消息id"))
 *          }))
 *       })
 *     )
 * )
 */
