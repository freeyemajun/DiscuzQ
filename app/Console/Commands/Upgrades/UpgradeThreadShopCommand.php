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


use App\Models\ThreadTag;
use App\Models\ThreadTom;
use App\Modules\ThreadTom\TomConfig;
use Discuz\Console\AbstractCommand;
use Illuminate\Database\DatabaseManager;

class UpgradeThreadShopCommand extends AbstractCommand
{
    protected $signature = 'upgrade:threadShop';
    protected $description='商品插件迁移原商品贴';
    protected function handle()
    {

        $shop_app_id = "61540fef8f4de8";
        /** @var DatabaseManager $dbmgr */
        try {
            ThreadTag::query()->where("tag",(string)TomConfig::TOM_GOODS)
                ->update(["tag"=>$shop_app_id]);

            $goodsData = ThreadTom::query()->where("tom_type",(string)TomConfig::TOM_GOODS)->get();
            foreach ($goodsData as $item){
                $productData = [];
                $oneProduct = [];
                $oneProduct["type"] = 10;
                $dataOld = json_decode($item->value,true);
                $oneProduct["data"] = $dataOld;
                $productData[] = $oneProduct;
                $data["products"] = $productData;
                $dataNew = json_encode($data,256);

                ThreadTom::query()->where("id",$item->id)->update(["tom_type"=>$shop_app_id,"key"=>$shop_app_id,"value"=>$dataNew]);
            }
            $this->info('商品插件迁移原商品贴成功。');
        }catch (\Exception $e){
            $this->info('UpgradeThreadShopCommand', [], $e->getMessage());
            $this->info('脚本执行 [异常]');
        }
    }
}
