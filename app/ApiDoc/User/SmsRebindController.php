<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/sms.rebind",
 *     summary="换绑手机号",
 *     description="用户换绑手机号时请求该接口，无解绑手机号逻辑",
 *     tags={"注册登录"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *          required=true,
 *          description = "请求参数",
 *          @OA\JsonContent(
 *              @OA\Property(property="mobile",type="string",description="手机号码"),
 *              @OA\Property(property="code",type="string",description="验证码"),
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
