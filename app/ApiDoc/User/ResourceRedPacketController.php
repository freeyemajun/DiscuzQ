<?php
/**
 * @OA\Get(
 *     path="/api/v3/redpacket.resource",
 *     summary="获取红包数据",
 *     description="获取红包数据",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(name="id",
 *          in="query",
 *          required=true,
 *          description = "红包id",
 *          @OA\Schema(type="integer")
 *      ),
 *     @OA\Response(
 *          response=200,
 *          description="返回更新结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *                  @OA\Schema(@OA\Property(property="Data",type="object",
 *                      @OA\Property(property="id",type="integer",description = "红包id"),
 *                      @OA\Property(property="threadId",type="integer",description = "主题id"),
 *                      @OA\Property(property="postId",type="integer",description = "评论id"),
 *                      @OA\Property(property="rule",type="integer",default="1",description = "发放规则，0定额，1随机"),
 *                      @OA\Property(property="condition",type="integer", default=0,description = "领取红包条件，0回复，1集赞"),
 *                      @OA\Property(property="likenum",type="integer", default=0,description = "集赞个数"),
 *                      @OA\Property(property="money",type="integer", default=0,description = "红包总金额"),
 *                      @OA\Property(property="number",type="integer", default=0,description = "红包个数"),
 *                      @OA\Property(property="remainMoney",type="integer", default=0,description = "剩余红包总额"),
 *                      @OA\Property(property="remainNumber",type="integer", default=0,description = "剩余红包个数"),
 *                      @OA\Property(property="status",type="integer", default=0,description = "0:红包已过期,1:红包未过期"),
 *                      @OA\Property(property="createdAt",type="string", format="datetime",default="2021-01-02 02:22:22",description = "创建时间"),
 *                      @OA\Property(property="updatedAt",type="string", format="datetime",default="2021-01-02 02:22:22",description = "更新时间")
 *                  ))
 *          }))
 * )
 */
