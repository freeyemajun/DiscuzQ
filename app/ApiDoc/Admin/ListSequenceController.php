<?php
/**
 *
 * @OA\Post(
 *     path="/api/backAdmin/sequence.list",
 *     summary="获取智能排序接口",
 *     description="获取智能排序接口",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *      @OA\Response(
 *         response=200,
 *         description="返回结果",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property = "Data", type = "object",
 *                  @OA\Property(property = "categoryIds", type = "string",description = "类型id列表，以,分割"),
 *                  @OA\Property(property = "groupIds", type = "string",description = "角色id列表，以,分割"),
 *                  @OA\Property(property = "threadIds", type = "string",description = "主题d列表，以,分割"),
 *                  @OA\Property(property = "blockThreadIds", type = "string",description = "阻止主题id列表，以,分割"),
 *                  @OA\Property(property = "userInfo", type = "array",description = "用户列表",@OA\Items(type="object",
 *                      @OA\Property(property = "id", type = "integer",description = "用户id"),
 *                      @OA\Property(property = "username", type = "string",description = "用户昵称"),
 *                  )),
 *                 @OA\Property(property = "blockUserInfo", type = "array",description = "阻止的用户列表",@OA\Items(type="object",
 *                      @OA\Property(property = "id", type = "integer",description = "用户id"),
 *                      @OA\Property(property = "username", type = "string",description = "用户昵称"),
 *                  )),
 *                  @OA\Property(property = "topicInfo", type = "array",description = "主题列表",@OA\Items(type="object",
 *                      @OA\Property(property = "id", type = "integer",description = "主题id"),
 *                      @OA\Property(property = "content", type = "string",description = "主题内容"),
 *                  )),
 *                 @OA\Property(property = "blockTopicInfo", type = "array",description = "阻止主题列表",@OA\Items(type="object",
 *                      @OA\Property(property = "id", type = "integer",description = "主题id"),
 *                      @OA\Property(property = "content", type = "string",description = "主题内容"),
 *                  )),
 *              ))
 *         })
 *     )
 * )
 *
 */

