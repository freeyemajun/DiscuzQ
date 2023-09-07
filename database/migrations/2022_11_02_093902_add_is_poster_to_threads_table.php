<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddIsPosterToThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('threads', function (Blueprint $table) {
            $table->tinyInteger('is_poster')->unsigned()->default(0)->after('is_approved')->comment('是否设置为海报');
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('threads', function (Blueprint $table) {
            $table->dropColumn('is_poster');
            //
        });
    }
}
