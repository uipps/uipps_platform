#!/bin/bash
php artisan migrate:fresh

php artisan db:seed --class=project

php artisan initproject InitTableField

#php ~/uipps_platform/artisan crontabCommand createProject "name_cn=网盟建站后台&type=PHP_PROJECT&table_field_belong_project_id=1&db_host=127.0.0.1&db_name=jianzhan_network_union_back_191121&db_port=3306&db_user=root&db_pwd=10y9c2U5&if_use_slave=F&if_daemon_pub=F&website_name_cn=网盟建站后台&waiwang_url=http://www.uipps.com&bendi_uri=/data0/htdocs/www"

#php ~/uipps_platform/artisan crontabCommand createProject "name_cn=Idvert商城&type=PHP_PROJECT&table_field_belong_project_id=1&db_host=127.0.0.1&db_name=niushop_b2c_back_distribute_191121&db_port=3306&db_user=root&db_pwd=10y9c2U5&if_use_slave=F&if_daemon_pub=F&website_name_cn=Idvert商城&waiwang_url=http://www.uipps.com&bendi_uri=/data0/htdocs/www"
