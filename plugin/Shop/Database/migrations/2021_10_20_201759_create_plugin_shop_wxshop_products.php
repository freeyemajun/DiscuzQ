<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Expression;

class CreatePluginShopWxshopProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->create('plugin_shop_wxshop_products', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment('自增id');
            $table->string('app_id', 64)->nullable(false)->comment('商店appid');
            $table->string('product_id',128)->nullable(false)->comment('微信小商店商品id');
            $table->string('title', 256)->nullable(false)->comment('商品名');
            $table->string('image_path', 256)->nullable(false)->comment('商品图片');
            $table->string('price',32)->nullable(false)->comment('价格');
            $table->string('path',128)->nullable(false)->comment('商品原path');
            $table->string('detail_url',512)->nullable(false)->comment('微信url，小程序，h5直接跳');
            $table->string('detail_qrcode',512)->nullable(false)->comment('外部url，扫码跳');
            $table->integer('is_remote')->nullable(false)->comment('是否放在远程0不1是');
            $table->string("attach_file_name",512)->nullable(false)->comment("附件文件名");
            $table->string("attach_file_path",512)->nullable(false)->comment("附件全路径");
            $table->string('detail_scheme',512)->nullable(false)->comment('外部url，点击跳转');
            $table->timestamp('created_at')->nullable(false)->default(new Expression('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->timestamp('updated_at')->nullable(false)->default(new Expression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('更新时间');

            $table->unique(['app_id','product_id'],'index_app_product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->dropIfExists('plugin_shop_wxshop_products');
    }
}
