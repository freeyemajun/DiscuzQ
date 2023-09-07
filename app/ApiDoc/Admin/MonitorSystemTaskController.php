<?php
/**
 * @OA\Get(
 *     path = "/api/backAdmin/monitor/system/task",
 *     summary = "监听定时任务",
 *     description = "监听定时任务",
 *     tags = {"管理后台"},
 *     @OA\Parameter(ref = "#/components/parameters/bear_token_true"),
 *     @OA\Response(response = 200, description = "监听定时任务", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(
 *                  @OA\Property(property = "Data", type = "array", @OA\Items(type = "object", allOf = {
 *                  }))
 *          )
 *    }))
 * )
 */
