<?php
/**
 * @OA\Get(
 *     path="/api/v3/users/pc/wechat/h5.genqrcode",
 *     summary="H5二维码生成接口",
 *     description="pc点击微信登录图片或者手机浏览器公众号登录",
 *     tags={"注册登录"},
 *     @OA\Parameter(
 *         name="type",
 *         in="query",
 *         required=true,
 *         description="生成类型",
 *         @OA\Schema(
 *             type="string",
 *             enum={"pc_login","pc_bind","mobile_browser_login","mobile_browser_bind"}
 *         )
 *     ),
 *     @OA\Parameter(name="redirectUri",in="query",required=true,description="H5授权回调",@OA\Schema(type="string")),
 *     @OA\Parameter(name="sessionToken",in="query",required=false,description="手机浏览器绑定微信使用",@OA\Schema(type="string")),
 *     @OA\Response(
 *        response=200,
 *        description="返回H5二维码",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(ref="#/components/schemas/dzq_qrcode"),
 *        })
 *     )
 * )
 */
