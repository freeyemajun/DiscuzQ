<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/users.order.logs",
 *     summary = "财务",
 *     description = "订单记录",
 *     tags ={"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Parameter(ref = "#/components/parameters/page"),
 *     @OA\Parameter(ref = "#/components/parameters/perPage"),
 *     @OA\Parameter(
 *         name="filter[orderSn]",
 *         in="query",
 *         required=false,
 *         description = "订单号(精准查询，不是模糊查询)",
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="filter[status]",
 *         in="query",
 *         required=false,
 *         description = "订单状态(0待付款；1已付款；2.取消订单；3支付失败；4订单过期；5部分退款；10全额退款；11在异常订单处理中不进行处理的订单)",
 *         @OA\Schema(
 *             type="integer",
 *             enum={0,1,2,3,4,5,10,11}
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="filter[startTime]",
 *         in="query",
 *         required=false,
 *         description = "订单时间-起",
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="filter[endTime]",
 *         in="query",
 *         required=false,
 *         description = "订单时间-终",
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="filter[nickname]",
 *         in="query",
 *         required=false,
 *         description = "发起方",
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="filter[payeeNickname]",
 *         in="query",
 *         required=false,
 *         description = "收入方",
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="filter[product]",
 *         in="query",
 *         required=false,
 *         description = "商品名称(帖子标题/内容)",
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *     @OA\Parameter(
 *         name="filter[type]",
 *         in="query",
 *         required=false,
 *         description = "交易类型；
 * 1：注册、2：打赏、3：付费主题、4：付费用户组、5：问答提问、6：问答围观、7：付费附件、8：站点续费、9：红包、10：悬赏、11：合并支付、20：文字贴红包、21：长文贴红包",
 *         @OA\Schema(
 *             type="string"
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response = 200,
 *         description = "订单记录列表",
 *         @OA\JsonContent(allOf ={
 *                 @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                 @OA\Schema(title="订单记录列表",description="订单记录列表",@OA\Property(property = "Data", type = "object",allOf={
 *                     @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *                     @OA\Schema(title="关注/粉丝列表", description="关注/粉丝列表",
 *                       @OA\Property(property = "pageData", type = "array", @OA\Items(type = "object",
 *                           @OA\Property(property = "avatar", type="integer", description = "订单ID"),
 *                           @OA\Property(property = "userId", type="integer", description = "付款人/发起方 id"),
 *                           @OA\Property(property = "payeeId", type="integer", description = "收款人/收入方 id"),
 *                           @OA\Property(property = "threadId", type="integer", description = "主题 id"),
 *                           @OA\Property(property = "nickname", type="string", description = "付款人/发起方 昵称"),
 *                           @OA\Property(property = "orderSn", type="string", description = "订单号"),
 *                           @OA\Property(property = "type", type="integer", description = "订单类型：订单类型：1注册(站点付费加入)，2打赏，3购买主题，4购买权限组，5付费提问，6问答围观，7购买附件，8站点付费(续费)，9红包，10悬赏，11合并订单(红包+悬赏合并支付)"),
 *                           @OA\Property(property = "amount", type="number", description = "订单总金额"),
 *                           @OA\Property(property = "status", type="integer", description = "订单状态：0待付款；1已付款；2.取消订单；3支付失败；4订单过期；5部分退款；10全额退款；11在异常订单处理中不进行处理的订单"),
 *                           @OA\Property(property = "createdAt", type="string", description = "订单创建时间"),
 *                           @OA\Property(property = "payeeNickname", type="string", description = "收款人/收入方 昵称"),
 *                           @OA\Property(property = "thread", type = "object",
 *                               @OA\Property(property = "threadId", type="integer", description = "主题ID"),
 *                               @OA\Property(property = "userId", type="integer", description = "作者ID"),
 *                               @OA\Property(property = "title", type="string", description = "标题"),
 *                               @OA\Property(property = "content", type="string", description = "内容"),
 *                          ))
 *                       )
 *                    )
 *                 }))
 *             }
 *         )
 *     )
 * )
 */
