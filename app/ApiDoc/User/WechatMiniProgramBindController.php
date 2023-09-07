<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/wechat/miniprogram.bind",
 *     summary="小程序绑定",
 *     description="小程序绑定接口",
 *     tags={"注册登录"},
 *     @OA\RequestBody(
 *        required=true,
 *        description = "请求参数",
 *        @OA\JsonContent(
 *           @OA\Property(property="jsCode",type="string",description="通过wx.login()获取的code"),
 *           @OA\Property(property="iv",type="string",description="通过wx.getUserInfo()获取的iv"),
 *           @OA\Property(property="encryptedData",type="string",description="通过wx.getUserInfo()获取的encryptedData"),
 *           @OA\Property(property="sessionToken",type="string",description="PC扫码登陆时，必传;参数由扫描二维码后在url中带入进页面;个人中心不需要传"),
 *           @OA\Property(property="type",type="string",description="标识来源:pc或h5;个人中心不需要传;用于区别sessionToken来源于pc还是h5"),
 *        )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回数据",
 *        @OA\JsonContent(allOf={
 *           @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *           @OA\Schema(@OA\Property(property="Data",type="object",
 *              @OA\Property(property = "tokenType", type = "string", description = "token 类型"),
 *              @OA\Property(property = "expiresIn", type = "integer", description = "过期时间(秒),30天过期"),
 *              @OA\Property(property = "accessToken", type = "string", description = "token"),
 *              @OA\Property(property = "refreshToken", type = "string", description = "刷新 token"),
 *              @OA\Property(property = "isMissNickname", type = "boolean", description = "用户是否缺少昵称字段的填写,true:缺少,false:不缺少"),
 *              @OA\Property(property = "userStatus", type = "integer",enum={0,1,2,3,4,10}, description = "用户状态,0:正常 1:禁用 2:审核中 3:审核拒绝 4:审核忽略 10:待填写扩展审核字段"),
 *              @OA\Property(property = "userId", type = "integer", description = "用户id"),
 *           ))
 *       })
 *     )
 * )
 */
