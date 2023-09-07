<?php
/**
 *@OA\Post(
 *    path = "/api/v3/follow.delete",
 *    summary = "删除我的关注/粉丝",
 *    description = "Discuz! Q 删除我的关注/粉丝",
 *    tags ={"个人中心"},
 *    @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *    @OA\RequestBody(
 *        required=true,
 *        description = "取消关注/粉丝",
 *        @OA\JsonContent(
 *           @OA\Property(property="id",type="integer",description="用户id"),
 *           @OA\Property(property="type",type="integer",description="1：取消关注、2：取消粉丝"),
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

