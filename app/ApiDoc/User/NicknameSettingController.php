<?php
/**
 *
 * @OA\POST(
 *     path="/api/v3/users/users/nickname.set",
 *     summary="设置或修改昵称",
 *     description="设置或修改用户昵称，需要登录态",
 *     tags={"注册登录"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token_true"),
 *     @OA\RequestBody(
 *        required=true,
 *        description = "请求参数",
 *        @OA\JsonContent(
 *           @OA\Property(property="nickname",type="string",description="用户昵称")
 *        )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回数据",
 *        @OA\JsonContent(ref="#/components/schemas/dzq_layout")
 *     )
 * )
 *
 */
