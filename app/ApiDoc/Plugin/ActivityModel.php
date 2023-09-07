<?php
/**
 * @OA\Schema(
 *     schema="activity",
 *     title="活动报名插件入参和出参",
 *     @OA\Property(property = "输入", type = "object", description = "活动报名插件入参",
 *         @OA\Property(property = "title", type = "string", description = "活动名称,50个字符"),
 *         @OA\Property(property = "content", type = "string", description = "活动内容,200个字符"),
 *         @OA\Property(property = "activityStartTime", type = "string", description = "活动开始时间"),
 *         @OA\Property(property = "activityEndTime", type = "string", description = "活动结束时间"),
 *         @OA\Property(property = "registerStartTime", type = "string", description = "报名开始时间"),
 *         @OA\Property(property = "registerEndTime", type = "string", description = "报名结束时间"),
 *         @OA\Property(property = "totalNumber", type = "integer", description = "报名人数上限 0:不限制"),
 *         @OA\Property(property = "position", type = "object", description = "位置信息",
 *             @OA\Property(property = "address", type = "string", description = "地址信息"),
 *             @OA\Property(property = "location", type = "string", description = "位置信息"),
 *             @OA\Property(property = "longitude", type = "string", description = "经度"),
 *             @OA\Property(property = "latitude", type = "string", description = "纬度")
 *         ),
 *        @OA\Property(property = "additionalInfoType", type = "array", description = "报名必填附加项；1：姓名、2：手机号、3：微信号、4：地址", @OA\Items()),
 *     ),
 *     @OA\Property(property = "输出", type = "object", description = "活动报名插件出参",
 *         @OA\Property(property = "activityId", type = "integer", description = "活动id"),
 *         @OA\Property(property = "title", type = "string", description = "活动名称,50个字符"),
 *         @OA\Property(property = "content", type = "string", description = "活动内容,200个字符"),
 *         @OA\Property(property = "activityStartTime", type = "string", description = "活动开始时间"),
 *         @OA\Property(property = "activityEndTime", type = "string", description = "活动结束时间"),
 *         @OA\Property(property = "registerStartTime", type = "string", description = "报名开始时间"),
 *         @OA\Property(property = "registerEndTime", type = "string", description = "报名结束时间"),
 *         @OA\Property(property = "totalNumber", type = "integer", description = "报名人数上限 0:不限制"),
 *         @OA\Property(property = "currentNumber", type = "integer", description = "当前已报名人数"),
 *         @OA\Property(property = "isRegistered", type = "boolean", description = "已经报名登记"),
 *         @OA\Property(property = "isExpired", type = "boolean", description = "活动已过期"),
 *         @OA\Property(property = "isMemberFull", type = "boolean", description = "人数已满"),
 *         @OA\Property(property = "position", type = "object", description = "位置信息【可以不传】",
 *            @OA\Property(property = "address", type = "string", description = "地址信息"),
 *            @OA\Property(property = "location", type = "string", description = "位置信息"),
 *            @OA\Property(property = "longitude", type = "string", description = "经度"),
 *            @OA\Property(property = "latitude", type = "string", description = "纬度")
 *         ),
 *         @OA\Property(property = "createAt", type = "string", description = "活动发起时间"),
 *         @OA\Property(property = "updateAt", type = "string", description = "活动最近编辑时间"),
 *         @OA\Property(property = "registerUsers", type = "array", description = "已报名用户(最多显示三个)",@OA\Items(
 *             @OA\Property(property = "userId", type = "integer", description = "用户id"),
 *             @OA\Property(property = "avatar", type = "string", description = "头像"),
 *             @OA\Property(property = "nickname", type = "string", description = "昵称"),
 *         )),
 *         @OA\Property(property = "additionalInfoType", type = "array", description = "报名必填项类型",@OA\Items()),
 *         @OA\Property(property = "activityUser", type = "array", description = "报名人信息",@OA\Items(
                @OA\Property(property = "userId", type = "integer", description = "用户id"),
 *              @OA\Property(property = "additionalInfo", type = "array", description = "报名信息",@OA\Items(
                    @OA\Property(property = "name", type = "string", description = "姓名"),
 *                  @OA\Property(property = "mobile", type = "string", description = "手机号"),
 *                  @OA\Property(property = "weixin", type = "string", description = "微信号"),
 *                  @OA\Property(property = "address", type = "string", description = "地址"),
 *              )),
 *              @OA\Property(property = "nickname", type = "string", description = "昵称"),
 *         )),
 *         @OA\Property(property = "isInitiator", type = "integer", description = "是否是发起人")
 *     )
 * )
 */
