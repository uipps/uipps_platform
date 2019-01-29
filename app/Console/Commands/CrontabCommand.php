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


    // 设置参数接口
    protected function getArguments()
    {
        return [
            ['action', InputArgument::REQUIRED],
            ['p1', InputArgument::OPTIONAL],
        ];
    }

}
