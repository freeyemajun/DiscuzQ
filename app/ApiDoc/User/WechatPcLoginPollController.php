<?php
/**
 * @OA\Get(
 *     path="/api/v3/users/pc/wechat/h5.login",
 *     summary="PC扫码登录-扫码成功后H5二维码（轮询）",
 *     description="pc扫码公众号二维码后轮询接口",
 *     tags={"注册登录"},
 *     @OA\Parameter(name="sessionToken",in="query",required=true,description="请求sessionToken",@OA\Schema(type="string")),
 *     @OA\Response(
 *        response=200,
 *        description="返回登录态相关信息",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(ref="#/components/schemas/dzq_login_token"),
 *            @OA\Schema(
 *                @OA\Property(property="Data",type="object",@OA\Property(property = "userId", type = "integer", description = "用户id"))
 *            )
 *        })
 *     )
 * )
 */
