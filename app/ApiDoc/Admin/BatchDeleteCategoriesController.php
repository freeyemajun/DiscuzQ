<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/categories.delete",
 *     summary="内容-内容分类",
 *     description="删除内容分类(批量)",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         description = "入参",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\property(property="id",type="string",description="分类id字符串"),
 *         )
 *     ),
 *     @OA\Response(
 *        response=200,
 *        description="出参",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *        })
 *     )
 * )
 */
