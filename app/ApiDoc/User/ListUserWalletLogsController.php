<?php
/**
 *@OA\Get(
 *    path = "/api/v3/wallet/log",
 *    summary = "收入明细",
 *    description = "个人中心",
 *    tags ={"个人中心"},
 *@OA\Parameter(ref = "#/components/parameters/bear_token"),
 *@OA\Parameter(name="walletLogType",
 *    in="query",
 *    required=true,
 *    description = "类型",
 *    @OA\Schema(type="string")
 *      ),
 *@OA\Parameter(name="filter[startTime]",
 *    in="query",
 *    required=true,
 *    description = "开始时间",
 *    @OA\Schema(type="string")
 *      ),
 *@OA\Parameter(name="filter[endTime]",
 *    in="query",
 *    required=true,
 *    description = "结束时间",
 *    @OA\Schema(type="string")
 *      ),
 *@OA\Parameter(ref = "#/components/parameters/page"),
 *@OA\Parameter(ref = "#/components/parameters/perPage"),
 *@OA\Parameter(ref = "#/components/parameters/filter_changeType"),
 * @OA\Response(response = 200,description = "返回帖子列表",@OA\JsonContent(allOf ={
 *     @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *     @OA\Schema(@OA\Property(property = "Data", type = "object",allOf={
 *         @OA\Schema(ref = "#/components/schemas/dzq_pagination")
 *      }))})
 *  ))
 */
