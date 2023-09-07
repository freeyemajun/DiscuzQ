<?php
/**
 * @OA\Post(
 *     path="/api/v3/tom.delete",
 *     summary="删除扩展对象里的指定索引下数据",
 *     description="例如扩展对象里包含两个视频对象，索引为$1和$2,该接口可定向删除其中一个视频对象【官方安装包暂未使用该接口】",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "待删除的主题id",
 *         @OA\JsonContent(
 *             @OA\Property(property="threadId",type="integer",description="主题id"),
 *             @OA\Property(property="tomId",type="integer",description="扩展对象id"),
 *             @OA\Property(property="key",type="integer",description="对象所属索引标记"),
 *         )
 *     ),
 *     @OA\Response(response=200,description="返回删除结果",@OA\JsonContent(ref="#/components/schemas/dzq_layout"))
 * )
 */
