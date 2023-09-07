<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/categories.create",
 *     summary="内容-内容分类",
 *     description="创建内容分类(批量)",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         description = "入参",
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\property(property="data",type="array",@OA\Items(type = "object",
 *                 @OA\property(property="name",type="string",description="分类名"),
 *                 @OA\property(property="description",type="integer",description="排序"),
 *                 @OA\property(property="sort",type="string",description="分类描述"),
 *                 @OA\property(property="parentId",type="integer",description="所属一级分类ID"),
 *             )),
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
