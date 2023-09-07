<?php
/**
 * @OA\Get(
 *    path = "/api/backAdmin/users.cash.logs",
 *    summary = "财务",
 *    description = "提现管理",
 *    tags ={"管理后台"},
 *    @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *    @OA\Parameter(ref = "#/components/parameters/page"),
 *    @OA\Parameter(ref = "#/components/parameters/perPage"),
 *    @OA\Parameter(
 *        name="filter[cashSn]",
 *        in="query",
 *        required=false,
 *        description = "提现流水号(精准查询，不是模糊查询)",
 *        @OA\Schema(
 *            type="string"
 *        )
 *    ),
 *    @OA\Parameter(
 *        name="filter[cashStatus]",
 *        in="query",
 *        required=false,
 *        description = "提现状态(提现状态：1待审核，2审核通过，3审核不通过，4待打款，5已打款， 6打款失败)",
 *        @OA\Schema(
 *            type="integer",
 *            enum={1,2,3,4,5,6}
 *        )
 *    ),
 *    @OA\Parameter(
 *        name="filter[startTime]",
 *        in="query",
 *        required=false,
 *        description = "订单时间-起",
 *        @OA\Schema(
 *            type="string"
 *        )
 *    ),
 *    @OA\Parameter(
 *        name="filter[endTime]",
 *        in="query",
 *        required=false,
 *        description = "订单时间-终",
 *        @OA\Schema(
 *            type="string"
 *        )
 *    ),
 *    @OA\Parameter(
 *        name="filter[nickname]",
 *        in="query",
 *        required=false,
 *        description = "操作用户",
 *        @OA\Schema(
 *            type="string"
 *        )
 *    ),
 *    @OA\Response(
 *        response = 200,
 *        description = "订单记录列表",
 *        @OA\JsonContent(allOf ={
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(title="订单记录列表",description="订单记录列表",@OA\Property(property = "Data", type = "object",allOf={
 *                    @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *                    @OA\Schema(title="关注/粉丝列表", description="关注/粉丝列表",
 *                      @OA\Property(property = "pageData", type = "array", @OA\Items(type = "object",
 *                          @OA\Property(property = "id", type="integer", description = "提现ID"),
 *                          @OA\Property(property = "userId", type="integer", description = "用户ID"),
 *                          @OA\Property(property = "nickname", type="string", description = "操作用户"),
 *                          @OA\Property(property = "cashSn", type="string", description = "流水号"),
 *                          @OA\Property(property = "cashCharge", type="number", description = "提现手续费"),
 *                          @OA\Property(property = "cashActualAmount", type="number", description = "实际提现金额"),
 *                          @OA\Property(property = "cashApplyAmount", type="number", description = "提现申请金额"),
 *                          @OA\Property(property = "cashStatus", type="integer", description = "提现状态：1待审核，2审核通过，3审核不通过，4待打款，5已打款， 6打款失败"),
 *                          @OA\Property(property = "cashMobile", type="string", description = "收款账号"),
 *                          @OA\Property(property = "cashType", type="integer", description = "提现转账类型：0：人工转账， 1：企业零钱付款"),
 *                          @OA\Property(property = "remark", type="string", description = "备注或原因"),
 *                          @OA\Property(property = "tradeTime", type="string", description = "交易时间"),
 *                          @OA\Property(property = "tradeNo", type="string", description = "交易号"),
 *                          @OA\Property(property = "errorCode", type="string", description = "错误代码"),
 *                          @OA\Property(property = "errorMessage", type="string", description = "交易失败描述"),
 *                          @OA\Property(property = "refundsStatus", type="integer", description = "返款状态，0未返款，1已返款"),
 *                          @OA\Property(property = "createdAt", type="string", description = "创建时间"),
 *                          @OA\Property(property = "updatedAt", type="string", description = "更新时间"),
 *                          @OA\Property(property = "wechat", type = "object",
 *                              @OA\Property(property = "mpOpenid", type="string", description = "公众号openid"),
 *                              @OA\Property(property = "minOpenid", type="string", description = "小程序openid"),
 *                         ))
 *                      )
 *                   )
 *                }))
 *            }
 *        )
 *    )
 *)
 */
