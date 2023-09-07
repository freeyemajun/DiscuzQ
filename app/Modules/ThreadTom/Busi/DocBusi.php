<?php
/**
 * Copyright (C) 2021 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Modules\ThreadTom\Busi;

use App\Api\Controller\Attachment\AttachmentTrait;
use App\Api\Serializer\AttachmentSerializer;
use App\Common\CacheKey;
use Discuz\Base\DzqCache;
use App\Common\ResponseCode;
use App\Models\Attachment;
use App\Models\Thread;
use App\Modules\ThreadTom\TomBaseBusi;

class DocBusi extends TomBaseBusi
{
    use AttachmentTrait;

    public function create()
    {
        $docIds = $this->getParams('docIds');
        $num = (int)$this->getSupportMaxUploadAttachmentNum();
        if (count($docIds) > $num) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '文件不能超过'.$num.'个');
        }
        return $this->jsonReturn(['docIds' => $docIds]);
    }

    public function update()
    {
        $docIds = $this->getParams('docIds');
        $num = (int)$this->getSupportMaxUploadAttachmentNum();
        if (count($docIds) > $num) {
            $this->outPut(ResponseCode::INVALID_PARAMETER, '文件不能超过'.$num.'个');
        }
        return $this->jsonReturn(['docIds' => $docIds]);
    }

    public function select()
    {
        $serializer = $this->app->make(AttachmentSerializer::class);
        $result = [];
        $docIds = $this->getParams('docIds');
        $attachments = DzqCache::hMGet(CacheKey::LIST_THREADS_V3_ATTACHMENT, $docIds, function ($docIds) {
            return Attachment::query()->whereIn('id', $docIds)->get()->keyBy('id')->toArray();
        });

        foreach ($attachments as $attachment) {
            $item = $this->camelData($serializer->getBeautyAttachment($attachment));
            if (!$this->canViewTom && (!$this->isPaySub && !empty($this->priceIds) && in_array($attachment['id'], $this->priceIds))) {
                $item['url'] = $item['thumbUrl'] = $item['blurUrl'];
                $item['needPay'] = 1;
            }else{
                $item['needPay'] = 0;
            }
            unset($item['blurUrl']);
            $result[] = $item;

        }
        return $this->jsonReturn($result);
    }
}
