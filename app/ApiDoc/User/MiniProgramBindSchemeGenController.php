<?php
/**
 * @OA\Get(
 *     path="/api/v3/users/mobilebrowser/wechat/miniprogram.genbindscheme",
 *     summary="拉起绑定小程序scheme生成接口",
 *     description="手机浏览器拉起小程序绑定页面使用",
 *     tags={"注册登录"},
 *     @OA\Parameter(
 *         name="type",
 *         in="query",
 *         required=true,
 *         description="scheme生成类型",
 *         @OA\Schema(
 *             type="string",
 *             enum={"bind_mini"}
 *         )
 *     ),
 *     @OA\Parameter(name="query",in="query",required=false,description="前端自定义传参",@OA\Schema(type="string")),
 *     @OA\Response(
 *        response=200,
 *        description="返回绑定scheme码",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(
 *                @OA\Property(property="Data",type="object",
 *                    @OA\Property(property="openLink",type="string",description="sheme码")
 *                )
 *            )
 *        })
 *     )
 * )
 */
