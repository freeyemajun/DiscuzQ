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

namespace App\Console\Commands\Upgrades;


use App\Models\Thread;
use App\Models\ThreadTom;
use App\Modules\ThreadTom\TomConfig;
use Discuz\Console\AbstractCommand;

class UpdateThreadAttachmentCommand extends AbstractCommand
{
    protected $signature = 'update:thread_attachment';
    protected $description='修复部分付费';
    protected function handle()
    {
        $this->info('修复部分付费start');
        $thread_attachment_ids = Thread::query()->where(function ($query){
            $query->where('price', '>', 0)->orWhere('attachment_price', '>', 0);
        })->pluck('id')->toArray();
        $thread_toms = ThreadTom::query()->whereIn('thread_id',$thread_attachment_ids)->whereIn('tom_type',[TomConfig::TOM_IMAGE, TomConfig::TOM_DOC, TomConfig::TOM_AUDIO, TomConfig::TOM_VIDEO])->where(['price_type' => 0])->get();
        foreach ($thread_toms as $val){
            $thread = Thread::query()->find($val->thread_id);
            if($thread->price > 0){         //全贴付费
                $v = json_decode($val->value, 1);
                foreach ($v as $vi){
                    if(!is_array($vi)){
                        $vi = [$vi];
                    }
                    $val->price_ids = json_encode($vi);
                    $val->price_type = 1;
                }
            }else{              //附件付费，只是附件付费
                if($val->tom_type == TomConfig::TOM_DOC){
                    $val->price_type = 1;
                    $v = json_decode($val->value, 1);
                    foreach ($v as $vi){
                        $val->price_ids = json_encode($vi);
                    }
                }
                //对于附件付费帖子，需要将帖子的 free_words 改成 1，文字部分可见
                $thread->free_words = 1;
                $thread->save();
            }
            $val->save();
        }
        $this->info('修复部分付费end');
    }
}
