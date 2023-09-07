<?php

/**
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use Discuz\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateChatgptkernels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema()->create('chatgptkernels', function (Blueprint $table) {
            $table->id()->comment('编号');
            $table->string('toid', 200)->default('')->comment('对象ID');
            $table->integer('status')->unsigned()->default(0)->index()->comment('处理状态');
            $table->string('type', 10)->default('')->comment('发送类型');
            $table->integer('msg_type')->unsigned()->nullable()->comment('发送类型');
            $table->unsignedBigInteger('dataline')->nullable()->comment('时间戳');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema()->dropIfExists('chatgptkernels');
    }
}
