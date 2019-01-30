<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;


class CrontabCommand extends Command
{
    protected $name = 'CrontabCommand';
    protected $description = '后台定时脚本';

    public function handle()
    {
        $argvList = $this->argument();
        $action = $argvList['action'];
        $data = $this->$action($argvList);

        print_r($data);
    }

    // 获取项目列表信息
    private function getProjectList($params)
    {
        $this->info("start getProjectList");

        $orderId = $params['p1'];
        //$order = \App\Repositories\Delivery\DeliveryRepository::a();
        echo $orderId;
        // TODO
        return true;
    }

    private function replaceBladeContentTags() {
        $l_path =  realpath(dirname(dirname(dirname(__DIR__))));
        $l_file = 'vendor/laravel/framework/src/Illuminate/View/Compilers/BladeCompiler.php';
        if (!file_exists($l_path . '/' .$l_file)) {
            echo 'file not found: ' . $l_file;
            return 0;
        }
        $content = file_get_contents($l_path . '/' .$l_file);

        // 替换其中的ContentTags,
        $str = "//protected \$contentTags = ['{{', '}}'];";
        if (false !== strpos($content, $str)) {
            echo 'already replace!';
            return 1;
        }
        $str_from = "protected \$contentTags = ['{{', '}}'];";
        if (false === strpos($content, $str_from)) {
            echo 'something wrong, not found: ' . $str_from;
            return 0;
        }
        // 备份文件
        @file_put_contents($l_path . '/' .$l_file . '.back.php', $content, FILE_APPEND);

        $replace_to = "//protected \$contentTags = ['{{', '}}'];\r\n    protected \$contentTags = ['<!--{', '}-->'];";
        $content = str_replace($str_from, $replace_to, $content);
        echo $content;
        file_put_contents($l_path . '/' .$l_file, $content); // 覆盖原文件
        return 3;
    }


    // 设置参数接口
    protected function getArguments()
    {
        return [
            ['action', InputArgument::REQUIRED],
            ['p1', InputArgument::OPTIONAL],
        ];
    }

}
