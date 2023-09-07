<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddTypeToAdminActionLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('admin_action_logs', function (Blueprint $table) {
            $table->string('type', 5)->default('')->after('id')->comment('操作类型');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('admin_action_logs', function (Blueprint $table) {
            $table->string('type');
        });
    }
}
