<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/stopwords.batch",
 *     summary="创建/修改敏感词接口(批量)",
 *     description="创建/修改敏感词接口(批量)",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "",
 *         @OA\JsonContent(
 *             @OA\Property(property="words",type="array",description="数组中每个元素都是按敏感词批量插入规则编写的字符串",
 *                  @OA\Items(type="string",default="士大夫=**|{IGNORE}|{IGNORE}|{IGNORE}",description="「敏感词」=「替换词（或处理方式）」 ; 忽略 {IGNORE}、审核 {MOD}、禁止 {BANNED}、替换 {REPLACE}")
 *              ),
 *             @OA\Property(property="overwrite",type="boolean",description="是否覆盖原有敏感词"),
 *         )
 *     ),
 *     @OA\Response(
 *          response=200,
 *          description="返回更新结果",
 *          @OA\JsonContent(
 *              allOf={
 *                  @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *                  @OA\Schema(@OA\Property(property="Data",type="object",
 *                      @OA\Property(property="type",type="string",default="stop-words", description = "数据类型"),
 *                      @OA\Property(property="created",type="integer",description = "创建数量"),
 *                      @OA\Property(property="updated",type="integer",description = "修改数量"),
 *                      @OA\Property(property="unique",type="integer",description = "重复数量")
 *                  ))
 *          }))
 * )
 */
