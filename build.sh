#!/bin/sh

date ;
php artisan clear-compiled
php artisan cache:clear
php artisan config:clear
php artisan event:clear
php artisan route:clear
php artisan view:clear

## git -C /www/html/test/uipps-api status  && \
## git status  && \
#git checkout -- .  && \
#git clean -fd  && \
#git pull

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


ACTION=$1
CMD=""
if [ "$ACTION" == "git_clear" ] ;then #恢复修改的文件和清理非git文件
    ## git -C /www/html/test/uipps-api status  && \
    CMD=$CMD"git status ;"
    CMD=$CMD"git checkout -- . ;"
    CMD=$CMD"git clean -fd ;"
    CMD=$CMD"git pull;"
elif [ "$ACTION" == "git_clean" ] ;then #只clean
    CMD=$CMD"git clean -fd;"
fi


# 处理结束, 开始执行shell命令
execCmd "$CMD"
