<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMsgToChatgptkernelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('chatgptkernels', function (Blueprint $table) {
            $table->integer('total_tokens')->default(0)->comment('total_tokens');
            $table->integer('completion_tokens')->default(0)->comment('completion_tokens');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('chatgptkernels', function (Blueprint $table) {
            $table->dropColumn('total_tokens');
            $table->dropColumn('completion_tokens');
        });
    }
}
