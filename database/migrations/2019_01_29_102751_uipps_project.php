<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UippsProject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 主机
        Schema::create('host_reg', function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('host_label', 50)->unique()->comment('主机卷标');
            $table->string('host_name', 200)->unique()->comment('主机名称');
            $table->string('host_os', 200)->comment('主机的操作系统');
            $table->string('host_ip', 15)->comment('主机的ip'); //
            $table->string('host_domain', 100)->comment('主机域名');
            $table->string('host_port', 100)->comment('主机端口');
            $table->string('host_root', 200)->comment('主机root');
            $table->string('description', 1000)->default('')->comment('描述');
            $table->string('creator', 100)->default('')->comment('创建者');
            $table->string('mender', 100)->default('')->comment('修改者');
            $table->unsignedInteger('created_at')->default(0)->comment('创建时间');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('更新时间');
            $table->engine = 'InnoDB';
            $table->comment = '全部项目';
        });

        // 项目
        Schema::create('project', function (Blueprint $table) {
            $table->increments('id')->comment('自增ID');
            $table->string('name_cn', 200)->unique()->comment('中文名称');
            $table->enum('type', ['SYSTEM','CMS','PHP_PROJECT','NORMAL','PUB','RES','GRAB'])->default('CMS')->comment('项目类型');
            $table->unsignedInteger('parent_id')->default(0)->comment('所属父级ID');
            $table->unsignedInteger('table_field_xiangmu_id')->default(0)->comment('字段定义表所属项目ID, 0:表示所属项目, 外来的项目可能不是0');
            $table->string('creator', 100)->default('')->comment('创建者');
            $table->string('mender', 100)->default('')->comment('修改者');
            // 主库
            $table->string('db_host', 50)->default('localhost')->comment('数据库主机');
            $table->string('db_name', 50)->default('')->comment('数据库名称, 英文名称');
            $table->unsignedInteger('db_port')->default(3306)->comment('数据库端口');
            $table->string('db_user', 20)->default('root')->comment('数据库用户名');
            $table->string('db_pwd', 20)->default('')->comment('数据库密码');
            $table->unsignedInteger('db_timeout')->default(0)->comment('数据库超时时间');
            $table->string('db_sock', 100)->default('')->comment('数据库socket位置');
            $table->enum('if_use_slave', ['yes','no'])->default('no')->comment('是否使用从库');
            // 从库
            $table->string('slave_db_host', 50)->default('')->comment('从库主机名');
            $table->string('slave_db_name', 50)->default('')->comment('从库数据库名');
            $table->unsignedInteger('slave_db_port')->default(3306)->comment('从库端口');
            $table->string('slave_db_user', 20)->default('root')->comment('从库用户名');
            $table->string('slave_db_pwd', 20)->default('')->comment('从库密码');
            $table->unsignedInteger('slave_db_timeout')->default(0)->comment('从库超时时间');
            $table->string('slave_db_sock', 100)->default('')->comment('从库socket位置');
            // 其他
            $table->enum('if_daemon_pub', ['yes','no'])->default('no')->comment('是否后台发布');
            $table->enum('status', ['use','stop','test','del','scrap','open','pause','close'])->default('use')->comment('状态, 使用、停用等');
            $table->unsignedInteger('search_order')->index()->default(0)->comment('搜索顺序');
            $table->unsignedInteger('list_order')->default(50)->comment('显示顺序');
            $table->enum('if_hide', ['yes','no'])->default('no')->comment('是否隐藏');
            $table->string('description', 1000)->default('')->comment('描述');
            $table->unsignedInteger('host_id')->default(0)->comment('主机id');
            $table->unsignedInteger('res_pub_map')->default(0)->comment('发布地图');
            $table->string('website_name_cn', 200)->default('就你网')->comment('网站中文名称, 该项目的');
            $table->string('waiwang_url', 255)->default('https://www.uipps.com')->comment('外网URL');
            $table->string('bendi_uri', 255)->default('/data0/htdocs/www')->comment('本地URI, D:/www/htdocs/www.uipps.com');

            $table->unsignedInteger('created_at')->default(0)->comment('创建时间');
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('更新时间');
            $table->engine = 'InnoDB';
            $table->comment = '全部项目';
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('project');
        Schema::table('loginlog', function (Blueprint $table) {
            //
        });
    }
}
