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

namespace App\Api\Controller\Plugin;

use App\Common\CacheKey;
use App\Common\DzqConst;
use App\Common\ResponseCode;
use Discuz\Base\DzqAdminController;
use Discuz\Base\DzqCache;
use Discuz\Common\Utils;

class PluginOperateController extends DzqAdminController
{
    use PluginTrait;

    public function main()
    {
        $appId = $this->inPut("appId");
        $operate = $this->inPut("operate");
        $pluginMap = Utils::getPluginList(true);
        if(!isset($pluginMap[$appId])){
            $this->outPut(ResponseCode::INVALID_PARAMETER,"没有找到对应的插件，请检查参数");
        }
        $item = $pluginMap[$appId];

        switch ($operate){
            case 1:
                $this->publishPlugin($item);
                break;
            case 2:
                $this->offlinePlugin($item);
                break;
            case 3:
                $this->unloadPlugin($item);
                break;
        }

        $this->outPut(ResponseCode::INVALID_PARAMETER);
    }

    public function suffixClearCache(){
        DzqCache::delKey(CacheKey::PLUGIN_LOCAL_CONFIG);
    }

    private function publishPlugin($item){
        $nameEn = $item["name_en"];
        $this->changePluginStatus($item, DzqConst::BOOL_YES);

        //执行命令
        Utils::runConsoleCmd('migrate:plugin', ['--force' => true,'--name' => $nameEn]);

        $this->outPut(0,'', "发布成功");
    }

    private function offlinePlugin($item){

        $this->changePluginStatus($item, DzqConst::BOOL_NO);

        $this->outPut(0,'', "下线成功");
    }

    private function unloadPlugin($item){
        $pluginDir = base_path('plugin');
        $nameEn = $item["name_en"];
        $pathDir = $pluginDir.DIRECTORY_SEPARATOR.ucfirst($nameEn);
        \App\Common\Utils::removeDir($pathDir);

        $this->outPut(0,'', "删除成功");
    }
}
