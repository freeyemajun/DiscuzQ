<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddReceiveAccountToUserWalletCash extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('user_wallet_cash', function (Blueprint $table) {
            $table->string('receive_account')->default('')->comment('收款账号');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('user_wallet_cash', function (Blueprint $table) {
            $table->dropColumn('receive_account');
        });
    }
}
