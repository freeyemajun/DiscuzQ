<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlertPublicValueToPluginSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('plugin_settings', function (Blueprint $table) {
            //
            $table->renameColumn("value","public_value");
            $table->text("private_value")->comment("私有数据")->after("type");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('plugin_settings', function (Blueprint $table) {
            //
            $table->renameColumn("public_value","value");
            $table->dropColumn("private_value");
        });
    }
}
