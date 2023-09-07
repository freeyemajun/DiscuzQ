<?php
/**
 * @OA\Get(
 *     path="/plugin/shop/api/wxshop/list ",
 *     summary="微信小商店商品列表",
 *     description="微信小商店商品列表",
 *     tags={"插件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         description = "当前页",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="perPage",
 *         in="query",
 *         required=false,
 *         description = "每页条数",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response = 200, description = "调用成功", @OA\JsonContent(allOf = {
 *          @OA\Schema(ref = "#/components/schemas/dzq_layout"),
 *          @OA\Schema(@OA\Property(property = "Data", type = "object", allOf = {
 *              @OA\Schema(ref = "#/components/schemas/dzq_pagination"),
 *              @OA\Schema(
 *                  @OA\Property(property = "pageData", type = "array", @OA\Items(type = "object", allOf = {
 *                      @OA\Schema(@OA\Property(property = "productId", type = "string", description = "商品id")),
 *                      @OA\Schema(@OA\Property(property = "title", type = "string", description = "商品名")),
 *                      @OA\Schema(@OA\Property(property = "imagePath", type = "string", description = "商品图片url")),
 *                      @OA\Schema(@OA\Property(property = "price", type = "string", description = "价格"))
 *                  }))
 *              )
 *         }))
 *    }))
 * )
 */

