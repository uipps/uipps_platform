<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::create('users', function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('name', 100)->comment('登录账号');
            $table->string('email', 100)->unique()->comment('邮箱');
            $table->dateTime('email_verified_at')->nullable()->comment('email认证时间');
            $table->string('password')->default('')->comment('密码');
            $table->rememberToken();
            $table->tinyInteger('status')->default(1)->comment('状态：0-删除 1-正常 2-预留其他状态');
            $table->unsignedInteger('created_at')->default(0)->comment('创建时间');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('更新时间');
            $table->engine = 'InnoDB';
            $table->comment = '用户表';
        });*/
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('users');
    }
}
