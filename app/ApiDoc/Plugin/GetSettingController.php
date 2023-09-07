<?php
/**
 * @OA\Get(
 *     path="/api/v3/plugin/settinginfo",
 *     summary="插件配置详情",
 *     description="查询当前插件的所有配置信息",
 *     tags={"插件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *         name="appId",
 *         in="query",
 *         required=true,
 *         description = "插件应用id",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="返回设置详情",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *             @OA\Schema(@OA\Property(property = "Data", type = "array",@OA\Items(
 *                 @OA\Property(property="setting",type="object",description="插件管理后台配置",
 *                   @OA\Property(property="id",type="integer", description="插件设置表id"),
 *                   @OA\Property(property="appId",type="string", description="插件appid"),
 *                   @OA\Property(property="appName",type="string", description="插件name_en"),
 *                   @OA\Property(property="type",type="integer", description="插件类型"),
 *                   @OA\Property(property="publicValue",type="object", description="公开的配置（里面的内容插件开发者自行定义）"),
 *                   @OA\Property(property="privateValue",type="object", description="私密的配置（里面的内容插件开发者自行定义）")
 *                  ),
 *                 @OA\Property(property="config",type="object",description="插件目录开发者定义的配置文件"),
 *             )))
 *         }))
 *     )
 * )
 */
