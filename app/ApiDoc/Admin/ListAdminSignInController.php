<?php
/**
 * @OA\Get(
 *     path="/api/backAdmin/signinfields.list",
 *     summary="注册与登录",
 *     description="查询注册扩展列表",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token_true"),
 *     @OA\Response(
 *        response=200,
 *        description="出参",
 *        @OA\JsonContent(allOf={
 *            @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *            @OA\Schema(
 *                @OA\Property(property="Data",type="array",@OA\Items(type = "object",
 *                    @OA\Property(property="id",type="integer",description="用户组 id"),
 *                    @OA\Property(property="name",type="string",description="用户端显示的字段名称"),
 *                    @OA\Property(property="type",type="integer",description="状态(0:单行文本框 1:多行文本框 2:单选 3:复选 4:图片上传 5:附件上传)"),
 *                    @OA\Property(property="fieldsExt",type="string",description="字段扩展信息，Json表示选项内容"),
 *                    @OA\Property(property="fieldsDesc",type="string",description="字段介绍"),
 *                    @OA\Property(property="sort",type="integer",description="自定义显示顺序"),
 *                    @OA\Property(property="status",type="integer",description="状态(-1:未启用 0:删除 1：启用)"),
 *                    @OA\Property(property="required",type="integer",description="是否必填项(0:否 1:是)"),
 *                    @OA\Property(property="typeDesc",type="number",description="状态描述"),
 *                ))
 *            )
 *        })
 *     )
 * )
 */
