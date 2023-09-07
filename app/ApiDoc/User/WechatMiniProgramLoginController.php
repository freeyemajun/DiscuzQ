<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/wechat/miniprogram.login",
 *     summary="小程序登录",
 *     description="用小程序登录时请求",
 *     tags={"注册登录"},
 *     @OA\RequestBody(
 *        required=true,
 *        description = "请求参数",
 *        @OA\JsonContent(
 *           @OA\Property(property="jsCode",type="string",description="通过 wx.login()获取的 code，文档地址https://developers.weixin.qq.com/miniprogram/dev/api/open-api/login/wx.login.html"),
 *           @OA\Property(property="iv",type="string",description="通过 wx.getUserInfo()获取的 iv，文档地址https://developers.weixin.qq.com/miniprogram/dev/api/open-api/user-info/wx.getUserInfo.html wx.getUserInfo()默认获取英文用户资料，可传参数{'lang':'zh_CN'}获取简体中文"),
 *           @OA\Property(property="encryptedData",type="string",description="通过 wx.getUserInfo()获取的 encryptedData，文档地址[https://developers.weixin.qq.com/miniprogram/dev/api/open-api/user-info/wx.getUserInfo.html](https://developers.weixin.qq.com/miniprogram/dev/api/open-api/user-info/wx.getUserInfo.html)wx.getUserInfo()默认获取英文用户资料，可传参数{'lang':'zh_CN'}获取简体中文"),
 *           @OA\Property(property="inviteCode",type="string",description="注册邀请码"),
 *           @OA\Property(property="sessionToken",type="string",description="PC扫码登陆时，必传。参数由扫描二维码后在url中带入进页面"),
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
