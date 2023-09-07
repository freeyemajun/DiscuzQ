<?php
/**
 * @OA\Get (
 *     path="/api/v3/users/wechat/h5.oauth",
 *     summary="授权跳转接口",
 *     description="进行H5登录时的跳转接口",
 *     tags={"注册登录"},
 *     @OA\Parameter(name="redirect",in="query",required=true,description="H5授权回调",@OA\Schema(type="string")),
 *     @OA\Response(
 *        response=302,
 *        description="直接跳转到H5授权页"
 *     )
 * )
 */
