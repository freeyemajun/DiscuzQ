<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddThreeTerminalStartToSiteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('site_info_dailies', function (Blueprint $table) {
            $table->unsignedBigInteger('pc_start_count')->default(0)->after('start_count')->comment('pc端启动数');
            $table->unsignedBigInteger('h5_start_count')->default(0)->after('pc_start_count')->comment('h5端启动数');
            $table->unsignedBigInteger('mini_start_count')->default(0)->after('h5_start_count')->comment('小程序端启动数');
            $table->unsignedBigInteger('pc_start_peoples')->default(0)->after('start_peoples')->comment('pc端启动人数');
            $table->unsignedBigInteger('h5_start_peoples')->default(0)->after('pc_start_peoples')->comment('h5端启动人数');
            $table->unsignedBigInteger('mini_start_peoples')->default(0)->after('h5_start_count')->comment('小程序端启动人数');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('site_info_dailies', function (Blueprint $table) {
            $table->unsignedBigInteger('pc_start_count')->change();
            $table->unsignedBigInteger('h5_start_count')->change();
            $table->unsignedBigInteger('mini_start_count')->change();
            $table->unsignedBigInteger('pc_start_peoples')->change();
            $table->unsignedBigInteger('h5_start_peoples')->change();
            $table->unsignedBigInteger('mini_start_peoples')->change();

        });
    }
}
