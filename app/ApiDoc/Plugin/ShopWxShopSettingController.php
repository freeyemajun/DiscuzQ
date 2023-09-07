<?php
/**
 * @OA\Post(
 *     path="/plugin/shop/api/wxshop/setting",
 *     summary="商店插件设置",
 *     description="商店插件微信小商店设置,当/api/backAdmin/plugin/settings.save返回成功之后再触发，做微信小商店特有的操作",
 *     tags={"插件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Response(
 *        response=200,
 *        description="设置成功",
 *        @OA\JsonContent(allOf={
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(@OA\Property(property = "Data", type = "object",
 *             @OA\Property(property="wxQrCode",type="string",description="自动产生的二维码url")
 *         ))
 *     }))
 * )
 */
