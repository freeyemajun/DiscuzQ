<?php

/**
 * Copyright (C) 2020 Tencent Cloud.
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

namespace App\Api\Controller\Plugin;

use App\Common\ResponseCode;
use Discuz\Base\DzqAdminController;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Stream;

class PluginUploadImageController extends DzqAdminController
{
    public function main()
    {
        $file = Arr::get($this->request->getUploadedFiles(), 'file');
        if (empty($file)) {
            $this->outPut(ResponseCode::INVALID_PARAMETER);
        }

        /** @var Stream $fileContent */
        $fileContent = $file->getStream();
        $fileName = $file->getClientFilename();
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileName = md5($fileName).time().'.'.$ext;

        /** @var PluginFileSave $shopFileSave */
        $shopFileSave = $this->app->make(PluginFileSave::class);
        list($path, $isRemote) = $shopFileSave->saveFile($fileName, $fileContent->getContents());
        $pathUrl = $shopFileSave->getFilePath($isRemote, $path);
        $result = [];
        $result['url'] = $pathUrl;

        $this->outPut(0, '', $result);
    }
}
