<?php
/**
 * @OA\Get(
 *     path = "/api/v3/wallet/user",
 *     summary = "获取用户钱包信息",
 *     description = "获取用户钱包信息",
 *     tags = {"支付钱包"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Response(
 *        response = 200,
 *        description = "获取用户钱包信息",
 *        @OA\JsonContent(allOf = {
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(@OA\Property(property = "Data", type = "object",allOf = {
 *                    @OA\Schema(ref = "#/components/schemas/user_wallet_detail_output")
 *                }))
 *            }
 *        )
 *     )
 * )
 */
