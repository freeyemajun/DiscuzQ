<?php
/**
 * @OA\Get(
 *     path="/api/v3/users/mobilebrowser/wechat/miniprogram.genscheme",
 *     summary="拉起小程序scheme生成接口",
 *     description="手机浏览器拉起小程序使用",
 *     tags={"注册登录"},
 *     @OA\Response(
 *        response=200,
 *        description="返回scheme码",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(
 *                @OA\Property(property="Data",type="object",@OA\Property(property="openLink",type="string",description="sheme码"))
 *            )
 *        })
 *     )
 * )
 */
