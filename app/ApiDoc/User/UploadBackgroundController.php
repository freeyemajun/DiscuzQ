<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/background",
 *     summary="上传背景图",
 *     description="上传背景图接口",
 *     tags={"个人中心"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "上传背景图",
 *         @OA\JsonContent(
 *             @OA\Property(property="avatar",type="string",description="头像"),
 *             @OA\Property(property="pid",type="integer",description="pid")
 *             )
 *           ),
 * @OA\Response(response=200,
 *        description="上传背景图接口返回",
 *        @OA\JsonContent(allOf ={
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *     })
 *     )
 * )
 *        )
 *     )

 */
