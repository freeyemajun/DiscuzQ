<?php
/**
 * @OA\Post(
 *     path="/api/v3/threads/operate",
 *     summary="帖子操作",
 *     description="帖子操作接口",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "帖子操作",
 *         @OA\JsonContent(
 *             @OA\Property(property="id",type="number",description="帖子id"),
 *             @OA\Property(property="isSticky",type="boolean",description="置顶"),
 *             @OA\Property(property="isEssence",type="boolean",description="加精"),
 *             @OA\Property(property="isFavorite",type="boolean",description="收藏"),
 *             @OA\Property(property="isDeleted",type="boolean",description="删除"),
 *             )
 *           ),
 * @OA\Response(response=200,
 *        description="帖子操作接口返回",
 *        @OA\JsonContent(allOf ={
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(title="帖子操作返回数据",description="帖子操作返回数据",@OA\Property(property="Data",type="object",
 *                @OA\Property(property="address", type="string", description="地址"),
 *                @OA\Property(property="attachmentPrice",type="number",description="附件价格"),
 *                @OA\Property(property="categoryId",type="integer",description="分类id"),
 *                @OA\Property(property="createdAt",type = "string",format="datetime", description = "创建时间"),
 *                @OA\Property(property="deletedAt",type="string",format="datetime", description = "删除时间"),
 *                @OA\Property(property="deletedUserId",type = "integer", description = "删除者"),
 *                @OA\Property(property="freeWords",type="integer", description ="属性名称待定"),
 *                @OA\Property(property="id",type="integer", description = "帖子id"),
 *                @OA\Property(property="isAnonymous",type="boolean",description = "是否匿名"),
 *                @OA\Property(property="isApproved",type = "boolean", description = "是否审核"),
 *                @OA\Property(property="isDisplay",type="boolean", description = "是否展示"),
 *                @OA\Property(property="isDraft",type="integer", description = "是否草稿"),
 *                @OA\Property(property="isEssence",type="boolean", description = "是否加精"),
 *                @OA\Property(property="isRedPacket",type="integer", description = "是否红包帖"),
 *                @OA\Property(property="isSite",type="integer", description = "待定"),
 *                @OA\Property(property="isSticky",type="boolean", description = "是否置顶"),
 *                @OA\Property(property="issueAt",type="string",format="datetime", description = "变更时间"),
 *                @OA\Property(property="lastPostedUserId",type="integer", description = "最后一次评论用户id"),
 *                @OA\Property(property="latitude",type="number", description = "纬度"),
 *                @OA\Property(property="location",type="string", description = "位置"),
 *                @OA\Property(property="longitude",type="number", description = "精度"),
 *                @OA\Property(property="paidCount",type="integer", description = "支付数量"),
 *                @OA\Property(property="postCount",type="integer", description = "评论数"),
 *                @OA\Property(property="postedAt",type="integer", description = "待定"),
 *                @OA\Property(property="price",type="number", description = "金额"),
 *                @OA\Property(property="rewardedCount",type="number", description = "奖励金额"),
 *                @OA\Property(property="shareCount",type="integer", description = "分享数"),
 *                @OA\Property(property="source",type="integer", description = "来源"),
 *                @OA\Property(property="title",type="string", description = "帖子标题"),
 *                @OA\Property(property="type",type="integer", description = "帖子类型"),
 *                @OA\Property(property="updatedAt",type="string",format="datetime", description = "更新时间"),
 *                @OA\Property(property="userId",type="integer", description = "用户id"),
 *                @OA\Property(property="viewCount",type="integer", description = "浏览数")
 *
 * ))
 *
 *
 *     })
 *     )
 * )
 *        )
 *     )

 */
