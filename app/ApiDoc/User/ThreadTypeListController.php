<?php
/**
 *@OA\Get(
 *    path = "/api/v3/thread.typelist",
 *    summary = "获取帖子类型列表",
 *    description = "获取帖子类型列表",
 *    tags ={"发布与展示"},
 *    @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *    @OA\Response(
 *        response = 200,
 *        description = "接口调用成功",
 *        @OA\JsonContent(allOf ={
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(@OA\Property(property = "Data", type = "array", description="类型列表", @OA\Items(type = "object",
 *                      @OA\Property(property = "name", type = "string", description = "类型名"),
 *                      @OA\Property(property = "type", type = "string", description = "类型id(101图片102语音103视频...帖子类型插件id...)"),
 *                )))
 *            })
 *   )
 * )
 */

