<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/deny.delete",
 *     summary="解除屏蔽",
 *     description="解除屏蔽接口",
 *     tags={"个人中心"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "解除屏蔽",
 *         @OA\JsonContent(
 *             @OA\Property(property="id",type="integer",description="解除用户id")
 *             )
 *           ),
 * @OA\Response(response=200,
 *        description="解除屏蔽接口返回",
 *        @OA\JsonContent(allOf ={
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout")
 *     })
 *     )
 * )
 *        )
 *     )

 */
