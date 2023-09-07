<?php
/**
 * https://editor.swagger.io/
 * https://petstore.swagger.io/
 * https://github.com/zircote/swagger-php
 *
 * https://zircote.github.io/swagger-php/Getting-started.html#installation
 *
 * https://packagist.org/packages/zircote/swagger-php
 * https://hub.docker.com/r/swaggerapi/swagger-ui
 * https://hub.docker.com/r/swaggerapi/swagger-editor
 *
 * @OA\Server(url="https://discuz.chat",description="Discuz! Q 官方网站")
 * @OA\Server(url="https://www.techo.chat",description="Discuz! Q 体验站")
 *
 * @OA\Info(
 *     title="Discuz! Q后台接口文档",
 *     version="3.0",
 *     description="本文档适用于对Discuz! Q进行二开的用户参考使用",
 *     termsOfService="https://gitee.com/Discuz/Discuz-Q",
 *     @OA\Contact(email="coralchu@tencent.com"),
 *     @OA\License(name="Apache 2.0",url="https://discuz.com/docs")
 * )
 * @OA\Tag(
 *     name="发布与展示",
 *     description="主题和评论相关接口",
 *     @OA\ExternalDocumentation(
 *          description="Discuz! Q官方网站",
 *          url="http://discuz.chat"
 *     )
 * )
 * @OA\Tag(
 *     name="注册登录",
 *     description="登录注册相关接口",
 *     @OA\ExternalDocumentation(
 *          description="Discuz! Q官方网站",
 *          url="http://discuz.chat"
 *     )
 * )
 * @OA\Tag(
 *     name="个人中心",
 *     description="个人中心相关接口",
 *     @OA\ExternalDocumentation(
 *          description="Discuz! Q官方网站",
 *          url="http://discuz.chat"
 *     )
 * )
 * @OA\Tag(
 *     name="支付钱包",
 *     description="钱包相关接口",
 *     @OA\ExternalDocumentation(
 *          description="Discuz! Q官方网站",
 *          url="http://discuz.chat"
 *     )
 * )
 * @OA\Tag(
 *     name="管理后台",
 *     description="管理后台相关接口",
 *     @OA\ExternalDocumentation(
 *          description="Discuz! Q官方网站",
 *          url="http://discuz.chat"
 *     )
 * )
 * @OA\Tag(
 *     name="私信与消息",
 *     description="用户私信、消息通知相关接口",
 *     @OA\ExternalDocumentation(
 *          description="Discuz! Q官方网站",
 *          url="http://discuz.chat"
 *     )
 * )
 * @OA\Tag(
 *     name="附件",
 *     description="附件相关接口",
 *     @OA\ExternalDocumentation(
 *          description="Discuz! Q官方网站",
 *          url="http://discuz.chat"
 *     )
 * )
 * @OA\Tag(
 *     name="邀请",
 *     description="邀请件相关接口",
 *     @OA\ExternalDocumentation(
 *          description="Discuz! Q官方网站",
 *          url="http://discuz.chat"
 *     )
 * )
 *  @OA\Tag(
 *     name="插件",
 *     description="插件相关接口【未开放】",
 *     @OA\ExternalDocumentation(
 *          description="Discuz! Q官方网站",
 *          url="http://discuz.chat"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="dzq_layout",
 *     title="接口返回",
 *     @OA\Property(property="Code",type="object",description="dzq错误码",oneOf={
 *                     	   @OA\Schema(type="string",title="0",description="返回成功"),
 *                     	   @OA\Schema(type="string",title="-10001",description="当前站点未安装"),
 *                     	   @OA\Schema(type="string",title="-2001",description="无效未知url地址"),
 *                     	   @OA\Schema(type="string",title="-2002",description="无效配置"),
 *                     	   @OA\Schema(type="string",title="-2003",description="运行时异常"),
 *                         @OA\Schema(type="string",title="-2004",description="无效参数"),
 *                         @OA\Schema(type="string",title="-3001",description="跳转到登录页"),
 *                         @OA\Schema(type="string",title="-3002",description="跳转到注册页"),
 *                         @OA\Schema(type="string",title="-3003",description="跳转到审核页"),
 *                         @OA\Schema(type="string",title="-3004",description="跳转到首页"),
 *                         @OA\Schema(type="string",title="-3005",description="站点已关闭"),
 *                         @OA\Schema(type="string",title="-3006",description="跳转到站点付费页"),
 *                         @OA\Schema(type="string",title="-3007",description="跳转到扩展字段页"),
 *                         @OA\Schema(type="string",title="-4001",description="参数错误"),
 *                         @OA\Schema(type="string",title="-4002",description="没有权限、没有访问扩展字段的权限"),
 *                         @OA\Schema(type="string",title="-4003",description="资源已存在"),
 *                         @OA\Schema(type="string",title="-4004",description="资源不存在"),
 *                         @OA\Schema(type="string",title="-4005",description="资源被占用"),
 *                         @OA\Schema(type="string",title="-4006",description="内容被禁用"),
 *                         @OA\Schema(type="string",title="-4007",description="审核不通过"),
 *                         @OA\Schema(type="string",title="-4008",description="忽略审核"),
 *                         @OA\Schema(type="string",title="-4009",description="用户已被封禁"),
 *                         @OA\Schema(type="string",title="-4010",description="资源已过期"),
 *                         @OA\Schema(type="string",title="-4011",description="无效token"),
 *                         @OA\Schema(type="string",title="-5001",description="网络错误"),
 *                         @OA\Schema(type="string",title="-5002",description="内部系统错误"),
 *                         @OA\Schema(type="string",title="-5003",description="数据库错误"),
 *                         @OA\Schema(type="string",title="-5004",description="外部接口错误"),
 *                         @OA\Schema(type="string",title="-5005",description="敏感词校验未通过"),
 *                         @OA\Schema(type="string",title="-6001",description="未知错误"),
 *                         @OA\Schema(type="string",title="-6002",description="调试错误"),
 *                         @OA\Schema(type="string",title="-7001",description="二维码已失效，扫码超时"),
 *                         @OA\Schema(type="string",title="-7002",description="扫码中"),
 *                         @OA\Schema(type="string",title="7003",description="扫码失败，请重新扫码"),
 *                         @OA\Schema(type="string",title="-7004",description="SESSION TOKEN过期"),
 *                         @OA\Schema(type="string",title="-7005",description="未找到用户"),
 *                         @OA\Schema(type="string",title="-7006",description="未找到微信用户"),
 *                         @OA\Schema(type="string",title="-7007",description="扫码登录失败"),
 *                         @OA\Schema(type="string",title="-7008",description="生成二维码参数类型错误"),
 *                         @OA\Schema(type="string",title="-7009",description="全局token获取失败"),
 *                         @OA\Schema(type="string",title="-7010",description="小程序二维码生成失败"),
 *                         @OA\Schema(type="string",title="-7011",description="绑定失败"),
 *                         @OA\Schema(type="string",title="-7012",description="生成scheme失败"),
 *                         @OA\Schema(type="string",title="-7013",description="解密邀请码失败"),
 *                         @OA\Schema(type="string",title="-7014",description="注册邀请码失败"),
 *                         @OA\Schema(type="string",title="-7016",description="需要绑定或注册用户"),
 *                         @OA\Schema(type="string",title="-7017",description="换绑失败"),
 *                         @OA\Schema(type="string",title="-7031",description="手机号已被绑定"),
 *                         @OA\Schema(type="string",title="-7032",description="站点关闭注册"),
 *                         @OA\Schema(type="string",title="-7033",description="注册类型错误"),
 *                         @OA\Schema(type="string",title="-7034",description="不可以使用相同的密码"),
 *                         @OA\Schema(type="string",title="-7035",description="请验证旧的手机号"),
 *                         @OA\Schema(type="string",title="-7036",description="请输入新的手机号"),
 *                         @OA\Schema(type="string",title="-7037",description="账户已经被绑定"),
 *                         @OA\Schema(type="string",title="-7038",description="账户微信为空"),
 *                         @OA\Schema(type="string",title="-7039",description="绑定错误"),
 *                         @OA\Schema(type="string",title="-7040",description="登录失败"),
 *                         @OA\Schema(type="string",title="-7041",description="用户名或昵称长度超过15个字符"),
 *                         @OA\Schema(type="string",title="-7042",description="用户名已经存在"),
 *                         @OA\Schema(type="string",title="-7043",description="短信服务未开启"),
 *                         @OA\Schema(type="string",title="-7044",description="绑定类型不存在"),
 *                         @OA\Schema(type="string",title="-7045",description="授权信息已过期，请重新授权"),
 *                         @OA\Schema(type="string",title="-7046",description="用户绑定类型不存在"),
 *                         @OA\Schema(type="string",title="-7047",description="参数不为对象"),
 *                         @OA\Schema(type="string",title="-7048",description="过渡开关未开启"),
 *                         @OA\Schema(type="string",title="-7049",description="用户名不能为空"),
 *                         @OA\Schema(type="string",title="-7050",description="用户登录态不能为空"),
 *                         @OA\Schema(type="string",title="-7051",description="该网站暂不支持微信换绑功能"),
 *                         @OA\Schema(type="string",title="-7052",description="用户id不允许为空"),
 *                         @OA\Schema(type="string",title="-7053",description="用户手机号不允许为空"),
 *                         @OA\Schema(type="string",title="-7054",description="真实姓名不能为空"),
 *                         @OA\Schema(type="string",title="-7055",description="身份证不能为空"),
 *                         @OA\Schema(type="string",title="-7056",description="实名认证不通过"),
 *                         @OA\Schema(type="string",title="-7057",description="昵称未通过敏感词校验"),
 *                         @OA\Schema(type="string",title="-7058",description="用户签名限制错误"),
 *                         @OA\Schema(type="string",title="-7059",description="不能关注自己"),
 *                         @OA\Schema(type="string",title="-7060",description="关注用户不存在"),
 *                         @OA\Schema(type="string",title="-7061",description="已被对方拉黑"),
 *                         @OA\Schema(type="string",title="-7062",description="用户名或密码错误"),
 *                         @OA\Schema(type="string",title="-7063",description="不能换绑自己的手机号"),
 *                         @OA\Schema(type="string",title="-7064",description="该网站暂不支持手机绑定功能"),
 *                         @OA\Schema(type="string",title="-7065",description="该网站暂不支持手机换绑功能"),
 *                         @OA\Schema(type="string",title="-7066",description="原有手机号验证码处理失败"),
 *                         @OA\Schema(type="string",title="-7067",description="密码输入非法"),
 *                         @OA\Schema(type="string",title="-7068",description="你已拉黑对方"),
 *                         @OA\Schema(type="string",title="-7069",description="密码不允许包含空格"),
 *                         @OA\Schema(type="string",title="-7070",description="用户需填写扩展字段"),
 *                         @OA\Schema(type="string",title="-7071",description="用户审核中"),
 *                         @OA\Schema(type="string",title="-7072",description="请付费加入站点"),
 *                         @OA\Schema(type="string",title="-7073",description="用户名不允许包含空格"),
 *                         @OA\Schema(type="string",title="-7074",description="当前注册人数过多，请稍后登录"),
 *                         @OA\Schema(type="string",title="-7075",description="不允许上传敏感图"),
 *                         @OA\Schema(type="string",title="-7076",description="分类不存在"),
 *                         @OA\Schema(type="string",title="-7077",description="当前站点是付费模式"),
 *                         @OA\Schema(type="string",title="-7078",description="手机号格式错误"),
 *                         @OA\Schema(type="string",title="-7079",description="生成scheme参数类型错误"),
 *                         @OA\Schema(type="string",title="-7080",description="生成绑定scheme参数类型错误"),
 *                         @OA\Schema(type="string",title="-7081",description="请先配置小程序并开启"),
 *                         @OA\Schema(type="string",title="-7082",description="下载资源已失效"),
 *                         @OA\Schema(type="string",title="-7083",description="超过今天可下载附件的最大次数"),
 *                         @OA\Schema(type="string",title="-7084",description="资源审核中"),
 *                         @OA\Schema(type="string",title="-8000",description="需要绑定微信"),
 *                         @OA\Schema(type="string",title="-8001",description="需要绑定手机"),
 *                         @OA\Schema(type="string",title="-9001",description="短信未开启"),
 *                         @OA\Schema(type="string",title="-9002",description="验证码错误"),
 *                         @OA\Schema(type="string",title="-9003",description="验证码已过期"),
 *                         @OA\Schema(type="string",title="-10000",description="支付失败")
 *      }
 * ),
 *     @OA\Property(property="Message",type="string",description="错误描述信息"),
 *     @OA\Property(property="Data",description="api数据集",type="object"),
 *     @OA\Property(property="RequestId",type="string",description="请求ID"),
 *     @OA\Property(property="RequestTime",format="datetime",default="2021-02-02 02:22:22", type="string",description="请求时间"),
 *     description="dzq接口的整体返回规范,Code等于0表示接口正常返回"
 * )
 * @OA\Schema(
 *     schema = "dzq_pagination",
 *     title = "分页接口模板",
 *     @OA\Property(property="pageData",type="array",description="分页数据",@OA\Items()),
 *     @OA\Property(property="currentPage",type="integer",format="number", default=1, description="当前页码"),
 *     @OA\Property(property="perPage",type="integer",format="number", default=20, description="每页数据条数"),
 *     @OA\Property(property="firstPageUrl",type="string",description="第一页数据地址"),
 *     @OA\Property(property="nextPageUrl",type="string",description="下一页数据地址"),
 *     @OA\Property(property="prePageUrl",type="string",description="上一页数据地址"),
 *     @OA\Property(property="pageLength",type="integer",description="页总数"),
 *     @OA\Property(property="totalCount",type="integer",description="全部数据条数"),
 *     @OA\Property(property="totalPage",type="integer",description="全部数据页数"),
 *     description="分页数据标准格式",
 * )
 * @OA\Parameter(
 *     parameter="bear_token",
 *     name="Authorization",
 *     in="header",
 *     required=false,
 *     description="Bearer Token",
 *     @OA\Schema(type="string")
 * ),
 * @OA\Parameter(
 *     parameter="bear_token_true",
 *     name="Authorization",
 *     in="header",
 *     required=true,
 *     description="Bearer Token",
 *     @OA\Schema(type="string")
 * ),
 * @OA\Parameter(
 *    parameter="threadlist_page",
 *    name="page",
 *    in="query",
 *    required=false,
 *    description = "当前页",
 *    @OA\Schema(
 *        type="integer",default=1
 *    ),
 *),
 *@OA\Parameter(
 *     parameter="threadlist_perPage",
 *    name="perPage",
 *    in="query",
 *    required=false,
 *    description = "每页数据条数",
 *    @OA\Schema(
 *        type="integer",default=20
 *    ),
 *),
 *@OA\Parameter(
 *     parameter="threadlist_scope",
 *    name="scope",
 *    in="query",
 *    required=false,
 *    description = "列表所属模块域 0:普通 1：推荐 2：付费首页 3：搜索页",
 *    @OA\Schema(
 *        type="integer",
 *        enum={0,1,2,3},
 *        default=0
 *    )
 *),
 *@OA\Parameter(
 *     parameter="threadlist_essence",
 *    name="filter[essence]",
 *    in="query",
 *    required=false,
 *    description = "精华帖",
 *    @OA\Schema(
 *        type="integer",default=1
 *    )
 *),
 *@OA\Parameter(
 *     parameter="threadlist_types",
 *    name="filter[types][]",
 *    in="query",
 *    required=false,
 *    description = "帖子类型",
 *    @OA\Schema(type="array",@OA\Items(type="integer"))
 *),
 *@OA\Parameter(
 *    parameter="threadlist_search",
 *    name="filter[search]",
 *    in="query",
 *    required=false,
 *    description = "搜索关键词(仅支持简单搜索，不支持关键词分词)",
 *    @OA\Schema(type="string",default="")
 *),
 *@OA\Parameter(
 *     parameter="threadlist_sort",
 *    name="filter[sort]",
 *    in="query",
 *    required=false,
 *    description = "排序规则",
 *    @OA\Schema(
 *        type="integer",enum={1,2,3,4},default=1
 *    )
 *),
 *@OA\Parameter(
 *     parameter="threadlist_attention",
 *    name="filter[attention]",
 *    in="query",
 *    required=false,
 *    description = "是否关注",
 *    @OA\Schema(
 *        type="integer",enum={0,1},default=0
 *    )
 *),
 *@OA\Parameter(
 *     parameter="threadlist_complex",
 *    name="filter[complex]",
 *    in="query",
 *    required=false,
 *    description = "其他复合筛选类型 1:我的草稿 2:我的点赞 3:我的收藏 4:我的购买 5:我or他的主题页",
 *    @OA\Schema(
 *        type="integer",enum={1,2,3,4,5}
 *    )
 *),
 *@OA\Parameter(
 *     parameter="threadlist_exclusiveIds",
 *    name="filter[exclusiveIds][]",
 *    in="query",
 *    required=false,
 *    description = "需要过滤掉的帖子id集合",
 *    @OA\Schema(type="array",@OA\Items(type="integer"))
 *),
 *@OA\Parameter(
 *     parameter="threadlist_categoryids",
 *    name="filter[categoryids]",
 *    in="query",
 *    required=false,
 *    description = "分类组合（需要查询的分类id集合）",
 *    @OA\Schema(
 *        type="array",@OA\Items(type="integer")
 *    )
 *),
 *@OA\Parameter(
 *     parameter="threadlist_toUserId",
 *    name="filter[toUserId]",
 *    in="query",
 *    required=false,
 *    description = "个人主页帖子列表",
 *    @OA\Schema(
 *        type="array",@OA\Items(type="integer")
 *    )
 *),
 * @OA\Schema(
 *     schema="local_plugin_output",
 *     title="主题插件输出参数",
 *     description="帖子插件个性化的出参",
 *     @OA\Property(property = "body", type = "array", description = "插件个性化数据",@OA\Items(type="object")),
 *     @OA\Property(property = "operation", type = "string", description = "操作类型"),
 *     @OA\Property(property = "threadId", type = "integer", description = "帖子id"),
 *     @OA\Property(property = "tomId", type = "integer", description = "帖子插件id")
 * )
 * @OA\Schema(
 *     schema="local_plugin_input",
 *     title="主题插件输入参数",
 *     description="帖子插件个性化的入参",
 *     @OA\Property(property = "body", type = "array", description = "插件个性化数据",@OA\Items(type="object")),
 *     @OA\Property(property = "operation", type = "string", description = "操作类型"),
 *     @OA\Property(property = "tomId", type = "integer", description = "帖子插件id")
 * )
 * @OA\Schema(
 *     schema="dzq_thread_item",
 *     title="主题详情集合",
 *     @OA\Property(property = "pageData", type = "array",@OA\Items(type = "object",ref="#/components/schemas/dzq_thread"))
 *
 * )
 *
 *
 * @OA\Schema(
 *     schema="dzq_thread",
 *     title="单个主题详情",
 *     @OA\Property(property = "threadId", type = "integer", description = "帖子id"),
 *     @OA\Property(property = "postId", type = "integer", description = "正文id"),
 *     @OA\Property(property = "userId", type = "integer", description = "用户id"),
 *     @OA\Property(property = "parentCategoryId", type = "integer", description = "分类id"),
 *     @OA\Property(property = "topicId", type = "integer", description = "帖子归属的话题"),
 *     @OA\Property(property = "categoryName", type = "string", description = "分类名称"),
 *     @OA\Property(property = "parentCategoryName", type = "string", description = "父级分类名称"),
 *     @OA\Property(property = "title", type = "string", description = "帖子标题"),
 *     @OA\Property(property = "viewCount", type = "integer", description = "浏览数"),
 *     @OA\Property(property = "isApproved", type = "integer", description = "是否审核通过，1通过，0待审核"),
 *     @OA\Property(property = "isStick", type = "boolean", description = "是否设置置顶"),
 *     @OA\Property(property = "isDraft", type = "boolean", description = "是否草稿"),
 *     @OA\Property(property = "isSite", type = "boolean", description = "是否设置到付费站首页热点数据推荐列表"),
 *     @OA\Property(property = "isAnonymous", type = "boolean", description = "是否匿名贴"),
 *     @OA\Property(property = "isFavorite", type = "boolean", description = "当前用户是否收藏"),
 *     @OA\Property(property = "price", type = "number",default=0, description = "帖子价格"),
 *     @OA\Property(property = "payType", type = "number",default = 0, description = "支付类型"),
 *     @OA\Property(property = "paid", type = "boolean", description = "是否已支付"),
 *     @OA\Property(property = "isLike", type = "boolean", description = "当前用户是否点赞"),
 *     @OA\Property(property = "isReward", type = "boolean", description = "当前用户是否打赏"),
 *     @OA\Property(property = "issueAt", type = "string", description = "帖子首次发布、草稿箱发布、审核通过发布，重新编辑内容发布，四种变更的时间记录"),
 *     @OA\Property(property = "createdAt", type = "string", description = "创建时间"),
 *     @OA\Property(property = "updatedAt", type = "string", description = "更新时间"),
 *     @OA\Property(property = "diffTime", type = "string",default="5秒前", description = "显示统一规则下的时间差"),
 *     @OA\Property(property = "freewords", type = "number", description = "免费字数占比（0~1）"),
 *     @OA\Property(property = "userStickStatus", type = "number", enum={0,1}, description = "是否在个人中心置顶（1置顶0不置顶）"),
 *     @OA\Property(property = "user", type = "object", description = "用户信息",
 *          @OA\Property(property = "userId", type = "integer", description = "用户id"),
 *          @OA\Property(property = "nickname", type = "string", description = "昵称"),
 *          @OA\Property(property = "avatar", type = "string", description = "头像地址"),
 *          @OA\Property(property = "threadCount", type = "integer", description = "发帖总数"),
 *          @OA\Property(property = "followCount", type = "integer", description = "关注人数"),
 *          @OA\Property(property = "fansCount", type = "integer", description = "粉丝数"),
 *          @OA\Property(property = "questionCount", type = "integer", description = "问答数"),
 *          @OA\Property(property = "isRealName", type = "boolean", description = "是否实名"),
 *          @OA\Property(property = "joinedAt", type = "string", description = "加入时间")
 *     ),
 *     @OA\Property(property = "group", type = "object", description = "用户组信息",
 *          @OA\Property(property = "groupIcon", type = "string", description = "用户组图标（暂时没有用处）"),
 *          @OA\Property(property = "groupId", type = "integer", description = "群组id"),
 *          @OA\Property(property = "groupName", type = "string", description = "群组名称"),
 *          @OA\Property(property = "isDisplay", type = "boolean", description = "是否显示用户组名称"),
 *     ),
 *     @OA\Property(property = "likeReward", type = "object", description = "对该贴所有用户的点赞打赏信息",
 *         @OA\Property(property = "likePayCount", type = "integer", description = "点赞和支付的用户数"),
 *         @OA\Property(property = "postCount", type = "integer", description = "评论数"),
 *         @OA\Property(property = "shareCount", type = "integer", description = "分享数"),
 *         @OA\Property(property = "users", type = "array", description = "帖子卡片左下角显示的头像信息",@OA\Items(
 *               @OA\Property(property = "avatar", type = "integer", description = "头像地址"),
 *               @OA\Property(property = "createdAt", type = "integer", description = "用户创建时间"),
 *               @OA\Property(property = "nickname", type = "integer", description = "昵称"),
 *               @OA\Property(property = "type", type = "integer", description = "用户类型"),
 *               @OA\Property(property = "userId", type = "integer", description = "用户id")
 *               )),
 *          ),
 *     @OA\Property(property = "displayTag", type = "object", description = "帖子归属的所有标签",
 *         @OA\Property(property = "isEssence", type = "boolean", description = "精华贴"),
 *         @OA\Property(property = "isPrice", type = "boolean", description = "付费贴"),
 *         @OA\Property(property = "isRedPack", type = "boolean", description = "红包贴"),
 *         @OA\Property(property = "isReward", type = "boolean", description = "悬赏贴"),
 *         @OA\Property(property = "isVote", type = "boolean", description = "投票贴"),
 *     ),
 *     @OA\Property(property = "position", type = "object", description = "位置信息",
 *        @OA\Property(property = "address", type = "string", description = "街道详细地址"),
 *        @OA\Property(property = "location", type = "string", description = "地址"),
 *        @OA\Property(property = "latitude", type = "string", description = "纬度"),
 *        @OA\Property(property = "longitude", type = "string", description = "经度"),
 *      ),
 *     @OA\Property(property = "ability", type = "object", description = "当前用户对该贴的操作权限",
 *        @OA\Property(property = "canBeReward", type = "boolean", description = "是否可打赏"),
 *        @OA\Property(property = "canDelete", type = "boolean", description = "是否可删除帖子"),
 *        @OA\Property(property = "canEdit", type = "boolean", description = "是否可编辑"),
 *        @OA\Property(property = "canEssence", type = "boolean", description = "是否可设置精华"),
 *        @OA\Property(property = "canFreeViewPost", type = "boolean", description = "是否免费查看付费帖详情"),
 *        @OA\Property(property = "canReply", type = "boolean", description = "是否可回复"),
 *        @OA\Property(property = "canStick", type = "boolean", description = "是否可设置置顶"),
 *        @OA\Property(property = "canViewPost", type = "boolean", description = "是否可查看详情"),
 *        @OA\Property(property = "canDownloadAttachment", type = "boolean", description = "是否可下载附件"),
 *        @OA\Property(property = "canViewAttachment", type = "boolean", description = "是否可查看附件"),
 *        @OA\Property(property = "canViewVideo", type = "boolean", description = "是否可查看视频"),
 *     ),
 *     @OA\Property(property = "orderInfo", type = "object", description = "订单信息",
 *          @OA\Property(property = "amount", type = "string", description = "金额"),
 *          @OA\Property(property = "isAnonymous", type = "boolean", description = "是否匿名"),
 *          @OA\Property(property = "redAmount", type = "string", description = "红包金额"),
 *          @OA\Property(property = "rewardAmount", type = "string", description = "悬赏金额"),
 *          @OA\Property(property = "title", type = "string", description = "标题"),
 *          @OA\Property(property = "type", type = "integer", description = "类型")
 *     ),
 *     @OA\Property(property = "content", type = "object", description = "帖子正文内容",allOf={
 *         @OA\Schema(@OA\Property(property = "text", type = "string", description = "帖子正文内容")),
 *         @OA\Schema(ref="#/components/schemas/thread_indexes_input")
 *     })
 * )
 * @OA\Schema(
 *     schema="thread_indexes_input",
 *     title="主题内含基础插件信息输入参数",
 *     @OA\Property(property="indexes",type="object",description="插件数据集合",
 *        @OA\Property(property = "101", type = "object", description = "图片",ref="#/components/schemas/local_plugin_input"),
 *        @OA\Property(property = "102", type = "object", description = "语音",ref="#/components/schemas/local_plugin_input"),
 *        @OA\Property(property = "103", type = "string", description = "视频",ref="#/components/schemas/local_plugin_input"),
 *        @OA\Property(property = "104", type = "string", description = "商品",ref="#/components/schemas/local_plugin_input"),
 *        @OA\Property(property = "106", type = "string", description = "红包",ref="#/components/schemas/local_plugin_input"),
 *        @OA\Property(property = "107", type = "string", description = "悬赏",ref="#/components/schemas/local_plugin_input"),
 *        @OA\Property(property = "108", type = "string", description = "文件附件",ref="#/components/schemas/local_plugin_input"),
 *        @OA\Property(property = "109", type = "string", description = "投票",ref="#/components/schemas/local_plugin_input"),
 *     )
 * )
 * @OA\Schema(
 *     schema="thread_indexes_output",
 *     title="主题内含基础插件信息输出参数",
 *     @OA\Property(property="indexes",type="object",description="插件数据集合",
 *        @OA\Property(property = "101", type = "object", description = "图片",ref="#/components/schemas/local_plugin_output"),
 *        @OA\Property(property = "102", type = "object", description = "语音",ref="#/components/schemas/local_plugin_output"),
 *        @OA\Property(property = "103", type = "string", description = "视频",ref="#/components/schemas/local_plugin_output"),
 *        @OA\Property(property = "104", type = "string", description = "商品",ref="#/components/schemas/local_plugin_output"),
 *        @OA\Property(property = "106", type = "string", description = "红包",ref="#/components/schemas/local_plugin_output"),
 *        @OA\Property(property = "107", type = "string", description = "悬赏",ref="#/components/schemas/local_plugin_output"),
 *        @OA\Property(property = "108", type = "string", description = "文件附件",ref="#/components/schemas/local_plugin_output"),
 *        @OA\Property(property = "109", type = "string", description = "投票",ref="#/components/schemas/local_plugin_output"),
 *     )
 * ),
 *
 * @OA\Parameter(
 *     parameter="page",
 *     name="page",
 *     in="query",
 *     required=false,
 *     description="当前页",
 *     @OA\Schema(
 *          type="integer",
 *          default=1
 *      )
 * ),
 *
 * @OA\Parameter(
 *     parameter="perPage",
 *     name="perPage",
 *     in="query",
 *     required=false,
 *     description="当前页",
 *     @OA\Schema(
 *          type="integer",
 *          default=20
 *      )
 * ),
 *
 * @OA\Parameter(
 *     parameter="filter_userId",
 *     name="filter[userId]",
 *     in="query",
 *     required=false,
 *     description = "筛选用户id",
 *     @OA\Schema(
 *          type="integer", default=1
 *      )
 * ),
 *
 * @OA\Parameter(
 *     parameter="filter_type",
 *     name="filter[type]",
 *     in="query",
 *     required=false,
 *     description="筛选类型",
 *     @OA\Schema(
 *          type="integer", default = 1
 *      )
 * ),
 *
 * @OA\Schema(
 *     schema = "attachment_detail_output",
 *     title = "附件详情输出数据集合",
 *          @OA\Property(property = "id", type = "integer", description = "附件id"),
 *          @OA\Property(property = "userId", type = "integer", description = "附件作者id"),
 *          @OA\Property(property = "order", type = "integer", description = "附件排序"),
 *          @OA\Property(property = "type", type = "integer", description = "附件类型(0帖子附件，1帖子图片，2帖子音频，3帖子视频，4消息图片)", enum = {0, 1, 2, 3, 4}),
 *          @OA\Property(property = "type_id", type = "integer", description = "关联的类型id(thread_id,post_id,dialog_message_id)"),
 *          @OA\Property(property = "isRemote", type = "boolean", description = "是否远程附件"),
 *          @OA\Property(property = "isApproved", type = "integer", description = "附件审核状态"),
 *          @OA\Property(property = "url", type = "string", description = "链接"),
 *          @OA\Property(property = "attachment", type = "string", description = "附件存储别名"),
 *          @OA\Property(property = "extension", type = "string", description = "附件后缀"),
 *          @OA\Property(property = "fileName", type = "string", description = "附件名"),
 *          @OA\Property(property = "filePath", type = "string", description = "附件存储路径"),
 *          @OA\Property(property = "fileSize", type = "integer", description = "附件大小"),
 *          @OA\Property(property = "fileType", type = "string", description = "附件mimeType"),
 *          @OA\Property(property = "fileWidth", type = "integer", description = "图-宽"),
 *          @OA\Property(property = "fileHeight", type = "integer", description = "图-高"),
 *          @OA\Property(property = "thumbUrl", type = "string", description = "缩略图链接")
 * )
 * @OA\Schema(
 *     schema = "user_detail_output",
 *     title = "用户详情输出数据集合",
 *          @OA\Property(property = "id", type = "integer", description = "用户id"),
 *          @OA\Property(property = "username", type = "string", description = "用户名(已去除)"),
 *          @OA\Property(property = "nickname", type = "string", description = "昵称"),
 *          @OA\Property(property = "mobile", type = "string", description = "昵称"),
 *          @OA\Property(property = "avatar", type = "string", description = "头像地址"),
 *          @OA\Property(property = "avatarUrl", type = "string", description = "头像地址"),
 *          @OA\Property(property = "realname", type = "string", description = "身份证姓名"),
 *          @OA\Property(property = "identity", type = "string", description = "身份证号码"),
 *          @OA\Property(property = "threadCount", type = "integer", description = "主题数"),
 *          @OA\Property(property = "followCount", type = "integer", description = "关注数"),
 *          @OA\Property(property = "fansCount", type = "integer", description = "粉丝数"),
 *          @OA\Property(property = "likedCount", type = "integer", description = "点赞数"),
 *          @OA\Property(property = "updatedAt", type = "string", description = "更新时间"),
 *          @OA\Property(property = "createdAt", type = "string", description = "创建时间")
 * )
 * @OA\Schema(
 *     schema = "user_wallet_detail_output",
 *     title = "用户钱包详情输出数据集合",
 *          @OA\Property(property = "userId", type = "integer", description = "用户id"),
 *          @OA\Property(property = "availableAmount", type = "string", description = "钱包可用余额"),
 *          @OA\Property(property = "freezeAmount", type = "string", description = "钱包冻结金额"),
 *          @OA\Property(property = "walletStatus", type = "integer", description = "钱包状态(0正常，1冻结体现)", enum = {0, 1}),
 *          @OA\Property(property = "createdAt", type = "string", description = "创建时间"),
 *          @OA\Property(property = "updatedAt", type = "string", description = "更新时间"),
 *          @OA\Property(property = "cashTaxRatio", type = "string", description = "用户提现时的税率")
 * )
 * @OA\Parameter(
 *     parameter = "notification_type_detail",
 *     name = "type",
 *     in = "query",
 *     required = true,
 *     description = "system系统通知, rewarded财务通知, threadrewarded悬赏通知, receiveredpacket红包通知, threadrewardedexpired悬赏过期通知, related艾特@我的, replied回复我的, liked点赞通知",
 *     @OA\Schema(
 *        type = "string",enum = {"system", "rewarded", "threadrewarded", "receiveredpacket", "threadrewardedexpired", "related", "replied", "liked"}
 *    )
 *),
 * @OA\Schema(
 *     schema = "notification_item",
 *     title = "消息通知详情集合",
 *     @OA\Property(property = "pageData", type = "array", @OA\Items(type = "object", ref = "#/components/schemas/notification_detail_output"))
 *
 * )
 * @OA\Schema(
 *     schema = "notification_detail_output",
 *     title = "消息通知详情输出数据集合",
 *           @OA\Property(property = "id", type = "integer", description = "消息id"),
 *           @OA\Property(property = "type", type = "string", description = "消息类型", enum = {"system", "rewarded", "threadrewarded", "receiveredpacket", "threadrewardedexpired", "related", "replied", "liked"}),
 *           @OA\Property(property = "title", type = "string", description = "消息通知标题"),
 *           @OA\Property(property = "content", type = "string", description = "消息通知内容"),
 *           @OA\Property(property = "raw", type = "object", description = "消息模板", allOf = {
 *              @OA\Schema(
 *                  @OA\Property(property = "tplId", type = "integer", description = "消息模板id"),
 *                  @OA\Property(property = "refeeType", type = "integer", description = "续费通知类型；1：普通用户组续费类型、2：付费用户组续费类型")
 *              )
 *           }),
 *           @OA\Property(property = "userId", type = "integer", description = "发送人-用户id"),
 *           @OA\Property(property = "userAvatar", type = "string", description = "发送人-用户头像"),
 *           @OA\Property(property = "nickname", type = "string", description = "发送人-昵称"),
 *           @OA\Property(property = "isReal", type = "boolean", description = "是否实名"),
 *           @OA\Property(property = "readAt", type = "integer", description = "已读时间"),
 *           @OA\Property(property = "createdAt", type = "string", description = "发送时间"),
 *           @OA\Property(property = "threadId", type = "integer", description = "帖子id"),
 *           @OA\Property(property = "threadTitle", type = "string", description = "帖子标题"),
 *           @OA\Property(property = "threadUserGroups", type = "string", description = "帖子作者所在用户组"),
 *           @OA\Property(property = "threadIsApproved", type = "integer", description = "帖子是否已审核"),
 *           @OA\Property(property = "threadUserNickname", type = "string", description = "帖子作者昵称"),
 *           @OA\Property(property = "threadUserAvatar", type = "string", description = "帖子作者头像"),
 *           @OA\Property(property = "threadCreatedAt", type = "string", description = "帖子创建时间"),
 *           @OA\Property(property = "postId", type = "integer", description = "内容id"),
 *           @OA\Property(property = "postContent", type = "string", description = "内容"),
 *           @OA\Property(property = "postCreatedAt", type = "string", description = "内容创建时间"),
 *           @OA\Property(property = "isFirst", type = "boolean", description = "是否是首帖内容"),
 *           @OA\Property(property = "replyPostId", type = "integer", description = "楼中楼回复id"),
 *           @OA\Property(property = "replyPostUserId", type = "integer", description = "楼中楼回复-用户id"),
 *           @OA\Property(property = "replyPostUserName", type = "string", description = "楼中楼回复-用户-用户名"),
 *           @OA\Property(property = "replyPostContent", type = "string", description = "楼中楼回复内容"),
 *           @OA\Property(property = "replyPostCreatedAt", type = "string", description = "楼中楼回复时间"),
 *           @OA\Property(property = "isReply", type = "integer", description = "是否已回复")
 * )
 * @OA\Schema(
 *     schema = "dialog_message_detail_output",
 *     title = "私信详情输出数据集合",
 *           @OA\Property(property = "id", type = "integer", description = "私信id"),
 *           @OA\Property(property = "userId", type = "integer", description = "用户id"),
 *           @OA\Property(property = "unreadCount", type = "integer", description = "未读数"),
 *           @OA\Property(property = "dialogId", type = "integer", description = "对话id"),
 *           @OA\Property(property = "attachmentId", type = "integer", description = "附件id"),
 *           @OA\Property(property = "summary", type = "string", description = "私信内容摘要"),
 *           @OA\Property(property = "messageText", type = "string", description = "私信文字内容"),
 *           @OA\Property(property = "messageTextHtml", type = "string", description = "私信文字网页内容"),
 *           @OA\Property(property = "imageUrl", type = "string", description = "私信图片链接"),
 *           @OA\Property(property = "updatedAt", type = "string", description = "更新时间"),
 *           @OA\Property(property = "createdAt", type = "string", description = "创建时间")
 * )
 *
 * @OA\Schema(
 *     schema="dzq_qrcode",
 *     title="返回二维码相关信息",
 *     @OA\Property(property="Data",type="object",
 *         @OA\Property(property = "sessionToken", type = "string", description = "用户sessionToken"),
 *         @OA\Property(property = "base64Img", type = "string", description = "二维码"),
 *     ))
 * )
 *
 * @OA\Schema(
 *     schema="dzq_login_token",
 *     title="登录态相关信息",
 *     @OA\Property(property="Data",type="object",
 *         @OA\Property(property = "tokenType", type = "string", description = "token类型"),
 *         @OA\Property(property = "expiresIn", type = "integer", description = "过期时间(秒)"),
 *         @OA\Property(property = "accessToken", type = "string", description = "token类型"),
 *         @OA\Property(property = "refreshToken", type = "string", description = "token类型"),
 *         @OA\Property(property = "isMissNickname", type = "integer", description = "是否缺少昵称"),
 *         @OA\Property(property = "avatarUrl", type = "string", description = "头像url"),
 *         @OA\Property(property = "userStatus", type = "integer", description = "用户状态"),
 *         @OA\Property(property = "userId", type = "integer", description = "用户id"),
 *     ))
 * )
 *
 * @OA\Schema(
 *     schema="dzq_user_info",
 *     title="用户相关信息",
 *     @OA\Property(property = "Data", type = "object",ref="#/components/schemas/dzq_user_model")
 * )
 *
 * @OA\Schema(
 *     schema="dzq_user_model",
 *     title="用户表数据",
 *     @OA\Property(property = "id", type = "integer", description = "用户id"),
 *     @OA\Property(property = "username", type = "string", description = "用户名"),
 *     @OA\Property(property = "password", type = "string", description = "密码"),
 *     @OA\Property(property = "nickname", type = "string", description = "用户昵称"),
 *     @OA\Property(property = "payPassword", type = "string", description = "支付密码"),
 *     @OA\Property(property = "mobile", type = "string", description = "手机号"),
 *     @OA\Property(property = "signature", type = "string", description = "签名"),
 *     @OA\Property(property = "lastLoginIp", type = "string", description = "最后登录ip地址"),
 *     @OA\Property(property = "lastLoginPort", type = "integer", description = "最后登录端口"),
 *     @OA\Property(property = "registerIp", type = "string", description = "注册ip"),
 *     @OA\Property(property = "registerPort", type = "string", description = "注册端口"),
 *     @OA\Property(property = "registerReason", type = "string", description = "注册原因"),
 *     @OA\Property(property = "rejectReason", type = "string", description = "审核拒绝原因"),
 *     @OA\Property(property = "usernameBout", type = "integer", description = "用户名修改次数"),
 *     @OA\Property(property = "threadCount", type = "integer", description = "主题数"),
 *     @OA\Property(property = "followCount", type = "integer", description = "关注数"),
 *     @OA\Property(property = "fansCount", type = "integer", description = "粉丝数"),
 *     @OA\Property(property = "likedCount", type = "integer", description = "点赞数"),
 *     @OA\Property(property = "questionCount", type = "integer", description = "提问数"),
 *     @OA\Property(property = "status", type = "integer", description = "用户状态：0正常 1禁用 2审核中 3审核拒绝 4审核忽略"),
 *     @OA\Property(property = "avatar", type = "integer", description = "头像地址"),
 *     @OA\Property(property = "identity", type = "string", description = "身份证号码"),
 *     @OA\Property(property = "realname", type = "string", description = "身份证姓名"),
 *     @OA\Property(property = "avatarAt", type = "string", description = "头像修改时间"),
 *     @OA\Property(property = "loginAt", type = "string", description = "最后登录时间"),
 *     @OA\Property(property = "joinedAt", type = "string", description = "付费加入时间"),
 *     @OA\Property(property = "expiredAt", type = "string", description = "付费到期时间"),
 *     @OA\Property(property = "createdAt", type = "string", description = "创建时间"),
 *     @OA\Property(property = "updatedAt", type = "string", description = "更新时间"),
 *     @OA\Property(property = "bindType", type = "integer", description = "登录绑定类型；0：默认或微信；2：qq登录；"),
 * )
 *
 * @OA\Schema(
 *     schema="dzq_wechat_user_model",
 *     title="微信用户表数据",
 *     @OA\Property(property = "id", type = "integer", description = "自增长id"),
 *     @OA\Property(property = "userId", type = "integer", description = "用户id"),
 *     @OA\Property(property = "mpOpenid", type = "string", description = "公众号openid"),
 *     @OA\Property(property = "devOpenid", type = "string", description = "开放平台openid"),
 *     @OA\Property(property = "minOpenid", type = "string", description = "小程序openid"),
 *     @OA\Property(property = "nickname", type = "string", description = "微信昵称"),
 *     @OA\Property(property = "sex", type = "integer", description = "性别"),
 *     @OA\Property(property = "province", type = "string", description = "省份"),
 *     @OA\Property(property = "city", type = "string", description = "城市"),
 *     @OA\Property(property = "country", type = "string", description = "国家"),
 *     @OA\Property(property = "headimgurl", type = "string", description = "头像"),
 *     @OA\Property(property = "privilege", type = "string", description = "用户特权信息"),
 *     @OA\Property(property = "unionid", type = "string", description = "只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段"),
 *     @OA\Property(property = "createdAt", type = "string", description = "创建时间"),
 *     @OA\Property(property = "updatedAt", type = "string", description = "更新时间"),
 * )
 *
 * @OA\Schema(
 *     schema="dzq_post_detail",
 *     title="评论详情",
 *     @OA\Property(property = "canDelete", type = "boolean", description="能否删除"),
 *     @OA\Property(property = "canHide", type = "boolean", description="能否删除"),
 *     @OA\Property(property = "canLike", type = "boolean", description="能否点赞"),
 *     @OA\Property(property = "commentPostId", type = "integer", description="被评论id"),
 *     @OA\Property(property = "commentUserId", type = "integer", description="被评论用户id"),
 *     @OA\Property(property = "content", type = "string", description="评论内容"),
 *     @OA\Property(property = "createdAt", type = "string", description="评论时间"),
 *     @OA\Property(property = "id", type = "integer", description="评论id"),
 *     @OA\Property(property = "images", type = "array", description="图片url", @OA\Items(type="string")),
 *     @OA\Property(property = "isApproved", type = "integer", description="是否审核通过"),
 *     @OA\Property(property = "isComment", type = "boolean", description="是否是二级评论"),
 *     @OA\Property(property = "isDeleted", type = "boolean", description="是否被删除"),
 *     @OA\Property(property = "isFirst", type = "boolean", description="是否是帖子内容"),
 *     @OA\Property(property = "isLiked", type = "boolean", description="是否点赞"),
 *     @OA\Property(property = "likeCount", type = "integer", description="点赞数量"),
 *     @OA\Property(property = "likeState", type = "object", description="关联点赞详情",
 *          @OA\Property(property = "post_id", type="integer", description="点赞评论id"),
 *          @OA\Property(property = "user_id", type="integer", description="点赞用户id")
 *     ),
 *     @OA\Property(property = "likedAt", type = "string", description="点赞时间"),
 *     @OA\Property(property = "redPacketAmount", type = "number", description="红包金额"),
 *     @OA\Property(property = "replyCount", type = "integer", description="回复数量"),
 *     @OA\Property(property = "replyPostId", type = "integer", description="回复评论id"),
 *     @OA\Property(property = "replyUserId", type = "integer", description="回复用户id"),
 *     @OA\Property(property = "rewards", type = "number", description="获得悬赏金额"),
 *     @OA\Property(property = "summaryText", type = "string", description="评论简介"),
 *     @OA\Property(property = "threadId", type = "integer", description="帖子id"),
 *     @OA\Property(property = "user", type = "object", description="发评论用户信息",
 *          @OA\Property(property="avatar", type="string", description="用户头像url"),
 *          @OA\Property(property="id", type="integer", description="用户id"),
 *          @OA\Property(property="isReal", type="boolean", description="是否实名"),
 *          @OA\Property(property="nickname", type="string", description="用户昵称"),
 *          @OA\Property(property="username", type="string", description="用户名称"),
 *     ),
 *     @OA\Property(property = "userId", type = "integer", description="发帖用户id"),
 * )
 *
 * @OA\Parameter(
 *    parameter="filter_changeType",
 *    name="filter[changeType]",
 *    in="query",
 *    required=false,
 *    description = "收入类型",
 *    @OA\Schema(type="array",@OA\Items(type="integer"))
 * )
 *
 * @OA\Parameter(
 *    parameter="filter_cashStatus",
 *    name="filter[cashStatus]",
 *    in="query",
 *    required=false,
 *    description = "提现状态",
 *    @OA\Schema(type="array",@OA\Items(type="integer"))
 * )
 *
 * @OA\Schema(
 *     schema="deleted_user_detail",
 *     title="删除者信息",
 *     @OA\Property(property = "deletedUserId", type = "integer", description = "删除者id"),
 *     @OA\Property(property = "deletedNickname", type = "string", description = "删除者昵称"),
 *     @OA\Property(property = "deletedAt", type = "string", description = "删除时间")
 * )
 *
 * @OA\Schema(
 *     schema="dzq_notification_tpls_model",
 *     title="通知模板model",
 *      @OA\Property(property="id",type="integer",description = "通知id"),
 *      @OA\Property(property="noticeId",type="string",description = "模板唯一标识ID"),
 *      @OA\Property(property="status",type="integer",description = "状态1开启 0关闭"),
 *      @OA\Property(property="type",type="integer",description = "通知类型0系统1微信2短信"),
 *      @OA\Property(property="typeName",type="string",description = "类型名称"),
 *      @OA\Property(property="title",type="string",description = "标题"),
 *      @OA\Property(property="content",type="string",description = "内容"),
 *      @OA\Property(property="vars",type="string",description = "可选的变量"),
 *      @OA\Property(property="templateId",type="string",description = "模板ID"),
 *      @OA\Property(property="firstData",type="string",description = "first.DATA"),
 *      @OA\Property(property="keywordsData",type="string",description = "keywords.DATA"),
 *      @OA\Property(property="remarkData",type="string",description = "remark.DATA"),
 *      @OA\Property(property="color",type="string",description = "data color"),
 *      @OA\Property(property="redirectType",type="integer",description = "跳转类型0无跳转 1跳转H5 2跳转小程序"),
 *      @OA\Property(property="redirectUrl",type="string",description = "跳转地址"),
 *      @OA\Property(property="pagePath",type="string",description = "跳转路由"),
 *      @OA\Property(property="isError",type="integer",description = "模板是否配置错误0不是1是"),
 *      @OA\Property(property="errorMsg",type="string",description = "错误信息"),
 * )
 * @OA\Schema(
 *     schema="group_detail",
 *     title="用户组信息",
 *     @OA\Property(property = "groupId", type = "integer", description = "用户组id"),
 *     @OA\Property(property = "groupName", type = "string", description = "用户组名称"),
 *     @OA\Property(property = "groupIcon", type = "string", description = "")
 * )
 * @OA\Schema(
 *     schema="report_detail",
 *     title="举报信息",
 *     @OA\Property(property = "id", type = "integer", description = "举报id"),
 *     @OA\Property(property = "userId", type = "integer", description = "用户id"),
 *     @OA\Property(property = "threadId", type = "integer", description = "主题id"),
 *     @OA\Property(property = "postId", type = "integer", description = "回复id"),
 *     @OA\Property(property = "type", type = "integer", description = "举报类型:0个人主页 1主题 2评论/回复"),
 *     @OA\Property(property = "reason", type = "string", description = "举报理由"),
 *     @OA\Property(property = "status", type = "integer", description = "举报状态:0未处理 1已处理"),
 *     @OA\Property(property = "createdAt", type = "string", description = "创建时间"),
 *     @OA\Property(property = "updatedAt", type = "string", description = "更新时间")
 * )
 * @OA\Schema(
 *     schema="site_info",
 *     title="站点信息项",
 *     @OA\Property(property = "version", type = "string", description = "版本号"),
 *     @OA\Property(property = "phpVersion", type = "string", description = "PHP版本"),
 *     @OA\Property(property = "serverSoftware", type = "string", description = "nginx版本"),
 *     @OA\Property(property = "serverOs", type = "string", description = "windows版本"),
 *     @OA\Property(property = "db", type = "string", description = "mysql版本"),
 *     @OA\Property(property = "databaseConnectionName", type = "string", description = "数据库类型"),
 *     @OA\Property(property = "sslInstalled", type = "boolean", description = "是否安装：否false、 是true"),
 *     @OA\Property(property = "cacheDriver", type = "string", description = "文件"),
 *     @OA\Property(property = "uploadSize", type = "string", description = "上传文件大小"),
 *     @OA\Property(property = "dbSize", type = "string", description = "数据库大小"),
 *     @OA\Property(property = "timezone", type = "string", description = "时区"),
 *     @OA\Property(property = "debugMode", type = "boolean", description = "是否开启debug"),
 *     @OA\Property(property = "storageDirWritable", type = "boolean", description = "是否开启写入权限"),
 *     @OA\Property(property = "cacheDirWritable", type = "boolean", description = "是否开启查看权限"),
 *     @OA\Property(property = "appSize", type = "string", description = "app大小"),
 *     @OA\Property(property = "packages", type = "array", description = "composer包",@OA\Items())
 * )
 * @OA\Schema(
 *     schema="unapproved_info",
 *     title="审核项",
 *     @OA\Property(property = "unapprovedUsers", type = "integer", description = "未审核的用户数"),
 *     @OA\Property(property = "unapprovedThreads", type = "integer", description = "未审核的主题数"),
 *     @OA\Property(property = "unapprovedPosts", type = "integer", description = "未审核的主题数"),
 *     @OA\Property(property = "unapprovedMoneys", type = "integer", description = "未审核的主题数")
 * )
 */
