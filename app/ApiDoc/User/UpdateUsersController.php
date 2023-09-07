<?php
/**
 * @OA\Post(
 *     path="/api/v3/users/update.user",
 *     summary="编辑资料",
 *     description="编辑资料接口",
 *     tags={"个人中心"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\RequestBody(
 *         required=true,
 *         description = "编辑资料",
 *         @OA\JsonContent(
 *             @OA\Property(property="nickname",type="string",description="昵称"),
 *             @OA\Property(property="username",type="string",description="用户名"),
 *             @OA\Property(property="password",type="string",description="密码"),
 *             @OA\Property(property="newPassword",type="string",description="新密码"),
 *             @OA\Property(property="passwordConfirmation",type="string",description="确认密码"),
 *             @OA\Property(property="payPassword",type="string",description="支付密码"),
 *             @OA\Property(property="payPasswordConfirmation",type="string", description="确认支付密码"),
 *             @OA\Property(property="payPasswordToken",type="string", description="支付密码令牌"),
 *             @OA\Property(property="registerReason",type="string",description="注册原因"),
 *             )
 *           ),
 * @OA\Response(response=200,
 *        description="编辑资料接口返回",
 *        @OA\JsonContent(allOf ={
 *           @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *           @OA\Schema(title="更新用户返回数据",description="更新用户返回数据",@OA\Property(property="Data",type="object",
 *                @OA\Property(property="avatar", type="string", description="头像"),
 *                @OA\Property(property="background",type="string",description="背景"),
 *                @OA\Property(property="fansCount",type="number",description="粉丝数量"),
 *                @OA\Property(property="followCount",type = "integer", description = "关注数"),
 *                @OA\Property(property="id",type="number", description ="id"),
 *                @OA\Property(property="likedCount",type = "integer", description = "点赞数"),
 *                @OA\Property(property="mobile",type="string", description ="手机号"),
 *                @OA\Property(property="nickname",type="string",description="昵称"),
 *                @OA\Property(property="questionCount",type="string", description ="提问数"),
 *                @OA\Property(property="signature",type = "string", description = "签名"),
 *                @OA\Property(property="threadCount",type="integer", description = "帖子数")))
 *     })
 *     )
 * )
 *        )
 *     )

 */
