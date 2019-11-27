<?php
// 初始安装
$root_path = __DIR__;

$cmd_list = [
    //'cd /d ' . $root_path . ' ; composer update',
    'php ' . $root_path . '/artisan migrate:fresh',
    'php ' . $root_path . '/artisan db:seed --class=project',
    'php ' . $root_path . '/artisan initproject InitTableField',
];

// 不同系统命令不一样
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // windows 下：
    $l_cmd = [
        'php ' . $root_path . '/artisan crontabCommand createProject "name_cn=media媒体建站&type=PHP_PROJECT&table_field_belong_project_id=1&db_host=127.0.0.1&db_name=media_order&db_port=3511&db_user=root&db_pwd=10y9c2U5&if_use_slave=F&if_daemon_pub=F&website_name_cn=媒体建站系统&waiwang_url=http://www.uipps.com&bendi_uri=/data0/htdocs/www"',
        'php ' . $root_path . '/artisan crontabCommand createProject "name_cn=Idvert商城&type=PHP_PROJECT&table_field_belong_project_id=1&db_host=127.0.0.1&db_name=niushop_b2c&db_port=3515&db_user=root&db_pwd=10y9c2U5&if_use_slave=F&if_daemon_pub=F&website_name_cn=Idvert商城&waiwang_url=http://www.uipps.com&bendi_uri=/data0/htdocs/www"',
    ];
    //$cmd_list = array_merge($cmd_list, $l_cmd);
} else if ('DARWIN' === strtoupper(PHP_OS)) {
    // MacOs 下：
    $l_cmd = [
        'php ' . $root_path . '/artisan crontabCommand createProject "name_cn=网盟建站后台&type=PHP_PROJECT&table_field_belong_project_id=1&db_host=127.0.0.1&db_name=jianzhan_network_union_back_191121&db_port=3306&db_user=root&db_pwd=10y9c2U5&if_use_slave=F&if_daemon_pub=F&website_name_cn=网盟建站后台&waiwang_url=http://www.uipps.com&bendi_uri=/data0/htdocs/www"',
        'php ' . $root_path . '/artisan crontabCommand createProject "name_cn=Idvert商城&type=PHP_PROJECT&table_field_belong_project_id=1&db_host=127.0.0.1&db_name=niushop_b2c_back_distribute_191121&db_port=3306&db_user=root&db_pwd=10y9c2U5&if_use_slave=F&if_daemon_pub=F&website_name_cn=Idvert商城&waiwang_url=http://www.uipps.com&bendi_uri=/data0/htdocs/www"',
    ];
    $cmd_list = array_merge($cmd_list, $l_cmd);
} else if ('CYGWIN' === strtoupper(PHP_OS)) {
    // windows的cygwin下：暂未测试
    $cmd_list = [];
} else {
    // Linux 下：暂未测试
    $cmd_list = [];
}

if ($cmd_list) {
    foreach ($cmd_list as $cmd) {
        echo date('Y-m-d H:i:s') . " exec command: $cmd \r\n";exec($cmd, $out_put, $ret);
        echo date('Y-m-d H:i:s') . " exec result: \r\n" . implode("\r\n", $out_put);echo "\r\n";
        echo date('Y-m-d H:i:s') . " sleep: 5 second!\r\n"; sleep(5);

        //echo "\r\nsystem:\r\n";system($cmd);
        //echo "\r\npassthru:\r\n";passthru($cmd);
    }
}
