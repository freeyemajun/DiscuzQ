<?php
/**
 * @OA\Get(
 *     path="/api/v3/offiaccount/jssdk",
 *     summary="获得jssdk数据",
 *     description="获得jssdk数据",
 *     tags={"微信相关"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(name="url",
 *          in="query",
 *          required=true,
 *          description = "主题id",
 *          @OA\Schema(type="integer")
 *      ),
 *     @OA\Response(
 *          response=200,
 *          description="返回更新结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *                  @OA\Schema(@OA\Property(property="Data",type="object",
 *                      @OA\Property(property="debug",type="boolean",description = "是否调试"),
 *                      @OA\Property(property="beta",type="boolean",description = "是否beta版"),
 *                      @OA\Property(property="jsApiList",type="array",description = "js的接口列表",@OA\Items(type="string")),
 *                      @OA\Property(property="openTagList",type="array",description = "开放标签列表",@OA\Items(type="string")),
 *                      @OA\Property(property="appId",type="string",description = ""),
 *                      @OA\Property(property="nonceStr",type="string",description = "随机字符串"),
 *                      @OA\Property(property="timestamp",type="integer",description = "时间戳"),
 *                      @OA\Property(property="url",type="string",description = "传过来的url"),
 *                      @OA\Property(property="signature",type="string",description = "签名"),
 *                  ))
 *          }))
 * )
 */
