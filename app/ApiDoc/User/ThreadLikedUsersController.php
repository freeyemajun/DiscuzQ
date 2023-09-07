<?php
/**
 * @OA\Get(
 *     path="/api/v3/thread.likedusers",
 *     summary="点赞、支付、打赏的用户列表",
 *     description="点赞、支付、打赏的用户列表",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(name="threadId",in="query",required=true,description = "主题id",@OA\Schema(type="integer")),
 *     @OA\Parameter(name="page",in="query",required=true,description = "获取数据的页码",@OA\Schema(type="integer")),
 *     @OA\Parameter(name="type",in="query",required=true,description = "用户类型（0:显示全部 1：点赞 2：付费or打赏）", @OA\Schema(type="integer",enum={0,1,2})),
 *     @OA\Response(response=200,description="返回点赞/支付的用户列表",@OA\JsonContent(allOf={
 *          @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *          @OA\Schema(@OA\Property(property="Data",type="object",@OA\Property(property="pageData",
 *              @OA\Property(property="allCount",type="integer",description="全部点赞或支付的用户数"),
 *              @OA\Property(property="likeCount",type="integer",description="点赞用户数"),
 *              @OA\Property(property="rewardCount",type="integer",description="打赏用户数"),
 *              @OA\Property(property="paidCount",type="integer",description="支付用户数"),
 *              @OA\Property(property="list",type="array",description="用户列表",@OA\Items(type="object",
 *                  @OA\Property(property="userId",type="integer",description="当前页"),
 *                  @OA\Property(property="createdAt",type="string",description="用户注册时间"),
 *                  @OA\Property(property="type",type="integer",enum={0,1,2}, description="点赞、付费、打赏 0:显示全部 1：点赞 2：付费or打赏"),
 *                  @OA\Property(property="passedAt",type="string",description="已过多少分钟"),
 *                  @OA\Property(property="nickname",type="string",description="昵称"),
 *                  @OA\Property(property="avatar",type="string",description="头像"),
 *              ))
 *          ),
 *          @OA\Property(property="currentPage",type="integer",description="当前页"),
 *          @OA\Property(property="perPage",type="integer",description="前端请求的预期每页显示条数"),
 *          @OA\Property(property="firstPageUrl",type="string",description="第一页url"),
 *          @OA\Property(property="nextPageUrl",type="string",description="下一页url"),
 *          @OA\Property(property="prePageUrl",type="string",description="上一页url"),
 *          @OA\Property(property="pageLength",type="integer",description="每页条数"),
 *          @OA\Property(property="totalCount",type="integer",description="全部条数"),
 *          @OA\Property(property="totalPage",type="integer",description="全部页数"),
 *          ))
 *     }))
 * )
 */
