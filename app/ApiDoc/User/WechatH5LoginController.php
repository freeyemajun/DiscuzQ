<?php
/**
 * @OA\Get (
 *     path="/api/v3/users/wechat/h5.login",
 *     summary="授权后登录",
 *     description="H5授权之后的登录跳转接口",
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
 *        name="inviteCode",
 *        in="query",
 *        required=true,
 *        description = "微信授权返回state",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *        name="path",
 *        in="query",
 *        required=false,
 *        description = "管理员生成的注册邀请码",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *        name="sessionToken",
 *        in="query",
 *        required=false,
 *        description = "PC扫码登陆时，必传。参数由扫描二维码后在url中带入进页面",
 *        @OA\Schema(type="string")
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
 *              @OA\Property(property = "avatar", type = "string", description = "用户头像")
 *          ))
 *       })
 *     )
 * )
 */
