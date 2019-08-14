<?php
// php artisan InitProject InitProjectTableField
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

    // table_def,field_def数据
    private function InitProjectTableField($request) {
        // 主要参数是db_name=grab&if_repair
        // 主要功能是创建数据库（如果数据库存在则按照项目特点进行补充和修正）、数据表和字段
        // 具备修复功能，保持同真实数据表一致的功能
        $this->info(date('Y-m-d H:i:s') . ' begin to process:' . self::NEW_LINE_CHAR);
        \DB::statement('drop table if exists migrations'); // 删除迁移库

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
        $this->info(__FILE__ . ' ' . __LINE__ );
        echo __FILE__ . ' ' . __LINE__ . self::NEW_LINE_CHAR;
        print_r($l_tmp);exit;
        $dbW = new DBW();
        $table_def = 'table_def';
        $field_def = 'field_def';
        $a_data_arr = array("source"=>'db',"creator"=>$creator);  // 能在外部增加字段的

        DbHelper::fill_table($dbR, $dbW, $a_data_arr,"all",$field_def,$table_def,$p_arr["id"]);
        DbHelper::fill_field($dbR, $dbW, $a_data_arr,"all",$field_def,$table_def);


        // ------ 如果有额外的初始化数据需要insert或update的时候
        $l_e_wai  = file_get_contents(database_path('migrations/uipps_init_insert.sql'));
        $l_e_tmpl = file_get_contents(database_path('migrations/tmpl_design_init_insert.sql'));
        if ($l_e_wai) {
            // insert或update一些初始数据
            DbHelper::execDbWCreateInsertUpdate($dbW, $l_e_tmpl . "\r\n ". $l_e_wai, array("INSERT INTO ","REPLACE INTO ","UPDATE "));
        }

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
