<?php
/**
 * @OA\Get(
 *     path="/api/backAdmin/user.wallet",
 *     summary="用户管理",
 *     description="用户钱包列表",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token_true"),
 *     @OA\Parameter(
 *        name="userId",
 *        in="query",
 *        required=true,
 *        description = "用户id",
 *        @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回绑定scheme码",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(
 *                @OA\Property(property="Data",type="object",
 *                    @OA\Property(property="userId",type="integer",description="用户id"),
 *                    @OA\Property(property="availableAmount",type="number",description="用户钱包可用余额"),
 *                    @OA\Property(property="freeze",type="number",description="用户钱包冻结金额，交易过程中冻结的资金"),
 *                    @OA\Property(property="walletStatus",type="integer",description="钱包状态，0：表示正常；1：表示冻结提现，此状态下，用户无法申请提现"),
 *                    @OA\Property(property="createdAt",type="string",description="添加时间"),
 *                    @OA\Property(property="updatedAt",type="string",description="修改时间"),
 *                    @OA\Property(property="cashTaxRatio",type="number",description="用户提现时的税率"),
 *                    @OA\Property(property="username",type="string",description="用户信息"),
 *                )
 *            )
 *        })
 *     )
 * )
 */
