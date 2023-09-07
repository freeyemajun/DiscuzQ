<?php
/**
 * @OA\Post(
 *     path="/api/v3/wallet/cash",
 *     summary="提现",
 *     description="提现接口",
 *     tags={"个人中心"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "提现参数",
 *         @OA\JsonContent(
 *              @OA\Property(property="cashApplyAmount",type="number", description ="提现金额"),
 *              @OA\Property(property="receiveAccount",type="string", description ="收款账号"),
 *             )
 *           ),
 * @OA\Response(response=200,
 *        description="提现接口返回",
 *        @OA\JsonContent(allOf ={
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *     })
 *     )
 * )
 *        )
 *     )

 */
