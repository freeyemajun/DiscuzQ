<?php

use Discuz\Database\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;

class CreatePluginSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->create('plugin_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment('自增id');
            $table->string('app_id', 100)->nullable(false)->comment('插件应用id');
            $table->string('app_name', 100)->nullable(false)->comment('插件唯一英文名');
            $table->tinyInteger('type')->nullable(false)->comment('插件类型');
            $table->text('value')->comment('JSON存储配置信息');
            $table->timestamp('created_at')->nullable(false)->default(new Expression('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->timestamp('updated_at')->nullable(false)->default(new Expression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('更新时间');
            $table->unique('app_id');
            $table->unique('app_name');
            $table->index(['app_id','type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->dropIfExists('plugin_settings');
    }
}
