<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterThreadTom extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('thread_tom', function (Blueprint $table) {
            $table->string('tom_type', 50)->comment('帖子插件id')->change();
            $table->string('key', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
