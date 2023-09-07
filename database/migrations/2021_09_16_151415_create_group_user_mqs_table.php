<?php

use Discuz\Database\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;

class CreateGroupUserMqsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->create('group_user_mqs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('group_id')->nullable(false)->default(0)->comment('用户组id');
            $table->bigInteger('user_id')->nullable(false)->default(0)->comment('用户id');
            $table->unsignedInteger('remain_days')->nullable(false)->default(0)->comment('剩余天数');
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
        $this->schema()->dropIfExists('group_user_mqs');
    }
}
