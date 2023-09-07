<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/stopwords.delete",
 *     summary="删除敏感词",
 *     description="删除敏感词",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "",
 *         @OA\JsonContent(
 *             @OA\Property(property="id",type="string",description="敏感词id字符串,如：1,2,3")
 *         )
 *     ),
 *     @OA\Response(
 *          response=200,
 *          description="返回结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *          }))
 * )
 */
