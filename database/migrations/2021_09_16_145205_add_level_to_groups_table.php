<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddLevelToGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('groups', function (Blueprint $table) {
            $table->unsignedInteger('level')->default(0)->after('fee')->comment('付费用户组等级');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('groups', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
}
