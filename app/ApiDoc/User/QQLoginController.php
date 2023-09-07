<?php
/**
 * @OA\Get (
 *     path="/api/v3/oauth/qq",
 *     summary="授权跳转接口",
 *     description="进行QQ登录时的跳转接口",
 *     tags={"注册登录"},
 *     @OA\Parameter(name="display",in="query",required=false,description="用于展示的样式。不传则默认展示为PC下的样式。如果传入“mobile”，则展示为mobile端下的样式。",@OA\Schema(type="string")),
 *     @OA\Parameter(name="code",in="query",required=false,description="qq授权回调后返回参数(第一次请求不需要填，回调后必填)",@OA\Schema(type="string")),
 *     @OA\Parameter(name="sessionId",in="query",required=false,description="回调地址返回的参数(第一次请求不需要填，回调后必填)",@OA\Schema(type="string")),
 *     @OA\Parameter(name="state",in="query",required=false,description="qq授权返回 state(第一次请求不需要填，回调后必填)",@OA\Schema(type="string")),
 *     @OA\Response(
 *        response=302,
 *        description="直接跳转到qq授权页"
 *     )
 * )
 */
