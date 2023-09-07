<?php

use Discuz\Base\DzqPluginMigration;
use Illuminate\Database\Schema\Blueprint;

class AddAdditionalInfoToPluginActivityUser extends DzqPluginMigration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('plugin_activity_user', function (Blueprint $table) {
            $table->string('additional_info')->default('')->comment('报名必填信息');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('plugin_activity_user', function (Blueprint $table) {
            $table->dropColumn('additional_info');
        });
    }
}
