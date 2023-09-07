<?php
/**
 *
 * @OA\Post(
 *     path="/api/backAdmin/users",
 *     summary="用户筛选",
 *     description="用户筛选",
 *     tags={"管理后台"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "",
 *         @OA\JsonContent(
 *             @OA\Property(property="perPage",type="integer",description="每页数据条数"),
 *             @OA\Property(property="page",type="integer",description="页码"),
 *             @OA\Property(property="filter",type="array",description="过滤条件",@OA\Items(type="object",
 *                  @OA\Property(property="id",type="integer",description="用户 id"),
 *                  @OA\Property(property="username",type="string",description="用户名：多个用户名用半角逗号隔开用户名前或后加星号可使用模糊搜索"),
 *                  @OA\Property(property="nickname",type="string",description="昵称搜索"),
 *                  @OA\Property(property="mobile",type="string",description="用户手机号"),
 *                  @OA\Property(property="status",type="integer",enum={0,1,2,3,4}, description="0(normal正常)1(ban禁用)2(mod审核中)3(through审核通过)3(refuse审核拒绝)4(ignore审核忽略)"),
 *                  @OA\Property(property="groupId",type="array",description="用户组",@OA\Items(type="integer")),
 *                  @OA\Property(property="isReal",type="string",enum={"yes","no"}, description="是否实名认证（yes/no）"),
 *                  @OA\Property(property="wechat",type="string",enum={"yes","no"}, description="是否绑定微信（yes/no）"),
 *              )),
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
 *                          @OA\Schema(@OA\Property(property = "userId", type = "integer", description = "用户id")),
 *                          @OA\Schema(@OA\Property(property = "username", type = "string", description = "用户名")),
 *                          @OA\Schema(@OA\Property(property = "nickname", type = "string", description = "昵称")),
 *                          @OA\Schema(@OA\Property(property = "mobile", type = "string", description = "电话")),
 *                          @OA\Schema(@OA\Property(property = "avatarUrl", type = "string", description = "头像")),
 *                          @OA\Schema(@OA\Property(property = "threadCount", type = "integer", description = "发表主题数")),
 *                          @OA\Schema(@OA\Property(property = "status", type = "integer", enum={0,1,2,3,4},description = "用户状态：0正常 1禁用 2审核中 3审核拒绝 4审核忽略")),
 *                          @OA\Schema(@OA\Property(property = "createdAt", type = "string",format="datetime",default="2021-01-02 02:22:22", description = "创建时间")),
 *                          @OA\Schema(@OA\Property(property = "updatedAt", type = "string",format="datetime",default="2021-01-02 02:22:22",description = "更新时间")),
 *                          @OA\Schema(@OA\Property(property = "groupName", type = "string", description = "用户组名")),
 *                          @OA\Schema(@OA\Property(property = "expirationTime", type = "string", description = "付费到期时间")),
 *                          @OA\Schema(@OA\Property(property = "extFields", type = "array", description = "扩展字段",@OA\Items(type="object",
 *                              @OA\Property(property = "id", type = "integer", description = "扩展字段id"),
 *                               @OA\Property(property = "userId", type = "integer", description = "用户id"),
 *                               @OA\Property(property = "name", type = "string", description = "用户端显示的字段名称"),
 *                               @OA\Property(property = "type", type = "integer", enum={0,1,2,3,4,5}, description = "0:单行文本框 1:多行文本框 2:单选 3:复选 4:图片上传 5:附件上传"),
 *                               @OA\Property(property = "fieldsExt", type = "string", description = "字段扩展信息，Json表示选项内容"),
 *                               @OA\Property(property = "fieldsDesc", type = "string", description = "字段介绍"),
 *                               @OA\Property(property = "remark", type = "string", description = "审核意见"),
 *                               @OA\Property(property = "sort", type = "integer", description = "自定义显示顺序"),
 *                               @OA\Property(property = "status", type = "integer", enum={0,1,2,3}, description = "0:废弃 1:待审核 2:驳回 3:审核通过"),
 *                               @OA\Property(property = "required", type = "integer", enum={0,1},description = "是否必填项 0:否 1:是"),
 *                          )))
 *                    })))
 *              }))
 *         })
 *     )
 * )
 *
 */
