<?php
/**
 * @OA\Get(
 *     path="/api/v3/users/pc/wechat/miniprogram.genqrcode",
 *     summary="小程序二维码生成接口",
 *     description="pc点击微信登录图片",
 *     tags={"注册登录"},
 *     @OA\Parameter(
 *         name="type",
 *         in="query",
 *         required=true,
 *         description="生成类型",
 *         @OA\Schema(
 *             type="string",
 *             enum={"pc_login_mini","pc_bind_mini"}
 *         )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回二维码",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(ref="#/components/schemas/dzq_qrcode"),
 *        })
 *     )
 * )
 */
