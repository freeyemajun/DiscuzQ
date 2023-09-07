<?php
/**
 * @OA\Get(
 *     path="/api/v3/invite.users.list",
 *     summary="邀请列表",
 *     description="邀请列表",
 *     tags={"邀请"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(name="page",
 *          in="query",
 *          required=false,
 *          description = "页码",
 *          @OA\Schema(type="integer")
 *      ),
 *     @OA\Parameter(name="perPage",
 *          in="query",
 *          required=false,
 *          description = "每页数据条数，不传则默认20，最大值50",
 *          @OA\Schema(type="integer")
 *      ),
 *     @OA\Response(
 *          response=200,
 *          description="返回更新结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *                  @OA\Schema(@OA\Property(property="Data",type="object",
 *                      @OA\Property(property="pageData",
 *                          @OA\Property(property="userId",type="integer",description = "用户id"),
 *                          @OA\Property(property="nickname",type="string",description = "昵称"),
 *                          @OA\Property(property="avatar",type="string",description = "头像"),
 *                          @OA\Property(property="groupName",type="string",description = "用户组名"),
 *                          @OA\Property(property="totalInviteUsers",type="integer",description = "总共邀请了几个用户"),
 *                          @OA\Property(property="totalInviteBounties",type="integer",description = "赚的赏金"),
 *                          @OA\Property(property="inviteUsersList",type="array",description = "邀请列表",
 *                              @OA\Items(type="object",
 *                                  @OA\Property(property="pid",type="integer",description="邀请者ID"),
 *                                  @OA\Property(property="userId", type="integer",description="被邀请者ID"),
 *                                  @OA\Property(property="avatar", type="string",description="头像"),
 *                                  @OA\Property(property="joinedAt",type="string",format="datetime",default="2021-01-02 02:22:22",description = "加入时间"),
 *                                  @OA\Property(property="nickname", type="string",description="用户名"),
 *                                  @OA\Property(property="bounty", type="integer",description="赏金"),
 *                              )
 *                          ),
 *                      ),
 *                      @OA\Property(property="currentPage",type="integer",description="当前页"),
 *                      @OA\Property(property="perPage",type="integer",description="前端请求的预期每页显示条数"),
 *                      @OA\Property(property="firstPageUrl",type="string",description="第一页url"),
 *                      @OA\Property(property="nextPageUrl",type="string",description="下一页url"),
 *                      @OA\Property(property="prePageUrl",type="string",description="上一页url"),
 *                      @OA\Property(property="pageLength",type="integer",description="每页条数"),
 *                      @OA\Property(property="totalCount",type="integer",description="全部条数"),
 *                      @OA\Property(property="totalPage",type="integer",description="全部页数"),
 *                  )),
 *
 *          }))
 * )
 */
