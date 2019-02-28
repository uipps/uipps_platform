<?php

namespace App\Console\Commands;

use App\Services\Admin\ProjectService;
use App\Repositories\Admin\ProjectRepository;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use DbHelper;
use DBR;
use DBW;


class InitProjectCommand extends Command
{
    const NEW_LINE_CHAR = "\r\n";

    protected $name = 'InitProjectCommand';
    protected $description = '项目初始化';

    public function handle()
    {
        $argvList = $this->argument();
        $action = $argvList['action'];
        $data = $this->$action($argvList);

        var_dump($data);
    }

    // 填充table_def,field_def数据 php artisan crontab fill_db_table_field uipps_platform
    private function InitTableField($request) {
        // 主要参数是db_name=grab&if_repair
        // 主要功能是创建数据库（如果数据库存在则按照项目特点进行补充和修正）、数据表和字段
        // 具备修复功能，保持同真实数据表一致的功能
        $this->info(date('Y-m-d H:i:s') . ' begin to process:' . self::NEW_LINE_CHAR);

        $_SESSION = session()->all();
        $creator = 1;
        if (isset($_SESSION['user']) && $_SESSION['user'] && isset($_SESSION['user']['id'])) {
            $creator = $_SESSION['user']['id'];
        }

        //try { } catch (\QueryException $e) { echo $e->getMessage(); } // catch不了
        $dbR = new DBR();
        $dbR->table_name = "project";
        $p_arr = $dbR->getOne('order by id');
        if (!$p_arr) {
            $this->info(date('Y-m-d H:i:s') . ' project list is empty!! '. self::NEW_LINE_CHAR);
            return 0;
        }
        // 简单校验
        $l_tmp = DbHelper::getAllDB($dbR);
        if ( !in_array($p_arr['db_name'], $l_tmp) ) {
            $this->info(date('Y-m-d H:i:s') . ' db_name is error!! '. self::NEW_LINE_CHAR);
            return 0;
        }
        $dbW = new DBW();
        $table_def = 'table_def';
        $field_def = 'field_def';
        $a_data_arr = array("source"=>'db',"creator"=>$creator);  // 能在外部增加字段的

        DbHelper::fill_table($dbR, $dbW, $a_data_arr,"all",$field_def,$table_def,$p_arr["id"]);
        DbHelper::fill_field($dbR, $dbW, $a_data_arr,"all",$field_def,$table_def);


        $this->info(date('Y-m-d H:i:s') . ' Done!' . self::NEW_LINE_CHAR);
        return 1;
    }


    // 设置参数接口
    protected function getArguments()
    {
        return [
            ['action', InputArgument::REQUIRED],
            ['p1', InputArgument::OPTIONAL],
            ['p2', InputArgument::OPTIONAL],
            ['pro_type', InputArgument::OPTIONAL],
        ];
    }

}
