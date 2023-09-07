<?php
/**
 *@OA\Post(
 *    path = "/api/v3/follow.create",
 *    summary = "关注用户",
 *    description = "Discuz! Q 关注/粉丝列表",
 *    tags ={"个人中心"},
 *    @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *    @OA\RequestBody(
 *        required=true,
 *        description = "关注用户id",
 *        @OA\JsonContent(
 *           @OA\Property(property="toUserId",type="integer",description="关注用户id")
 *        )
 *     ),
 *
 *    @OA\Response(
 *        response = 200,
 *        description = "",
 *        @OA\JsonContent(allOf ={
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout")
 *            }
 *        )
 *    )
 *)
 *
 *
 *
 */

