<?php
/**
 *@OA\Get(
 *    path = "/api/v3/wallet/cash",
 *    summary = "提现明细",
 *    description = "个人中心",
 *    tags ={"个人中心"},
 *@OA\Parameter(ref = "#/components/parameters/bear_token"),
 *@OA\Parameter(
 *    name="filter[startTime]",
 *    in="query",
 *    required=true,
 *    description = "开始时间",
 *    @OA\Schema(type="string")),
 *@OA\Parameter(
 *    name="filter[endTime]",
 *    in="query",
 *    required=true,
 *    description = "结束时间",
 *    @OA\Schema(type="string")
 *      ),
 *@OA\Parameter(ref = "#/components/parameters/page"),
 *@OA\Parameter(ref = "#/components/parameters/perPage"),
 *@OA\Parameter(ref = "#/components/parameters/filter_cashStatus"),
 *@OA\Response(
 *        response = 200,
 *        description = "返回提现明细列表",
 *        @OA\JsonContent(allOf ={
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(title="提现列表", description="提现列表",@OA\Property(property = "Data", type = "array", description="提现列表", @OA\Items(type = "object",
 *                      @OA\Property(property = "cash_user", type = "integer", description = "提现用户"),
 *                      @OA\Property(property = "cash_sn", type = "string", description = "提现流水号"),
 *                      @OA\Property(property = "cash_status", type = "integer", description = "提现状态"),
 *                      @OA\Property(property = "cash_username", type = "integer", description = "提现人"),
 *                      @OA\Property(property = "cash_type", type = "integer", description = "提现方式"),
 *                      @OA\Property(property = "cash_mobile", type = "integer", description = "提现到的手机号码"),
 *                      @OA\Property(property = "cash_start_time", type = "string", description = "申请时间范围：开始"),
 *                      @OA\Property(property = "cash_end_time", type = "string", description = "申请时间范围：结束"),
 *                )))
 *            }
 *        )
 *    )
 * )
 */
