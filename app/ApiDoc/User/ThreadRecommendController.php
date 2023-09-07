<?php
/**
 * @OA\Get(
 *     path="/api/v3/thread.recommends",
 *     summary="推荐的精华帖列表",
 *     description="推荐的精华帖列表",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(name="perPage",in="query",required=true,description = "预期返回的主体个数",@OA\Schema(type="integer")),
 *     @OA\Response(response=200,description="返回推荐的内容",@OA\JsonContent(allOf={
 *         @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *         @OA\Schema(@OA\Property(property="Data",type="object",@OA\Property(property="displayTag",
 *             @OA\Property(property="isPrice",type="boolean",description="付费贴"),
 *             @OA\Property(property="isEssence",type="boolean",description="精华帖"),
 *             @OA\Property(property="isRedPack",type="boolean",description="红包贴"),
 *             @OA\Property(property="isReward",type="boolean",description="悬赏贴"),
 *             @OA\Property(property="isVote",type="boolean",description="投票贴"),
 *         ),
 *         @OA\Property(property="threadId",type="integer",description="帖子id"),
 *         @OA\Property(property="categoryId",type="integer",description="分类id"),
 *         @OA\Property(property="title",type="string",description="标题"),
 *         @OA\Property(property="viewCount",type="integer",description="浏览数"),
 *         )),
 *     }))
 * )
 */
