<?php
/**
 *
 * @OA\Post(
 *     path="/api/backAdmin/notification/tpl",
 *     summary="通知设置",
 *     description="通知设置",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "",
 *         @OA\JsonContent(
 *             @OA\Property(property="perPage",type="integer",description="每页数据条数"),
 *             @OA\Property(property="page",type="integer",description="页码"),
 *         )
 *     ),
 *      @OA\Response(
 *         response=200,
 *         description="返回结果",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *                    @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *                    @OA\Schema(@OA\Property(property = "pageData", type = "array", @OA\Items(type = "object", allOf = {
 *                          @OA\Schema(@OA\Property(property = "name", type = "integer", description = "通知名称")),
 *                          @OA\Schema(@OA\Property(property = "typeStatus", type = "object", description = "类型状态",
 *                              @OA\Property(property = "id", type = "integer",description = "类型id"),
 *                              @OA\Property(property = "status", type = "integer",description = "模板状态:1开启0关闭"),
 *                              @OA\Property(property = "type", type = "string",description = "通知类型:0系统1微信2短信"),
 *                              @OA\Property(property = "isError", type = "integer",description = "模板是否配置错误"),
 *                              @OA\Property(property = "errorMsg", type = "integer",description = "错误信息"),
 *                          )),
 *                    })))
 *              }))
 *         })
 *     )
 * )
 *
 */

