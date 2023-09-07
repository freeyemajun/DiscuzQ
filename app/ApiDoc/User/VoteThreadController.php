<?php
/**
 * @OA\Post(
 *     path="/api/v3/vote.thread",
 *     summary="投票",
 *     description="投票",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *          required=true,
 *          description = "",
 *          @OA\JsonContent(
 *              @OA\Property(property="threadId",type="integer",description="主题id"),
 *              @OA\Property(property="vote",type="object",description = "",
 *                  @OA\Property(property="id",type="integer",description = "投票id"),
 *                  @OA\Property(property="subitemIds",type="array",description = "投票选项数组[选项id]",@OA\Items(type="integer")),
 *              ),
 *          )
 *     ),
 *     @OA\Response(
 *          response=200,
 *          description="返回更新结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *                  @OA\Schema(@OA\Property(property="Data",type="object",
 *                      @OA\Property(property="value",type="object",description = "key是帖子中的插件id",
 *                          @OA\Property(property="tomId",type="integer",description = "帖子中的插件id"),
 *                          @OA\Property(property="operation",type="string",description = "操作类型(create,delete,update,select)"),
 *                          @OA\Property(property="body",type="object",description = "投票内容",
 *                              @OA\Property(property="voteId",type="integer",description = "投票id"),
 *                              @OA\Property(property="voteTitle",type="integer",description = "投票标题"),
 *                              @OA\Property(property="choiceType",type="integer",enum={1,2},description = "选择类型，1单选/2多选"),
 *                              @OA\Property(property="voteUsers",type="integer",description = "参与人数"),
 *                              @OA\Property(property="expiredAt",type="string", format="datetime",default="2021-01-02 02:22:22",description = "过期时间"),
 *                              @OA\Property(property="isExpired",type="boolean",description = "是否已过期"),
 *                              @OA\Property(property="isVoted",type="boolean",description = "是否已投票结束"),
 *                              @OA\Property(property="subitems",type="array",description="用户列表",
 *                                  @OA\Items(type="object",
 *                                      @OA\Property(property="id",type="integer",description="选项id"),
 *                                      @OA\Property(property="content",type="string",description="选项内容"),
 *                                      @OA\Property(property="voteCount",type="integer", description="选择次数"),
 *                                      @OA\Property(property="isVoted",type="boolean",description="已选择"),
 *                                      @OA\Property(property="voteRate",type="string",default="0%",description="选择占比"),
 *                                  ),
 *                              ),
 *                          ),
 *                      )
 *                  ))
 *          }))
 * )
 */
