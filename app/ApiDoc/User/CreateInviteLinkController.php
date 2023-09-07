<?php
/**
 * @OA\Get(
 *     path="/api/v3/invite.link.create",
 *     summary="创建邀请链接",
 *     description="创建邀请链接",
 *     tags={"邀请"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(name="group",
 *          in="query",
 *          required=false,
 *          description = "用户组id",
 *          @OA\Schema(type="integer")
 *      ),
 *     @OA\Response(
 *          response=200,
 *          description="返回更新结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *                  @OA\Schema(@OA\Property(property="Data",type="object",
 *                      @OA\Property(property="userId",type="integer",description = "邀请者id"),
 *                      @OA\Property(property="code",type="string",description = "邀请码"),
 *                  ))
 *          }))
 * )
 */
