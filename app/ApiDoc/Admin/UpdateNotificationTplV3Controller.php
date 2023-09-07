<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/notification/tpl/update",
 *     summary="编辑通知模板",
 *     description="编辑通知模板",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "",
 *         @OA\JsonContent(
 *                  @OA\Property(property="data",type="array", @OA\Items(type="object",
 *                      @OA\Property(property="id",type="integer",description="通知id"),
 *                      @OA\Property(property="status",type="integer",description="状态1开启 0关闭"),
 *                      @OA\Property(property="notice_id",type="integer",description="模板唯一标识ID"),
 *                      @OA\Property(property="title",type="string",description="标题"),
 *                      @OA\Property(property="content",type="string",description="内容"),
 *                      @OA\Property(property="template_id",type="string",description="模板ID"),
 *                      @OA\Property(property="firstData",type="string",description="first.DATA"),
 *                      @OA\Property(property="keywordsData",type="string",description="keywords.DATA"),
 *                      @OA\Property(property="remarkData",type="string",description="remark.DATA"),
 *                      @OA\Property(property="color",type="string",description="data color"),
 *                      @OA\Property(property="redirectType",type="integer",description="跳转类型0无跳转 1跳转H5 2跳转小程序"),
 *                      @OA\Property(property="redirectUrl",type="integer",description="跳转地址"),
 *                      @OA\Property(property="pagePath",type="integer",description="跳转路由"),
 *                  )),
 *         )
 *     ),
 *     @OA\Response(
 *          response=200,
 *          description="返回更新结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *                  @OA\Schema(@OA\Property(property="Data",type="object",
 *                      @OA\Property(property="key",type="object", description="key是通知id", allOf={
 *                              @OA\Schema(ref="#/components/schemas/dzq_notification_tpls_model"),
 *                          }),
 *                  ))
 *          }))
 * )
 */
