<?php
/**
 * @OA\Get(
 *     path = "/api/v3/upgrade.group",
 *     summary = "用户组升级列表",
 *     description = "获取付费用户组列表",
 *     tags = {"个人中心"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Response(
 *        response = 200,
 *        description = "获取付费用户组列表",
 *        @OA\JsonContent(allOf = {
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(@OA\Property(property = "Data", type = "array", @OA\Items(
 *                      @OA\Property(property = "name", type = "string", description = "用户组名称"),
 *                      @OA\Property(property = "color", type = "string", description = "用户组颜色"),
 *                      @OA\Property(property = "icon", type = "string", description = "用户组icon"),
 *                      @OA\Property(property = "fee", type = "number", description = "用户组费用"),
 *                      @OA\Property(property = "level", type = "integer", description = "用户组等级"),
 *                      @OA\Property(property = "days", type = "integer", description = "用户组有效天数"),
 *                      @OA\Property(property = "description", type = "string", description = "用户组特权描述"),
 *                      @OA\Property(property = "notice", type = "string", description = "购买须知"),
 *                      @OA\Property(property = "button", type = "integer", description = "展示按钮；0：不展示、1：续费、2：升级"),
 *
 *                )))
 *            }
 *        )
 *     )
 * )
 */
