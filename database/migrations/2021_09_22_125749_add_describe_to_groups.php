<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddDescribeToGroups extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('groups', function (Blueprint $table) {
            $table->string('description', 255)->default('')->after('is_commission')->comment('特权描述');
            $table->string('notice', 255)->default('')->after('description')->comment('须知');
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
            $table->dropColumn('description');
            $table->dropColumn('notice');
        });
    }
}
