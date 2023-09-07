<?php
/**
 * @OA\Get(
 *     path="/api/v3/users/pc/wechat.rebind.poll",
 *     summary="PC扫码换绑-扫码成功后（轮询）",
 *     description="pc扫码进行换绑的二维码轮询接口",
 *     tags={"注册登录"},
 *     @OA\Parameter(name="sessionToken",in="query",required=true,description="请求sessionToken",@OA\Schema(type="string")),
 *     @OA\Response(
 *        response=200,
 *        description="返回微信用户相关信息",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(
 *                title="微信换绑结果",
 *                @OA\Property(property="Data",type="object",
 *                    @OA\Property(property = "rebind", type = "boolean", description = "换绑是否成功"),
 *                    @OA\Property(property = "wxuser", type = "object", description = "微信用户",allOf = {
 *                        @OA\Schema(ref = "#/components/schemas/dzq_wechat_user_model"),
 *                        @OA\Schema(@OA\Property(property = "user", type = "object", description = "用户信息",ref = "#/components/schemas/dzq_user_model")),
 *                    })
 *                )
 *            )
 *        })
 *     )
 * )
 */
