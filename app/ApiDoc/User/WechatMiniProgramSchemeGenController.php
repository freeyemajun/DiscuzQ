<?php
/**
 * @OA\Get (
 *     path="/api/v3/users/mobilebrowser/wechat/miniprogram.bind",
 *     summary="手机浏览器绑定小程序",
 *     description="手机浏览器（微信外）拉起小程序后进行小程序绑定;暂无使用",
 *     tags={"注册登录"},
 *     @OA\Parameter(
 *        name="code",
 *        in="query",
 *        required=true,
 *        description = "微信授权返回code",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *        name="sessionId",
 *        in="query",
 *        required=true,
 *        description = "回调地址返回的参数",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *        name="sessionToken",
 *        in="query",
 *        required=false,
 *        description = "扫码登陆时，必传。参数由扫描二维码后在url中带入进页面",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回数据",
 *        @OA\JsonContent(allOf={
 *           @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property="Data",type="object",@OA\Property(property = "openLink", type = "string", description = "链接"))),
 *       })
 *     )
 * )
 */
