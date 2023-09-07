<?php
/**
 * @OA\Get(
 *     path="/api/v3/plugin/list",
 *     summary="插件列表",
 *     description="获取站点已安装插件信息",
 *     tags={"插件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Response(response=200,description="返回取消报名结果",@OA\JsonContent(allOf={
 *         @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *         @OA\Schema(@OA\Property(property = "Data", type = "array",@OA\Items(
 *             @OA\Property(property="name_cn",type="string",description="插件中文名称"),
 *             @OA\Property(property="name_en",type="string",description="插件英文名称"),
 *             @OA\Property(property="description",type="string",description="插件描述内容"),
 *             @OA\Property(property="type",type="integer",description="1:帖子插件 2：xxx"),
 *             @OA\Property(property="app_id",type="string",description="插件唯一id"),
 *             @OA\Property(property="version",type="string",description="版本号"),
 *             @OA\Property(property="status",type="integer",enum={0,1}, description="0：禁用 1：启用"),
 *             @OA\Property(property="icon",type="string", description="插件应用图标地址"),
 *             @OA\Property(property="author",type="object", description="作者信息",
 *                 @OA\Property(property="name",type="string", description="作者名称"),
 *                 @OA\Property(property="email",type="string", description="作者联系邮件")
 *             ),
 *             @OA\Property(property="authority",type="object", description="权限设置",
 *                 @OA\Property(property="title",type="string", description="权限名称"),
 *                 @OA\Property(property="permission",type="string", description="权限字段名称"),
 *                 @OA\Property(property="canUsePlugin",type="boolean", description="是否可以使用插件")
 *             ),
 *             @OA\Property(property="plugin_trigger",type="string", description="触发前端插件的js文件"),
 *             @OA\Property(property="setting",type="object", description="管理后台设置",
 *                 @OA\Property(property="id",type="integer", description="插件设置表id"),
 *                 @OA\Property(property="appId",type="string", description="插件appid"),
 *                 @OA\Property(property="appName",type="string", description="插件name_en"),
 *                 @OA\Property(property="type",type="integer", description="插件类型"),
 *                 @OA\Property(property="publicValue",type="object", description="公开的配置（里面的内容插件开发者自行定义）"),
 *                 @OA\Property(property="privateValue",type="object", description="私密的配置（里面的内容插件开发者自行定义）")
 *             ),
 *         )))
 *     }))
 * )
 */
