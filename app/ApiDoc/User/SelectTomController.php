<?php
/**
 * @OA\Get(
 *     path="/api/v3/tom.detail",
 *     summary="主题内扩展对象详情",
 *     description="主体内扩展对象详情，如图片、视频、投票等",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(name="threadId",in="query",required=true,description = "主题id",@OA\Schema(type="integer")),
 *     @OA\Parameter(name="tomId",in="query",required=true,description = "扩展对象id",@OA\Schema(type="integer")),
 *     @OA\Parameter(name="key",in="query",required=true,description = "对象所属索引标记",@OA\Schema(type="string")),
 *     @OA\Response(response=200,description="返回点赞/支付的用户列表",@OA\JsonContent(allOf={
 *         @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *         @OA\Schema(@OA\Property(property="Data",type="object",@OA\Property(property="key",type="object",ref="#/components/schemas/local_plugin_output")))
 *     }))
 * )
 */
