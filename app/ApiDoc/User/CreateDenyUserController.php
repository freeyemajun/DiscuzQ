<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/deny.create",
 *     summary="屏蔽用户",
 *     description="屏蔽用户接口",
 *     tags={"个人中心"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "屏蔽用户接口入参",
 *         @OA\JsonContent(
 *             @OA\Property(property="id",type="integer", description = "用户id")
 *             )
 *           ),
 * @OA\Response(response=200,
 *        description="上传背景图接口返回",
 *        @OA\JsonContent(allOf ={
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(title="更新用户返回数据",description="更新用户返回数据",@OA\Property(property="Data",type="object",
 *                @OA\Property(property="createdAt", type="string", description="创建时间"),
 *                @OA\Property(property="denyUserId",type="integer", description = "屏蔽用户id"),
 *                @OA\Property(property="id",type="integer", description = "id"),
 *                @OA\Property(property="userId",type="integer", description = "用户id")
 *          ))
 *
 *     })
 *     )
 * )
 *        )
 *     )

 */
