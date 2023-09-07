<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/sms.reset.pwd",
 *     summary="重设登录密码",
 *     description="用户重新设置登录密码时请求该接口",
 *     tags={"注册登录"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *          required=true,
 *          description = "请求参数",
 *          @OA\JsonContent(
 *              @OA\Property(property="mobile",type="string",description="手机号码"),
 *              @OA\Property(property="code",type="string",description="验证码"),
 *              @OA\Property(property="password",type="string",description="密码"),
 *          )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回数据",
 *        @OA\JsonContent(allOf={
 *           @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *       })
 *     )
 * )
 */
