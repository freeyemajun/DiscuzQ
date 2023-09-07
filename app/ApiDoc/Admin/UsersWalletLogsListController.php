<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/users.wallet.logs",
 *     summary = "资金明细列表",
 *     description = "资金明细列表",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_page"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_perPage"),
 *     @OA\Parameter(name = "filter[nickname]", in = "query", required = false, description = "用户昵称", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "filter[changeType]", in = "query", required = false, description = "金额类型
 *     8问答冻结；9问答返还解冻；10提现冻结；11提现成功；12提现解冻；30注册收入；31打赏收入；32人工收入；
 *     33分成打赏收入；34注册分成收入；35问答答题收入；36问答围观收入；41打赏支出；50人工支出；51加入用户组支出；
 *     52付费附件支出；60付费主题收入；61付费主题支出；62分成付费主题收入；63付费附件收入；64付费附件分成收入；
 *     71站点续费支出；81问答提问支出；82问答围观支出；100文字帖红包支出；101文字帖红包冻结；102文字帖红包收入；
 *     103文字帖冻结返还；104文字帖订单异常返现；110长文帖红包支出；111长文帖红包冻结；112长文帖红包收入；113长文帖冻结返还；
 *     114长文帖订单异常返现；120悬赏问答收入；121悬赏帖过期-悬赏帖剩余悬赏金额返回；124问答帖订单异常返现；
 *     150红包冻结；151红包收入；152红包退款；153红包支出;154红包订单异常退款；160悬赏问答冻结；161悬赏问答收入；
 *     162悬赏问答退款；163悬赏订单异常退款；170合并订单冻结；171合并订单退款；172合并订单异常退款",
 *      @OA\Schema(type = "string",enum = {"8", "9","10","11","12","30","31","32","33","34","35","36","41","50","51","52","60","61","62","63","64",
 *                  "101","102","103","104","111","112","113","114","120","121","124","150","151","152","154","160","161","162","163","170","171","172"}
 *     )),
 *     @OA\Parameter(name = "filter[changeDesc]", in = "query", required = false, description = "变动描述", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "filter[startTime]", in = "query", required = false, description = "开始时间", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "filter[endTime]", in = "query", required = false, description = "结束时间", @OA\Schema(type = "string")),
 *     @OA\Response(response = 200, description = "返回资金明细列表", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *              @OA\Schema(
 *                  @OA\Property(property = "pageData", type = "array", @OA\Items(type = "object", allOf = {
 *                      @OA\Schema(@OA\Property(property = "walletLogId", type = "integer", description = "钱包明细id")),
 *                      @OA\Schema(@OA\Property(property = "nickname", type = "integer", description = "用户昵称")),
 *                      @OA\Schema(@OA\Property(property = "username", type = "integer", description = "用户名")),
 *                      @OA\Schema(@OA\Property(property = "createdAt", type = "integer", description = "创建时间")),
 *                      @OA\Schema(@OA\Property(property = "changeAvailableAmount", type = "string", description = "变动可用金额")),
 *                      @OA\Schema(@OA\Property(property = "changeFreezeAmount", type = "string", description = "变动冻结金额")),
 *                      @OA\Schema(@OA\Property(property = "changeDesc", type = "string", description = "变动描述")),
 *                      @OA\Schema(@OA\Property(property = "changeType", type = "string", description = "变动类型"))
 *                  }))
 *              )
 *    }))
 * )
 */
