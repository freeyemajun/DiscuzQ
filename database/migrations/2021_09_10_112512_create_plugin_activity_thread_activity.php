<?php


use Discuz\Base\DzqPluginMigration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;

class CreatePluginActivityThreadActivity extends DzqPluginMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->create('plugin_activity_thread_activity', function (Blueprint $table) {
            $table->unsignedBigInteger('id', true)->comment('自增id');
            $table->unsignedBigInteger('user_id')->comment('用户id');
            $table->unsignedBigInteger('thread_id')->comment('帖子id');
            $table->string('title', 100)->nullable(false)->comment('活动名称');
            $table->text('content')->comment('活动内容');
            $table->dateTime('activity_start_time')->comment('活动开始时间');
            $table->dateTime('activity_end_time')->comment('活动结束时间');
            $table->dateTime('register_start_time')->nullable(true)->comment('报名开始时间');
            $table->dateTime('register_end_time')->nullable(true)->comment('报名结束时间');
            $table->integer('total_number')->default(0)->comment('报名人数上限 0:不限制');
            $table->string('address', 200)->nullable(false)->default('')->comment('地址信息');
            $table->string('location', 200)->nullable(true)->default('')->comment('位置信息');
            $table->decimal('longitude', 10, 7)->default(0.0000000)->nullable(false)->comment('经度');
            $table->decimal('latitude', 10, 7)->default(0.0000000)->nullable(false)->comment('纬度');
            $table->tinyInteger('status')->default(1)->comment('0:无效 1：有效');
            $table->timestamp('created_at')->nullable(false)->default(new Expression('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->timestamp('updated_at')->nullable(false)->default(new Expression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('更新时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->dropIfExists('plugin_activity_thread_activity');
    }
}
