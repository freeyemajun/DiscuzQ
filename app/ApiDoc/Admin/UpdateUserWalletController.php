<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/update.user.wallet",
 *     summary="用户管理",
 *     description="用户钱包增加",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         description = "入参",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\property(property="userId",type="integer",description="用户 id"),
 *             @OA\property(property="operateType",type="integer",description="1：增加余额操作，2：减少余额操作"),
 *             @OA\property(property="operateAmount",type="number",description="给用户增加或减少的金额，都为大于零的数值"),
 *             @OA\property(property="operateReason",type="string",description="填写操作的备注或原因"),
 *             @OA\property(property="walletStatus",type="integer",description="标志用户钱包状态，0：表示正常；1：表示冻结提现，此状态下用户将无法提现"),
 *         )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="钱包信息",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(
 *                @OA\Property(property="Data",type="object",
 *                    @OA\Property(property="userId",type="integer",description="用户id"),
 *                    @OA\Property(property="availableAmount",type="string",description="可用金额"),
 *                    @OA\Property(property="freezeAmount",type="string",description="冻结金额"),
 *                    @OA\Property(property="walletStatus",type="integer",description="钱包状态"),
 *                    @OA\Property(property="createdAt",type="string",description="创建时间"),
 *                    @OA\Property(property="updatedAt",type="string",description="更新时间"),
 *                )
 *            )
 *        })
 *     )
 * )
 */
