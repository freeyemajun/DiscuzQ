<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/siteinfo",
 *     summary = "获取站点信息",
 *     description = "获取站点信息",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\Response(response = 200, description = "返回站点信息", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *                @OA\Schema(@OA\Property(property = "siteinfo", type = "object", description = "站点信息项", allOf = {
 *                           @OA\Schema(ref = "#/components/schemas/site_info")
 *                      })),
 *                @OA\Schema(@OA\Property(property = "unapproved", type = "object", description = "审核项", allOf = {
 *                           @OA\Schema(ref = "#/components/schemas/unapproved_info")
 *                      }))
 *         }))
 *    }))
 * )
 */
