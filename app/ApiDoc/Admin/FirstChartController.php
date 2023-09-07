<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/statistic/firstChart",
 *     summary = "数据看板",
 *     description = "数据看板",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token"),
 *     @OA\Parameter(name = "type", in = "query", required = true, description = "统计方式，1-日统计，2-周统计，3-月统计", @OA\Schema(type = "integer", enum = {1, 2, 3})),
 *     @OA\Parameter(name = "filter[createdAtBegin]", in = "query", required = false, description = "统计时间-起始", @OA\Schema(type = "string")),
 *     @OA\Parameter(name = "filter[createdAtEnd]", in = "query", required = false, description = "统计时间-截止", @OA\Schema(type = "string")),
 *     @OA\Response(response = 200, description = "返回看板内容", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *              @OA\Schema(@OA\Property(property = "threadData", type = "array", description = "每日发帖数", @OA\Items(type = "object", allOf = {
 *                  @OA\Schema(@OA\Property(property = "date", type = "string", description = "日期")),
 *                  @OA\Schema(@OA\Property(property = "count", type = "integer", description = "数量")),
 *              }))),
 *              @OA\Schema(@OA\Property(property = "postData", type = "array", description = "每日回帖数", @OA\Items(type = "object", allOf = {
 *                  @OA\Schema(@OA\Property(property = "date", type = "string", description = "日期")),
 *                  @OA\Schema(@OA\Property(property = "count", type = "integer", description = "数量")),
 *              }))),
 *              @OA\Schema(@OA\Property(property = "activeUserData", type = "array", description = "每日活跃用户数", @OA\Items(type = "object", allOf = {
 *                  @OA\Schema(@OA\Property(property = "date", type = "string", description = "日期")),
 *                  @OA\Schema(@OA\Property(property = "count", type = "integer", description = "数量")),
 *              }))),
 *              @OA\Schema(@OA\Property(property = "joinUserData", type = "array", description = "每日注册用户数", @OA\Items(type = "object", allOf = {
 *                  @OA\Schema(@OA\Property(property = "date", type = "string", description = "日期")),
 *                  @OA\Schema(@OA\Property(property = "count", type = "integer", description = "数量")),
 *              }))),
 *              @OA\Schema(@OA\Property(property = "overData", type = "object", description = "总统计项", allOf = {
 *                  @OA\Schema(@OA\Property(property = "over", type = "object", description = "日期", allOf = {
 *                      @OA\Schema(@OA\Property(property = "activeUserNumToday", type = "integer", description = "今日活跃用户数")),
 *                      @OA\Schema(@OA\Property(property = "addUserNumToday", type = "integer", description = "今日新增用户数")),
 *                      @OA\Schema(@OA\Property(property = "addThreadNumToday", type = "integer", description = "今日发帖数")),
 *                      @OA\Schema(@OA\Property(property = "addPostNumToday", type = "integer", description = "今日回帖数")),
 *                      @OA\Schema(@OA\Property(property = "totalUserNum", type = "integer", description = "用户总数量")),
 *                      @OA\Schema(@OA\Property(property = "totalThreadNum", type = "integer", description = "发帖总数量")),
 *                      @OA\Schema(@OA\Property(property = "totalPostNum", type = "integer", description = "回帖总数量")),
 *                      @OA\Schema(@OA\Property(property = "essenceThreadNum", type = "integer", description = "精华内容数"))
 *                  }))
 *              }))
 *         }))
 *    }))
 * )
 */