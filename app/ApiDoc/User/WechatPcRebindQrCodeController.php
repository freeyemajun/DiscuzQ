<?php
/**
 * @OA\Get(
 *     path="/api/v3/users/pc/wechat.rebind.genqrcode",
 *     summary="微信换绑二维码生成",
 *     description="小程序换绑仅在PC端个人中心请求该接口",
 *     tags={"注册登录"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(name="redirectUri",in="query",required=false,description="前端回调uri",@OA\Schema(type="string")),
 *     @OA\Response(
 *        response=200,
 *        description="返回公众号、小程序二维码",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(ref="#/components/schemas/dzq_qrcode"),
 *        })
 *     )
 * )
 */
