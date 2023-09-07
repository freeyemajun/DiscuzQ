<?php
/**
 *
 * @OA\Get(
 *     path="/api/v3/thread.stick",
 *     summary="置顶主题列表",
 *     description="获取首页置顶主题列表",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *         name = "categoryIds",
 *         in = "query",
 *         required = false,
 *         description = "分类id",
 *         @OA\Schema(type="array", @OA\Items(type = "integer"))
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="返回置顶列表",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(@OA\Property(property="code",type="string",description="dzq错误码")),
 *             @OA\Schema(@OA\Property(property="Message",type="string",description="错误描述信息")),
 *             @OA\Schema(@OA\Property(property="RequestId",type="string",description="请求ID")),
 *             @OA\Schema(@OA\Property(property="RequestTime",format="datetime",default="2021-02-02 02:22:22", type="string",description="请求时间")),
 *             @OA\Schema(@OA\Property(property="Data", type="array",
 *                 @OA\Items(type="object",
 *                     @OA\Property(property="threadId", type="number",description="帖子id"),
 *                     @OA\Property(property="categoryId",default=1,type="number",description="分类id"),
 *                     @OA\Property(property="title", type="string",description="帖子标题"),
 *                     @OA\Property(property="updatedAt",format="datetime",default="2021-02-02 02:22:22", type="string",description="更新时间"),
 *                     @OA\Property(property="canViewPosts", type="boolean",description="是否可查阅详情"),
 *                     @OA\Property(property="sort", type="integer",description="排序")
 *                  )
 *             ))
 *         })
 *     )
 * )
 *
 */
