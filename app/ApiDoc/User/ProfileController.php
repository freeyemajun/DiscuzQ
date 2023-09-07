<?php
/**
 * @OA\Get(
 *     path="/api/v3/user",
 *     summary="用户资料",
 *     description="用户资料接口",
 *     tags={"个人中心"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *         name="userId",
 *         in="query",
 *         required=true,
 *         description = "用户id",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="用户资料接口返回",
 *         @OA\JsonContent(allOf={
 *             @OA\Schema(ref="#/components/schemas/dzq_layout"),
 *             @OA\Schema(title="用户资料接口返回",description="用户资料接口返回",@OA\Property(property="Data",type="object",
 *                   @OA\Property(property="avatarUrl", type="string", description="头像地址"),
 *                   @OA\Property(property="backgroundUrl", type="string", description="背景图地址"),
 *                   @OA\Property(property="banReason", type="string", description="封禁理由"),
 *                   @OA\Property(property="canBeAsked", type="boolean", description="能否提问"),
 *                   @OA\Property(property="canDelete", type="boolean", description="能否删除"),
 *                   @OA\Property(property="canEdit", type="boolean", description="能否编辑"),
 *                   @OA\Property(property="canEditUsername", type="boolean", description="能否编辑用户名"),
 *                   @OA\Property(property="canWalletPay", type="boolean", description="能否钱包支付"),
 *                   @OA\Property(property="denyStatus", type="boolean", description="待定"),
 *                   @OA\Property(property="expiredAt", type="string",format="datetime", description= "过期时间"),
 *                   @OA\Property(property="expiredDays", type="boolean", description = "待定"),
 *                   @OA\Property(property="follow", type="string", description = "关注"),
 *                   @OA\Property(property="followCount", type="integer", description = "关注数"),
 *                   @OA\property(property="group",type="object",allOf ={
 *                       @OA\Schema(
 *                          @OA\Property(property="groupName",type="string", description = "用户组"),
 *                          @OA\Property(property="groupId",type="integer", description = "用户组id"),
 *                          @OA\Property(property="expirationTime",type="string", description = "到期时间"),
 *                          @OA\Property(property="isTop",type="boolean", description = "是否为顶级付费用户组"),
 *                          @OA\Property(property="color",type="string", description = "用户组颜色"),
 *                          @OA\Property(property="level",type="integer", description = "用户组等级"),
 *                          @OA\Property(property="remainTime",type="integer", description = "剩余时间数字"),
 *                          @OA\Property(property="typeTime",type="integer", description = "剩余时间类型；0：天、1：时、2：分"),
 *                      )
 *                  }),
 *                   @OA\Property(property="hasPassword", type="boolean", description = "是否设置密码"),
 *                   @OA\Property(property="id", type="integer", description = "用户id"),
 *                   @OA\Property(property="identity", type="string", description = "身份证号"),
 *                   @OA\Property(property="isBindWechat", type="boolean", description = "是否绑定微信"),
 *                   @OA\Property(property="isReal", type="boolean", description = "是否实名"),
 *                   @OA\Property(property="isRenew", type="boolean", description = "待定"),
 *                   @OA\Property(property="likedCount", type="integer", description = "被点赞数"),
 *                   @OA\Property(property="loginAt", type="string", description = "登录时间"),
 *                   @OA\Property(property="mobile", type="string", description = "手机号"),
 *                   @OA\Property(property="nickname", type="string", description = "昵称"),
 *                   @OA\Property(property="originalAvatarUrl", type="string", description = "原始头像地址"),
 *                   @OA\Property(property="originalBackGroundUrl", type="string", description = "原始背景图地址"),
 *                   @OA\Property(property="originalMobile", type="string", description = "原始手机号码"),
 *                   @OA\Property(property="paid", type="boolean", description = "待定"),
 *                   @OA\Property(property="payTime", type="string",format="datetime", description = "待定"),
 *                   @OA\Property(property="questionCount", type="integer", description = "提问数"),
 *                   @OA\Property(property="realname", type="string", description = "身份证姓名"),
 *                   @OA\Property(property="registerReason", type="string", description = "注册原因"),
 *                   @OA\Property(property="showGroups", type="boolean", description = "是否展示组"),
 *                   @OA\Property(property="signature", type="string", description = "签名"),
 *                   @OA\Property(property="status", type="integer", description = "个人状态"),
 *                   @OA\Property(property="threadCount", type="integer", description = "帖子数"),
 *                   @OA\Property(property="updatedAt", type="string", description = "更新时间"),
 *                   @OA\Property(property="username", type="string", description = "用户名"),
 *                   @OA\Property(property="usernameBout", type="integer", description = "待定"),
 *                   @OA\Property(property="walletBalance", type="number", description = "钱包余额"),
 *                   @OA\Property(property="walletFreeze", type="number", description = "冻结金额"),
 *                   @OA\Property(property="wxHeadImgUrl", type="string", description = "微信头像地址"),
 *                   @OA\Property(property="wxNickname", type="string", description = "微信昵称")
 * ))
 *         })
 *     )
 * )
 */
