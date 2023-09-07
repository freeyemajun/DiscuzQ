<?php
/**
 * @OA\Get(
 *     path="/api/v3/view.count",
 *     summary="记录主题查看次数",
 *     description="调用一次接口增加一次查看次数",
 *     tags={"发布与展示"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(name="threadId",
 *          in="query",
 *          required=true,
 *          description = "主题id",
 *          @OA\Schema(type="integer")),
 *     @OA\Response(
 *          response=200,
 *          description="返回更新结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *                  @OA\Schema(@OA\Property(property="Data",type="object",
 *                      @OA\Property(property="viewCount",type="integer",description = "查看次数")
 *                  ))
 *          }))
 * )
 */
