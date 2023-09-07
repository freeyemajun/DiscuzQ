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

namespace App\Api\Controller\Threads;

use App\Common\Platform;
use App\Common\PluginEnum;
use App\Common\ResponseCode;
use App\Modules\ThreadTom\TomConfig;
use App\Repositories\UserRepository;
use Discuz\Base\DzqController;
use Discuz\Common\Utils;
use Discuz\Contracts\Setting\SettingsRepository;

class ThreadTypeListController extends DzqController
{
    use ThreadTrait;

    protected $thread;

    protected $settings;

    public function __construct(SettingsRepository $settings)
    {
        $this->settings = $settings;
    }

    protected function checkRequestPermissions(UserRepository $userRepo)
    {
        return true;
    }

    public function main()
    {
       $result = [
           ["name"=>"视频","type"=>(string)TomConfig::TOM_VIDEO],
           ["name"=>"图片","type"=>(string)TomConfig::TOM_IMAGE],
           ["name"=>"语音","type"=>(string)TomConfig::TOM_AUDIO],
           ["name"=>"红包","type"=>(string)TomConfig::TOM_REDPACK],
           ["name"=>"投票","type"=>(string)TomConfig::TOM_VOTE],
           ["name"=>"悬赏问答","type"=>(string)TomConfig::TOM_REWARD],
           ["name"=>"文件附件","type"=>(string)TomConfig::TOM_DOC],
       ];

        $pluginList = Utils::getPluginList();
        foreach ($pluginList as $key=>$item){
            if($item["type"] != PluginEnum::PLUGIN_THREAD
                || (isset($item["filter_enable"]) && !$item["filter_enable"])) {
                continue;
            }
            $result[]=["name"=>$item["name_cn"],"type"=>$key];
        }

        $isDisplay = $this->settings->get('thread_optimize', 'default');
       if (!$isDisplay &&  Utils::requestFrom() == Platform::MinProgram) {
            $optimizeTemp = TomConfig::OPTIMIZE_TYPE_LIST;
            $resultTemp = [];
            foreach ($result as $oneItem){
                if (!in_array($oneItem["type"], $optimizeTemp)){
                    $resultTemp[] = $oneItem;
                }
            }
            $result = $resultTemp;
       }

       $this->outPut(ResponseCode::SUCCESS, '', $result);
    }

}
