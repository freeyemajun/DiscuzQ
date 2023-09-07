<?php
/**
 * @OA\Get(
 *     path = "/api/v3/unreadnotification",
 *     summary = "未读消息通知统计",
 *     description = "未读消息通知统计",
 *     tags = {"私信与消息"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Response(
 *        response = 200,
 *        description = "返回未读消息",
 *        @OA\JsonContent(allOf = {
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *                    @OA\Schema(@OA\Property(property = "dialogNotifications", type = "integer", description = "未读私信总数")),
 *                    @OA\Schema(@OA\Property(property = "unreadNotifications", type = "integer", description = "未读消息总数")),
 *                    @OA\Schema(@OA\Property(property = "typeUnreadNotifications", type = "object", description = "各类型未读消息数", allOf = {
 *                        @OA\Schema(@OA\Property(property = "liked", type = "integer", description = "点赞我的")),
 *                        @OA\Schema(@OA\Property(property = "receiveredpacket", type = "integer", description = "收到红包")),
 *                        @OA\Schema(@OA\Property(property = "related", type = "integer", description = "艾特@我的")),
 *                        @OA\Schema(@OA\Property(property = "replied", type = "integer", description = "回复我的")),
 *                        @OA\Schema(@OA\Property(property = "rewarded", type = "integer", description = "打赏通知/人工收入(他人支付付费内容等)")),
 *                        @OA\Schema(@OA\Property(property = "system", type = "integer", description = "系统通知")),
 *                        @OA\Schema(@OA\Property(property = "threadrewarded", type = "integer", description = "悬赏通知")),
 *                        @OA\Schema(@OA\Property(property = "threadrewardedexpired", type = "integer", description = "悬赏过期通知")),
 *                        @OA\Schema(@OA\Property(property = "withdrawal", type = "integer", description = "提现通知"))
 *                      }))
 *                }))
 *            }
 *        )
 *     )
 * )
 */
