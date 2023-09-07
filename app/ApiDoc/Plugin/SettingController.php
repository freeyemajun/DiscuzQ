<?php
/**
 * @OA\Post(
 *     path="/api/backAdmin/plugin/settings.save",
 *     summary="插件设置面板",
 *     description="单个插件应用的所有配置信息",
 *     tags={"插件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="appId",type="string",description="插件应用id"),
 *             @OA\Property(property="appName",type="string",description="插件英文名称"),
 *             @OA\Property(property="type",type="integer",description="插件类型 1：主体贴插件"),
 *             @OA\Property(property="privateValue",type="object",description="（私密配置）配置字段的kv数组"),
 *             @OA\Property(property="publicValue",type="object",description="（公开配置）配置字段的kv数组"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="设置成功",
 *         @OA\JsonContent(ref="#/components/schemas/dzq_layout")
 *     )
 * )
 */
