<?php
/**
 *@OA\Get(
 *    path = "/api/v3/follow.list",
 *    summary = "我的关注/粉丝列表",
 *    description = "Discuz! Q 关注/粉丝列表",
 *    tags ={"个人中心"},
 *    @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *    @OA\Parameter(ref = "#/components/parameters/page"),
 *    @OA\Parameter(ref = "#/components/parameters/perPage"),
 *    @OA\Parameter(ref = "#/components/parameters/filter_userId"),
 *    @OA\Parameter(ref = "#/components/parameters/filter_type"),
 *
 *    @OA\Response(
 *        response = 200,
 *        description = "返回关注/粉丝列表",
 *        @OA\JsonContent(allOf ={
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(title="关注/粉丝列表",description="关注/粉丝列表",@OA\Property(property = "Data", type = "object",allOf={
 *                    @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *                    @OA\Schema(title="关注/粉丝列表", description="关注/粉丝列表",
 *                      @OA\Property(property = "pageData", type = "array", @OA\Items(type = "object",
 *                          @OA\Property(property = "group", type = "object",
 *                              @OA\Property(property = "groupIcon", type="string", description = "用户组icon"),
 *                              @OA\Property(property = "groupName", type="string", description = "用户组名称"),
 *                              @OA\Property(property = "groupId", type="integer", description = "用户组id")
 *                          ),
 *                          @OA\Property(property = "user", type = "object",
 *                              @OA\Property(property = "avatar", type="string", description = "用户头像url"),
 *                              @OA\Property(property = "nickname", type="string", description = "用户昵称"),
 *                              @OA\Property(property = "userId", type="integer", description = "用户id")
 *                          ),
 *                          @OA\Property(property = "userFollow", type = "object",
 *                              @OA\Property(property = "createdAt", type="string", description = "创建时间", format = "datetime"),
 *                              @OA\Property(property = "fromUserId", type="integer", description = "关注人id"),
 *                              @OA\Property(property = "id", type="integer", description = "关注id"),
 *                              @OA\Property(property = "isFollow", type="integer", description = "是否关注过别人/被别人关注过"),
 *                              @OA\Property(property = "isMutual", type="integer", description = "是否互关"),
 *                              @OA\Property(property = "toUserId", type="integer", description = "被关注人id"),
 *                              @OA\Property(property = "updatedAt", type="string", format = "datetime", description = "更新时间")
 *                         ))
 *                      )
 *                   )
 *                }))
 *            }
 *        )
 *    )
 *)
 *
 *
 *
 *
 */

