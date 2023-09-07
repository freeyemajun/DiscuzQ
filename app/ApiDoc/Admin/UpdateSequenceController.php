<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/sequence.update",
 *     summary="修改智能排序接口",
 *     description="修改智能排序接口",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "",
 *         @OA\JsonContent(
 *              @OA\Property(property="siteOpenSort",type="integer",description="是否默认首页1是0否",),
 *              @OA\Property(property="categoryIds",type="string",description="内容分类id"),
 *              @OA\Property(property="groupIds",type="string",description="用户角色ID"),
 *              @OA\Property(property="userIds",type="string",description="用户ID"),
 *              @OA\Property(property="topicIds",type="string",description="话题ID"),
 *              @OA\Property(property="threadIds",type="string",description="主题ID/帖子"),
 *              @OA\Property(property="blockUserIds",type="string",description="阻止显示的用户ID"),
 *              @OA\Property(property="blockTopicIds",type="string",description="阻止显示的话题ID"),
 *              @OA\Property(property="blockThreadIds",type="string",description="阻止显示的主题ID/帖子"),
 *         )
 *     ),
 *     @OA\Response(
 *          response=200,
 *          description="返回更新结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *          }))
 * )
 */
