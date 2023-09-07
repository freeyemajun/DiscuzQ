<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/update.mobile",
 *     summary="更换手机号",
 *     description="更换手机号接口",
 *     tags={"个人中心"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "更换手机号",
 *         @OA\JsonContent(
 *             @OA\Property(property="mobile",type="string",description="手机号"),
 *             @OA\Property(property="code",type="string",description="验证码"),
 *             )
 *           ),
 * @OA\Response(response=200,
 *        description="编辑资料接口返回",
 *        @OA\JsonContent(allOf ={
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *     })
 *     )
 * )
 *        )
 *     )

 */
