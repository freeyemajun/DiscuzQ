<?php

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddisDownloadedToShare extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->table('attachments_share', function (Blueprint $table) {
            $table->unsignedSmallInteger('is_downloaded')->default(0)->after('download_count')->comment('是否下载 0没有下载 1已下载');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->table('attachments_share', function (Blueprint $table) {
            $table->smallInteger('is_downloaded')->default(0)->after('download_count')->comment('是否下载 0没有下载 1已下载');
        });
    }
}
