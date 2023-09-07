<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/cache.delete",
 *     summary="清缓存",
 *     description="清缓存",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Response(
 *          response=200,
 *          description="返回结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *          }))
 * )
 */
