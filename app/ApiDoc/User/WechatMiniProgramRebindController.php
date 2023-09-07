<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/wechat/miniprogram.rebind",
 *     summary="小程序换绑",
 *     description="扫PC的小程序二维码进行换绑",
 *     tags={"注册登录"},
 *     @OA\RequestBody(
 *        required=true,
 *        description = "请求参数",
 *        @OA\JsonContent(
 *           @OA\Property(property="jsCode",type="string",description="通过wx.login()获取的code"),
 *           @OA\Property(property="iv",type="string",description="通过wx.getUserInfo()获取的iv"),
 *           @OA\Property(property="encryptedData",type="string",description="通过wx.getUserInfo()获取的encryptedData"),
 *           @OA\Property(property="sessionToken",type="string",description="PC扫码登陆时，必传;参数由扫描二维码后在url中带入进页面"),
 *        )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回数据",
 *        @OA\JsonContent(ref="#/components/schemas/dzq_layout")
 *     )
 *     )
 * )
 */
