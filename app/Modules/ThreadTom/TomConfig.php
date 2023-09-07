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

namespace App\Modules\ThreadTom;

class TomConfig
{
    const TOM_TEXT = 100;//文字内容，目前不单独作为扩展插件存储

    const TOM_IMAGE = 101;

    const TOM_AUDIO = 102;

    const TOM_VIDEO = 103;

    const TOM_GOODS = 104;

    const TOM_REDPACK = 106;

    const TOM_REWARD = 107;

    const TOM_DOC = 108;

    const TOM_VOTE = 109;

    const OPTIMIZE_TYPE_LIST = [TomConfig::TOM_GOODS,TomConfig::TOM_REDPACK,TomConfig::TOM_REWARD,'61540fef8f4de8'];

    public static $sub_pay_list = [
        self::TOM_IMAGE,
        self::TOM_VIDEO,
        self::TOM_AUDIO,
        self::TOM_DOC
    ];

    public static $map = [
        self::TOM_TEXT => [
            'name_en' => 'Text',
            'name_cn' => '文字',
            'service' => ''
        ],
        self::TOM_IMAGE => [
            'name_en' => 'Image',
            'name_cn' => '图片',
            'service' => \App\Modules\ThreadTom\Busi\ImageBusi::class
        ],
        self::TOM_AUDIO => [
            'name_en' => 'Audio',
            'name_cn' => '语音条',
            'service' => \App\Modules\ThreadTom\Busi\AudioBusi::class
        ],
        self::TOM_VIDEO => [
            'name_en' => 'Video',
            'name_cn' => '视频',
            'service' => \App\Modules\ThreadTom\Busi\VideoBusi::class
        ],
        self::TOM_GOODS => [
            'name_en' => 'Goods',
            'name_cn' => '商品',
            'service' => \App\Modules\ThreadTom\Busi\GoodsBusi::class
        ],
        self::TOM_REDPACK => [
            'name_en' => 'RedPacket',
            'name_cn' => '红包',
            'service' => \App\Modules\ThreadTom\Busi\RedPackBusi::class
        ],
        self::TOM_REWARD => [
            'name_en' => 'Reward',
            'name_cn' => '悬赏',
            'service' => \App\Modules\ThreadTom\Busi\RewardBusi::class
        ],
        self::TOM_DOC => [
            'name_en' => 'Attachment',
            'name_cn' => '附件',
            'service' => \App\Modules\ThreadTom\Busi\DocBusi::class
        ],
        self::TOM_VOTE => [
            'name_en' => 'Vote',
            'name_cn' => '投票',
            'service' => \App\Modules\ThreadTom\Busi\VoteBusi::class
        ]
    ];


}
