<?php
/**
 *
 * @OA\Post(
 *     path="/api/backAdmin/stopwords.list",
 *     summary="查询敏感词",
 *     description="查询敏感词",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "",
 *         @OA\JsonContent(
 *             @OA\Property(property="perPage",type="integer",description="每页数据条数"),
 *             @OA\Property(property="page",type="integer",description="页码"),
 *             @OA\Property(property="filter",type="object",description="过滤条件",
 *                  @OA\Property(property="keyword",type="string",description="内容")
 *              ),
 *         )
 *     ),
 *      @OA\Response(
 *         response=200,
 *         description="置顶提示",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *                    @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *                    @OA\Schema(@OA\Property(property = "pageData", type = "array", @OA\Items(type = "object", allOf = {
 *                          @OA\Schema(@OA\Property(property = "id", type = "integer", description = "过滤id")),
 *                          @OA\Schema(@OA\Property(property = "userId", type = "integer", description = "用户id")),
 *                          @OA\Schema(@OA\Property(property = "ugc", type = "string",description = "ugc的处理方式，忽略 {IGNORE}、审核 {MOD}、禁止 {BANNED}、替换 {REPLACE}")),
 *                          @OA\Schema(@OA\Property(property = "username", type = "string", description = "用户名的处理方式")),
 *                          @OA\Schema(@OA\Property(property = "nickname", type = "string", description = "昵称的处理方式")),
 *                          @OA\Schema(@OA\Property(property = "signature", type = "string", description = "签名的处理方式")),
 *                          @OA\Schema(@OA\Property(property = "dialog", type = "string", description = "短消息处理方式")),
 *                          @OA\Schema(@OA\Property(property = "find", type = "string", description = "敏感词")),
 *                          @OA\Schema(@OA\Property(property = "replacement", type = "string",description = "替换词或替换规则")),
 *                          @OA\Schema(@OA\Property(property = "createdAt", type = "string",format="datetime",default="2021-01-02 02:22:22", description = "创建时间")),
 *                          @OA\Schema(@OA\Property(property = "updatedAt", type = "string",format="datetime",default="2021-01-02 02:22:22", description = "更新时间")),
 *                    })))
 *              }))
 *         })
 *     )
 * )
 *
 */
