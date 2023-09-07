<?php

use Discuz\Base\DzqPluginMigration;
use Illuminate\Database\Schema\Blueprint;

class AddAdditionalInfoToActivity extends DzqPluginMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('plugin_activity_thread_activity', function (Blueprint $table) {
            $table->string('additional_info_type')->default('{}')->comment('报名必填信息；1：姓名、2：手机号、3：微信号、4：地址；数据形式：{1,2,3,4}');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('plugin_activity_thread_activity', function (Blueprint $table) {
            $table->dropColumn('additional_info_type');
        });
    }
}
