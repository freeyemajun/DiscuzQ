<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/reports",
 *     summary = "举报反馈列表",
 *     description = "举报反馈列表",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_page"),
 *     @OA\Parameter(ref = "#/components/parameters/threadlist_perPage"),
 *     @OA\Parameter(name = "filter[username]", in = "query", required = false, description = "用户名,举报人名称", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "filter[status]", in = "query", required = false, description = "举报状态,1:已处理 0：未处理", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "filter[type]", in = "query", required = false, description = "举报类型,0:个人主页 1:主题 2评论/回复", @OA\Schema(type = "integer")),
 *     @OA\Parameter(name = "filter[startTime]", in = "query", required = false, description = "开始时间", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "filter[endTime]", in = "query", required = false, description = "结束时间", @OA\Schema(type = "string")),
 *     @OA\Response(response = 200, description = "返回举报列表", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *              @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *              @OA\Schema(
 *                  @OA\Property(property = "pageData", type = "array", @OA\Items(type = "object", allOf = {
 *                      @OA\Schema(@OA\Property(property = "group", type = "object", description = "用户组信息", allOf = {
 *                           @OA\Schema(ref = "#/components/schemas/group_detail")
 *                      })),
 *                      @OA\Schema(@OA\Property(property = "report", type = "object", description = "举报信息", allOf = {
 *                           @OA\Schema(ref = "#/components/schemas/report_detail")
 *                      })),
 *                      @OA\Schema(@OA\Property(property = "user", type = "object", description = "举报人信息", allOf = {
 *                           @OA\Schema(@OA\Property(property = "userId", type = "integer", description = "用户id")),
 *                           @OA\Schema(@OA\Property(property = "userName", type = "string", description = "用户名"))
 *                      }))
 *                  }))
 *              )
 *         }))
 *    }))
 * )
 */
