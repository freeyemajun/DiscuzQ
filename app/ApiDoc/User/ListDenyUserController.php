<?php
/**
 * @OA\Get(
 *     path="/api/v3/users/deny.list",
 *     summary="屏蔽用户列表",
 *     description="屏蔽用户列表接口",
 *     tags={"个人中心"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=true,
 *         description = "页数",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="屏蔽用户列表接口返回",
 *         @OA\JsonContent(allOf={
 *          @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *          @OA\Schema(@OA\Property(property = "Data", type = "object",allOf={
 *               @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *               @OA\Schema(@OA\Property(property = "pageData", type = "array",@OA\Items(type = "object",allOf={
 *                @OA\Schema(@OA\Property(property = "avatar", type = "string", description = "用户头像"),
 *                           @OA\Property(property = "denyUserId", type = "integer", description = "屏蔽用户id"),
 *                           @OA\Property(property = "nickname", type = "string", description = "屏蔽用户昵称"),
 *                           @OA\Property(property = "pid", type = "integer", description = "pid"),
 *                           @OA\Property(property = "userId", type = "integer", description = "用户id")
 * )
 *     }))
 *     )}))
 *     })
 *
 *     )
 * )
 */
