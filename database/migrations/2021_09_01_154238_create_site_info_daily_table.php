<?php

use Discuz\Database\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;

class CreateSiteInfoDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->create('site_info_dailies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date')->nullable(false)->comment('统计日期');
            $table->integer('mini_active_users')->nullable(false)->default(0)->comment('小程序活跃用户数');
            $table->integer('pc_active_users')->nullable(false)->default(0)->comment('PC端活跃用户数');
            $table->integer('h5_active_users')->nullable(false)->default(0)->comment('h5端活跃用户数');
            $table->integer('new_users')->nullable(false)->default(0)->comment('新增用户数');
            $table->integer('mini_threads')->nullable(false)->default(0)->comment('小程序发帖数');
            $table->integer('pc_threads')->nullable(false)->default(0)->comment('pc发帖数');
            $table->integer('h5_threads')->nullable(false)->default(0)->comment('h5发帖数');
            $table->integer('mini_posts')->nullable(false)->default(0)->comment('小程序回帖数');
            $table->integer('pc_posts')->nullable(false)->default(0)->comment('pc回帖数');
            $table->integer('h5_posts')->nullable(false)->default(0)->comment('h5回帖数');
            $table->integer('threads_sum')->nullable(false)->default(0)->comment('总发帖数');
            $table->integer('posts_sum')->nullable(false)->default(0)->comment('总回复数');
            $table->integer('start_count')->nullable(false)->default(0)->comment('启动数');
            $table->integer('start_peoples')->nullable(false)->default(0)->comment('启动人数');
            $table->integer('orders_count')->nullable(false)->default(0)->comment('订单数');
            $table->decimal('orders_money',12,2)->nullable(false)->default(0.00)->comment('订单金额');
            $table->decimal('withdrawal_profit',12,2)->nullable(false)->default(0.00)->comment('提现收付费收入');
            $table->decimal('order_royalty',12,2)->nullable(false)->default(0.00)->comment('打赏提成收入');
            $table->decimal('total_register_profit',12,2)->nullable(false)->default(0.00)->comment('注册加入收入');
            $table->tinyInteger('is_upload')->nullable(false)->default(0);
            $table->timestamp('created_at')->default(new Expression('CURRENT_TIMESTAMP'))->comment('创建时间');
            $table->timestamp('updated_at')->default(new Expression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->comment('更新时间');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->dropIfExists('site_info_daily');
    }
}
