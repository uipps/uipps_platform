#!/usr/bin/env php
<?php
/*
 进行.git和.git_uipps的目录切换，默认是 change toggle(互相切换)
    1) 如果存在.git_uipps和.git，则将.git ==> .git_laravel_master , .git_uipps ==> .git
    2) 如果存在.git_laravel_master和.git，则将.git ==> .git_uipps , .git_laravel_master ==> .git
    3) 如果同时存在.git_laravel_master和.git_uipps，则提示错误，需要人工处理。
    4) 如果.git_laravel_master和.git_uipps两个都不存在，也提示错误，需要人工处理。
    5) 如果.git不存在，也提示错误，需要人工处理。


 使用：
  php owner_change_git.php

 */
$o = getopt('t:');

echo date('Y-m-d H:i:s') . ' begin:' . PHP_EOL;
main($o);

function main($o) {
    // 暂时不用参数吧，默认互相切换就好
    $l_path = __DIR__;
    $l_dir_git = '.git';
    $l_dir_uipps = '.git_uipps';
    $l_dir_laravel = '.git_laravel_master';

    // 目录校验
    // 5. 如果.git不存在，提示错误，需要人工处理
    if (!file_exists($l_path . '/' . $l_dir_git)) {
        echo $l_dir_git . ' not exist!' . PHP_EOL;
        return ;
    }

    // 4. 如果.git_laravel_master和.git_uipps两个都不存在，提示错误，需要人工处理
    if (!file_exists($l_path . '/' . $l_dir_uipps) && !file_exists($l_path . '/' . $l_dir_laravel)) {
        echo $l_dir_laravel . ' And ' . $l_dir_uipps . ' are not exist!' . PHP_EOL;
        return ;
    }

    // 3. 如果.git_laravel_master和.git_uipps两个同时存在，提示错误，需要人工处理
    if (file_exists($l_path . '/' . $l_dir_uipps) && file_exists($l_path . '/' . $l_dir_laravel)) {
        echo $l_dir_laravel . ' And ' . $l_dir_uipps . ' are simultaneously exist!' . PHP_EOL;
        return ;
    }

    // 1. 如果存在.git_uipps和.git，则将.git ==> .git_laravel_master , .git_uipps ==> .git
    if (file_exists($l_path . '/' . $l_dir_uipps)) {
        // 注意先后顺序
        rename($l_path . '/' . $l_dir_git, $l_path . '/' . $l_dir_laravel);
        rename($l_path . '/' . $l_dir_uipps, $l_path . '/' . $l_dir_git);
        echo ' toggle success! ' . $l_dir_uipps . ' ===> ' . $l_dir_git . PHP_EOL;
        return ;
    }

    // 2. 如果存在.git_laravel_master和.git，则将.git ==> .git_uipps , .git_laravel_master ==> .git
    if (file_exists($l_path . '/' . $l_dir_laravel)) {
        rename($l_path . '/' . $l_dir_git, $l_path . '/' . $l_dir_uipps);
        rename($l_path . '/' . $l_dir_laravel, $l_path . '/' . $l_dir_git);
        echo ' toggle success! ' . $l_dir_laravel . ' ===> ' . $l_dir_git . PHP_EOL;
        return ;
    }

    echo date('Y-m-d H:i:s') . ' 就问你是怎么到这里来的！' . PHP_EOL;
    return ;
}
