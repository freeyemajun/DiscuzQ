<?php
/**
 * @OA\Get (
 *     path="/api/v3/oauth/qq/user",
 *     summary="授权后登录",
 *     description="QQ登录通过参数换取token接口",
 *     tags={"注册登录"},
 *     @OA\Parameter(
 *        name="sessionId",
 *        in="query",
 *        required=true,
 *        description = "登录接口返回 sessionId",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *        name="accessToken",
 *        in="query",
 *        required=true,
 *        description = "登录接口返回授权token",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *        name="state",
 *        in="query",
 *        required=false,
 *        description = "登录接口返回 state",
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
