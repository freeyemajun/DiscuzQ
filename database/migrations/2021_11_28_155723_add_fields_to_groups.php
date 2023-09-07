<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddFieldsToGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('groups', function (Blueprint $table) {
            $table->integer('time_range')->default(0)->comment('访问的时间范围(天)');
            $table->integer('content_range')->default(0)->comment('访问的内容范围(天)');
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
            $table->dropColumn('time_range');
            $table->dropColumn('content_range');
        });
    }
}
