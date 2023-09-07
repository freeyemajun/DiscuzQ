<?php
/**
 * @OA\Get (
 *     path="/api/v3/oauth/wechat/miniprogram/code",
 *     summary="生成海报二维码接口",
 *     description="生成海报二维码接口",
 *     tags={"注册登录"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token_true"),
 *     @OA\Parameter(
 *        name="path",
 *        in="query",
 *        required=true,
 *        description = "路径",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *        name="width",
 *        in="query",
 *        required=false,
 *        description = "宽度",
 *        @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *        name="r",
 *        in="query",
 *        required=false,
 *        description = "r",
 *        @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *        name="g",
 *        in="query",
 *        required=false,
 *        description = "g",
 *        @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *        name="b",
 *        in="query",
 *        required=false,
 *        description = "b",
 *        @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回数据",
 *        @OA\JsonContent(allOf={
 *           @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property="Data",type="object",@OA\Property(property = "base64Img", type = "string", description = "base64格式字符串"))),
 *       })
 *     )
 * )
 */
