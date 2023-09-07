<?php
/**
 * @OA\Get (
 *     path="/api/v3/users/wechat/h5.rebind",
 *     summary="H5登录换绑",
 *     description="扫PC的H5二维码进行换绑",
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
 *        name="state",
 *        in="query",
 *        required=true,
 *        description = "回调带回",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *        name="sessionToken",
 *        in="query",
 *        required=false,
 *        description = "扫描二维码之后返回的用户sessionToken",
 *        @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="返回数据",
 *        @OA\JsonContent(ref="#/components/schemas/dzq_layout")
 *     )
 * )
 */
