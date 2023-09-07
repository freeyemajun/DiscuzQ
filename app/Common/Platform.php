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

namespace App\Common;

class Platform
{
    const PC = 1;

    const H5 = 2;

    const MinProgram = 3;

    //判断请求来源常量
    const FROM_PC = 'pc';

    const FROM_H5 = 'h5';

    const FROM_WEAPP = 'weapp';     #微信小程序

    const FROM_SWAN = 'swan';       #百度小程序

    const FROM_ALIPAY = 'alipay';   #支付宝小程序

    const FROM_TT = 'tt';           #字节跳动小程序

    const FROM_QQ = 'qq';           #qq小程序

    const FROM_JD = 'jd';           #京东小程序
}
