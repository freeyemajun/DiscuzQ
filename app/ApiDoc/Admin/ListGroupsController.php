<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/groups.list",
 *     summary = "获取用户组列表",
 *     description = "获取用户组列表",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\Response(response = 200, description = "返回用户组列表", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(
 *                  @OA\Property(property = "Data", type = "array", @OA\Items(type = "object", allOf = {
 *                      @OA\Schema(@OA\Property(property = "id", type = "integer", description = "用户组id")),
 *                      @OA\Schema(@OA\Property(property = "name", type = "string", description = "用户组名称")),
 *                      @OA\Schema(@OA\Property(property = "default", type = "boolean", description = "是否默认")),
 *                      @OA\Schema(@OA\Property(property = "checked", type = "integer", description = "是否推荐到首页")),
 *                      @OA\Schema(@OA\Property(property = "days", type = "integer", description = "付费获得天数")),
 *                      @OA\Schema(@OA\Property(property = "fee", type = "string", description = "收费金额")),
 *                      @OA\Schema(@OA\Property(property = "isCommission", type = "boolean", description = "是否可以收入提成")),
 *                      @OA\Schema(@OA\Property(property = "isDisplay", type = "boolean", description = "是否显示在用户名后")),
 *                      @OA\Schema(@OA\Property(property = "isPaid", type = "boolean", description = "是否收费：0不收费，1收费")),
 *                      @OA\Schema(@OA\Property(property = "scale", type = "string", description = "分成比例")),
 *                      @OA\Schema(@OA\Property(property = "type", type = "string", description = "类型")),
 *                      @OA\Schema(@OA\Property(property = "level", type = "integer",description = "付费组 等级")),
 *                      @OA\Schema(@OA\Property(property = "description", type = "string",description = "特权描述")),
 *                      @OA\Schema(@OA\Property(property = "notice", type = "string",description = "购买须知")),
 *
 *                  }))
 *          )
 *    }))
 * )
 */
