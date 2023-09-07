<?php
/**
 * @OA\Get(
 *     path="/api/v3/attachment.download",
 *     summary="附件生成url链接",
 *     description="附件生成url链接",
 *     tags={"附件"},
 *     @OA\Parameter(ref="#/components/parameters/bear_token"),
 *     @OA\Parameter(name="sign",
 *          in="query",
 *          required=true,
 *          description = "唯一识别",
 *          @OA\Schema(type="integer")
 *      ),
 *     @OA\Parameter(name="attachmentsId",
 *          in="query",
 *          required=true,
 *          description = "附件id",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="返回更新结果",
 *     )
 * )
 */

