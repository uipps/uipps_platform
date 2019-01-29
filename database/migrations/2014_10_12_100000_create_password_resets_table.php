<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePasswordResetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('email')->index()->comment('邮箱');
            $table->string('token')->comment('口令token');
            $table->dateTime('created_at')->nullable()->comment('创建时间');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('更新时间');
            $table->engine = 'InnoDB';
            $table->comment = '密码重置表';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('password_resets');
    }
}
