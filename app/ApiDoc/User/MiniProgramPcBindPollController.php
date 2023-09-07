<?php
/**
 * @OA\Get(
 *     path="/api/v3/users/pc/wechat/miniprogram.bind",
 *     summary="PC扫码绑定-扫码成功后小程序二维码（轮询）",
 *     description="pc扫码小程序二维码后轮询接口",
 *     tags={"注册登录"},
 *     @OA\Parameter(name="sessionToken",in="query",required=true,description="请求sessionToken",@OA\Schema(type="string")),
 *     @OA\Response(
 *        response=200,
 *        description="返回登录态相关信息",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(ref="#/components/schemas/dzq_login_token"),
 *        })
 *     )
 * )
 */
