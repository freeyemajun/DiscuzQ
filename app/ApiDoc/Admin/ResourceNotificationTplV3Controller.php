<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/notification/tpl/detail",
 *     summary="通知模板详情列表",
 *     description="通知模板详情列表",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "",
 *         @OA\JsonContent(
 *             @OA\Property(property="typeName",type="integer",description="类型名称，如：新用户注册通知"),
 *             @OA\Property(property="type",type="integer",description="通知类型,0系统1微信2短信"),
 *         )
 *     ),
 *     @OA\Response(
 *          response=200,
 *          description="返回更新结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *                  @OA\Schema(@OA\Property(property="Data",type="array",@OA\Items(type="object",allOf={
 *                              @OA\Schema(ref="#/components/schemas/dzq_notification_tpls_model"),
 *                              @OA\Schema(@OA\Property(property="templateVariables",type="object",description = "参数-描述")),
 *                          })))
 *          }))
 * )
 */
