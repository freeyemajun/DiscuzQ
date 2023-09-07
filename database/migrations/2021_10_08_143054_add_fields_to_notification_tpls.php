<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddFieldsToNotificationTpls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('notification_tpls', function (Blueprint $table) {
            $table->tinyInteger('push_type')->default(0)->comment('消息推送类型(0:即时推送,1:间隔推送)');
            $table->unsignedInteger('delay_time')->default(0)->comment('间隔推送延迟时间(秒)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('notification_tpls', function (Blueprint $table) {
            $table->dropColumn('push_type');
            $table->dropColumn('delay_time');
        });
    }
}
