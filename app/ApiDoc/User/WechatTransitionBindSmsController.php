<?php
/**
 *
 * @OA\Post (
 *     path="/api/v3/users/wechat/transition/sms.bind",
 *     summary="微信绑定手机并登陆",
 *     description="过渡阶段,微信登录绑定手机号并登录",
 *     tags={"注册登录"},
 *     @OA\RequestBody(
 *        required=true,
 *        description = "请求参数",
 *        @OA\JsonContent(
 *           @OA\Property(property="mobile",type="string",description="手机号码"),
 *           @OA\Property(property="code",type="string",description="验证码"),
 *           @OA\Property(property="sessionToken",type="string",description="session_token,微信登录授权后所带"),
 *        )
 *     ),
 *      @OA\Response(
 *         response=200,
 *         description="返回数据",
 *         @OA\JsonContent(allOf={
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
 *        })
 *     )
 * )
 *
 */
