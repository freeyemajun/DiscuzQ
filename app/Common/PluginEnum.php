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

namespace App\Common;


class PluginEnum
{
    const PLUGIN_CUSTOMER = 0;//完全自定义插件
    const PLUGIN_THREAD = 1;//帖子新增类型
    const PLUGIN_DATA_IMPORT = 2;//外部数据导入
    const PLUGIN_AD = 3;//广告插件
    const PLUGIN_BANNER = 4;//首页banner插件
    const PLUGIN_EMOJI = 5;//表情插件
}
