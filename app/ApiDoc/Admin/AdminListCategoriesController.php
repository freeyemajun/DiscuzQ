<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/categories",
 *     summary = "内容-内容分类",
 *     description = "内容分类列表",
 *     tags ={"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Response(
 *         response = 200,
 *         description = "内容分类列表",
 *         @OA\JsonContent(allOf ={
 *                 @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                 @OA\Schema(title="内容分类列表",description="内容分类列表",@OA\Property(property = "Data", type = "array",@OA\Items(type = "object",
 *                     @OA\Property(property = "categoryId", type="integer", description = "分类id"),
 *                     @OA\Property(property = "name", type="string", description = "分类名称"),
 *                     @OA\Property(property = "description", type="string", description = "分类描述"),
 *                     @OA\Property(property = "icon", type="string", description = "图标"),
 *                     @OA\Property(property = "sort", type="integer", description = "排序序号(前端可不关注)"),
 *                     @OA\Property(property = "property", type="integer", description = "属性(暂时不用)"),
 *                     @OA\Property(property = "threadCount", type="integer", description = "分类下帖子总数数"),
 *                     @OA\Property(property = "parentid", type="integer", description = "一级分类id"),
 *                     @OA\Property(property = "canCreateThread", type="boolean", description = "是否可以在此分类下创建帖子"),
 *                     @OA\Property(property = "searchIds", type = "array",@OA\Items(type = "integer")),
 *                     @OA\Property(property = "children", type = "array",@OA\Items(type = "object",
 *                         @OA\Property(property = "categoryId", type="integer", description = "分类id"),
 *                         @OA\Property(property = "name", type="string", description = "分类名称"),
 *                         @OA\Property(property = "description", type="string", description = "分类描述"),
 *                         @OA\Property(property = "icon", type="string", description = "图标"),
 *                         @OA\Property(property = "sort", type="integer", description = "排序序号(前端可不关注)"),
 *                         @OA\Property(property = "property", type="integer", description = "属性(暂时不用)"),
 *                         @OA\Property(property = "threadCount", type="integer", description = "分类下帖子总数数"),
 *                         @OA\Property(property = "parentid", type="integer", description = "一级分类id"),
 *                         @OA\Property(property = "canCreateThread", type="boolean", description = "是否可以在此分类下创建帖子"),
 *                         @OA\Property(property = "searchIds", type = "string", description = "用于搜索内容分类帖子的id数组，只有一个id时为数值，有多个时为数组"),
 *                     )),
 *                 )))
 *         })
 *     )
 * )
 */
