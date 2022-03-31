#!/bin/sh

date ;
# Cache the framework bootstrap files
php artisan optimize

php artisan config:cache
php artisan event:cache

# Rebuild the cached package manifest
php artisan package:discover

php artisan route:cache
php artisan view:cache


#shell命令安全执行函数
function execCmd(){
    CMD=$(echo "$1" | sed "s#;;;#__@@__#g")
    OLDIFS=$IFS;IFS=';';
    for CMDCell in $CMD
    do
        CMDCell=$(echo "$CMDCell" | sed "s#__@@__#;#g")
        res=$(eval "$CMDCell" 2>&1)
        if [ "$?" !=  "0" ];then
            echo  "The Shell encountered a fatal error. then exit.This is Error Info."
            eval "printf '%.0s=' {1..50};echo"
        echo "Run Commod: "$CMDCell
            echo "STDERR: "$res
            eval "printf '%.0s=' {1..50};echo"
            echo "please fix it. and go on..."
            exit 100
        fi
    done
    IFS=$OLDIFS
    return 0;
}

# 处理结束, 开始执行shell命令
execCmd "$CMD"
