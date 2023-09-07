<?php
/**
 *@OA\Get(
 *    path = "/api/v3/emoji",
 *    summary = "表情列表",
 *    description = "Discuz! Q 表情列表",
 *    tags ={"发布与展示"},
 *@OA\Parameter(ref = "#/components/parameters/bear_token"),
 *
 * @OA\Response(
 *        response = 200,
 *        description = "返回表情列表",
 *        @OA\JsonContent(allOf ={
 *                @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *                @OA\Schema(title="表情列表", description="表情列表",@OA\Property(property = "Data", type = "array", description="表情列表", @OA\Items(type = "object",
 *                      @OA\Property(property = "id", type = "integer", description = "表情id"),
 *                      @OA\Property(property = "category", type = "string", description = "表情类型", default = "qq"),
 *                      @OA\Property(property = "code", type = "string", description = "表情编码", default = ":weixiao:"),
 *                      @OA\Property(property = "createdAt", type = "string", format="datetime", description = "创建时间", default = "2021-08-09T12:16:18.000000Z"),
 *                      @OA\Property(property = "order", type = "integer", description = "序号", default = 1),
 *                      @OA\Property(property = "updatedAt", type = "string", format="datetime", description = "更新时间", default = "2021-08-09T12:16:18.000000Z"),
 *                      @OA\Property(property = "url", type = "string", description = "表情地址", default = "https://discuz.chat/emoji/qq/weixiao.gif")
 *              )))
 *            }
 *        )
 *    )
 *)
 *
 *
 */

