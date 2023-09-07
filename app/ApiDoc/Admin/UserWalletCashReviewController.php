<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/wallet.cash.review",
 *     summary="财务",
 *     description="审核(审核通过/审核不通过)",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         description = "入参",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\property(property="ids",type="array",description="提现 ID(提现记录 ID 数组)",@OA\Items(type="integer")),
 *             @OA\property(property="cashStatus",type="integer",description="审核状态(2：审核通过，3：审核不通过)"),
 *             @OA\property(property="remark",type="string",description="审核原因(审核不通过时填写原因)"),
 *         )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="审核提交结果，提交成功对应提现记录 ID 键值为 success（审核成功），failure（审核失败），pem_notexist（证书不存在）",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(
 *                @OA\Property(property="Data",type="object",
 *                    @OA\Property(property="1",type="string",description="审核结果")
 *                )
 *            )
 *        })
 *     )
 * )
 */
