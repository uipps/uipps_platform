<?php

use Illuminate\Database\Seeder;

class project extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 自提点为0的数据，录入一条对应的线路ID
        DB::table('project')->insert([
            'id' => 1,
            'name_cn' => '通用发布系统',
            'type' => 'SYSTEM',
            'parent_id' => 0,
            'table_field_xiangmu_id' => 0,
            'creator' => 0,
            'mender' => '',
            'db_host' => '127.0.0.1',
            'db_name' => 'uipps_platform',
            'db_port' => 3306,
            'db_user' => 'root',
            'db_pwd' => '10y9c2U5',
            'db_timeout' => 0,
            'db_sock' => '',
            'if_use_slave' => 'no',
            'slave_db_host' => '',
            'slave_db_name' => '',
            'slave_db_port' => 0,
            'slave_db_user' => '',
            'slave_db_pwd' => '',
            'slave_db_timeout' => 0,
            'slave_db_sock' => '',
            'if_daemon_pub' => 'no',
            'status' => 'use',
            'search_order' => 0,
            'list_order' => 50,
            'if_hide' => 'no',
            'description' => '',
            'host_id' => 0,
            'res_pub_map' => 0,
            'website_name_cn' => '就你网',
            'waiwang_url' => 'https://www.uipps.com',
            'bendi_uri' => '/data0/htdocs/www',
            'created_at' => time(),
        ]);
    }
}
