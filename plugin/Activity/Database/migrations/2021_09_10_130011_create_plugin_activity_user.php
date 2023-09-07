<?php

use Discuz\Base\DzqPluginMigration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;

class CreatePluginActivityUser extends DzqPluginMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->create('plugin_activity_user', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment('自增id');
            $table->unsignedBigInteger('thread_id')->nullable(false)->comment('主题id');
            $table->unsignedBigInteger('activity_id')->nullable(false)->comment('活动id');
            $table->unsignedBigInteger('user_id')->nullable(false)->comment('用户id');
            $table->tinyInteger('status')->default(1)->comment('0:无效 1：有效');
            $table->timestamp('created_at')->nullable(false)->default(new Expression('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->timestamp('updated_at')->nullable(false)->default(new Expression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('更新时间');
            $table->index('thread_id');
            $table->index(['activity_id','user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->dropIfExists('plugin_activity_users');
    }
}
