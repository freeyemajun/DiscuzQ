<?php
/**
 * @OA\Post(
 *     path="/api/v3/goods/analysis",
 *     summary="商品解析",
 *     description="可解析部分电商url链接",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(required=true,description = "待解析地址",@OA\JsonContent(
 *         @OA\Property(property="address",type="string",description="商品地址"),
 *     )),
 *     @OA\Response(response=200,description="返回点赞/支付的用户列表",@OA\JsonContent(allOf={
 *         @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *         @OA\Schema(@OA\Property(property="Data",type="object",
 *             @OA\Property(property="createdAt",type="string",description="创建时间"),
 *             @OA\Property(property="detailContent",type="string",description="解析详情页地址"),
 *             @OA\Property(property="id",type="string",description="商品地址"),
 *             @OA\Property(property="imagePath",type="string",description="商品封面图"),
 *             @OA\Property(property="platformId",type="string",description="平台商品 id"),
 *             @OA\Property(property="postId",type="string",description="帖子id"),
 *             @OA\Property(property="price",type="string",description="价格"),
 *             @OA\Property(property="readyContent",type="string",description="预解析内容"),
 *             @OA\Property(property="status",type="string",enum={0,1}, description="商品状态:0正常 1失效/下架"),
 *             @OA\Property(property="title",type="string",description="商品标题"),
 *             @OA\Property(property="type",type="string",enum={0,1,2}, description="商品来源:0淘宝 1天猫 2京东 等"),
 *             @OA\Property(property="updatedAt",type="string",description="更新时间"),
 *             @OA\Property(property="userId",type="string",description="用户id"),
 *        ))
 *     }))
 * )
 */
