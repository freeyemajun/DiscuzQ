<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddPriceTypeToThreadTomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('thread_tom', function (Blueprint $table) {
            $table->tinyInteger('price_type')->default('0')->after('status')->comment('插件/组件是否部分付费');
            $table->string('price_ids')->default('{}')->after('price_type')->comment('插件/组件部分付费id集合');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('thread_tom', function (Blueprint $table) {
            $table->dropColumn('price_type');
            $table->dropColumn('price_ids');
        });
    }
}
