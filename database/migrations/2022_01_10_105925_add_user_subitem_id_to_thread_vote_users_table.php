<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUserSubitemIdToThreadVoteUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('thread_vote_users', function (Blueprint $table) {
            $table->unique(['user_id','thread_vote_subitem_id'],'user_subitem_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('thread_vote_users', function (Blueprint $table) {
            $table->dropIndex('user_subitem_id');
        });
    }
}
