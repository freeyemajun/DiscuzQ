<?php
/**
 * @OA\Get(
 *     path="/api/v3/check.user.get.redpacket",
 *     summary="是否领取红包后第一次进入帖子",
 *     description="查询是否是领取红包后第一次进入帖子",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(name="threadId",
 *          in="query",
 *          required=true,
 *          description = "主题id",
 *          @OA\Schema(type="integer")
 *      ),
 *     @OA\Response(
 *          response=200,
 *          description="返回更新结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *                  @OA\Schema(@OA\Property(property="Data",type="object",
 *                      @OA\Property(property="status",type="boolean",description = "是否领取到红包"),
 *                      @OA\Property(property="amount",type="integer",description = "领取的红包金额"),
 *                      @OA\Property(property="getRedPacketTime",type="string",default="2021-02-18T09:26:19.000000",description = "领取红包的时间"),
 *                      @OA\Property(property="afterGetRedPacketFirstEnter",type="boolean",description = "是否是领取红包后第一次进入帖子"),
 *                  ))
 *          }))
 * )
 */
