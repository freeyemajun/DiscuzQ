<?php
/**
* @OA\Post(
*     path="/api/v3/reports",
*     summary="创建举报反馈记录",
*     description="创建举报反馈记录",
*     tags={"发布与展示"},
*     @OA\Parameter(ref="#/components/parameters/bear_token"),
*     @OA\RequestBody(
*          required=true,
*          description = "",
*          @OA\JsonContent(
 *              @OA\Property(property="userId",type="integer",description = "用户id"),
 *              @OA\Property(property="threadId",type="integer",description = "主题id"),
 *              @OA\Property(property="type",type="integer",description = "举报类型(0个人主页 1主题 2评论/回复)",enum={0,1,2}),
 *              @OA\Property(property="reason",type="string",description="举报原因"),
*               @OA\Property(property="postId",type="string",description = "回复id(当type=2时，postId为必须)")
*         )
*     ),
*
*     @OA\Response(
*          response=200,
*          description="返回更新结果",
*          @OA\JsonContent(
*              allOf={
*                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
*                  @OA\Schema(@OA\Property(property="Data",type="object",
*                  @OA\Property(property="id",type="integer",description = "创建记录id")
*                  ))
*              }
*     ))
* )
*/
