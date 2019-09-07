<?php
// php F:/develope/php/www/uipps_platform/artisan crontabCommand getProjectList
// php artisan crontab fill_db_table_field uipps_platform
namespace App\Console\Commands;

use App\Http\Controllers\Project\ProjectAddController;
use App\Services\Admin\ProjectService;
use App\Repositories\Admin\ProjectRepository;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Symfony\Component\Console\Input\InputArgument;
use DbHelper;
//use GuzzleHttp\Client;
use DBR;
use DBW;


class CrontabCommand extends Command
{
    const NEW_LINE_CHAR = "\r\n";

    protected $name = 'CrontabCommand';
    protected $description = '后台定时脚本';

    // 设置参数接口
    protected function getArguments()
    {
        return [
            ['action', InputArgument::REQUIRED],
            ['db_name', InputArgument::OPTIONAL], // 创建项目的时候是db_info, 包括db_host
            //['project_info', InputArgument::OPTIONAL],
        ];
    }

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
        $this->info('start getProjectList');

        $projectService = new ProjectService(new ProjectRepository());
        $p_list = $projectService->getProjectList($params);
        //print_r($p_list);

        $orderId = $params['db_name'];
        //$order = \App\Repositories\Delivery\DeliveryRepository::a();
        echo $orderId;
        // TODO
        return $p_list;
    }

    // 后台创建项目，包括主从库设置等
    private function createProject($request) {
        $this->info('start createProject');

        if (!isset($request['db_name']) ) {
            $this->info(date('Y-m-d H:i:s') . ' project-info must not be empty!! '. self::NEW_LINE_CHAR);
            return 0;
        }
        parse_str($request['db_name'], $project_info);
        //$project_info = parse_query($request['db_name']);
        if (!$project_info || !isset($project_info['db_host'])) {
            $this->info(date('Y-m-d H:i:s') . ' project-info decode error! '. self::NEW_LINE_CHAR);
            return 0;
        }
        //print_r($project_info);exit;
        return createProjectBy($project_info);


        /* 失败
        $project_add = new ProjectAddController(new \App\Services\Admin\UserService());
        //$project_add->setMethod('POST');
        $a_request = new Request();
        $_POST = $request;
        //$a_request->all();
        $a_request->setMethod('POST');
        $project_add->execute($a_request);*/


        /* 网页请求的方法 /project/add
        $l_options = [];
        $l_method = 'POST'; // 有数据则为post方法
        $l_url = '/project/add';
        $req = new \GuzzleHttp\Client($l_options);
        try {
            $response = $req->request($l_method, $l_url);
        } catch (\Exception $e) {
            echo $e->getCode() . ' ' . $e->getMessage();
            return '';
        }
        echo $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $l_header = $response->getHeaders();	// 返回的头信息
        return $html_content = $response->getBody()->getContents();	// 抓取到了页面内容*/
    }

    // 填充table_def,field_def数据 php artisan crontab fill_db_table_field uipps_platform
    private function fill_db_table_field($request) {
        // 主要参数是db_name=grab&if_repair
        // 主要功能是创建数据库（如果数据库存在则按照项目特点进行补充和修正）、数据表和字段
        // 具备修复功能，保持同真实数据表一致的功能
        $this->info(date('Y-m-d H:i:s') . ' begin to process:' . self::NEW_LINE_CHAR);

        // 只允许cli后台运行，主要是完成一些任务。输出调试信息等  web测试的时候有&&&&安全隐患&&&&
        if (php_sapi_name() != 'cli') {
            $this->info(date('Y-m-d H:i:s') . ' this must be run command line module!!' . self::NEW_LINE_CHAR);
            return 0;  // 总是返回此结果
        }

        // 参数检验可以放到validate中去，先放到此处检验一下。
        if (!array_key_exists('db_name', $request)) {
            $this->info(date('Y-m-d H:i:s') . ' db_name must not be empty!! '. self::NEW_LINE_CHAR);
            return 0;
        }
        /*
        if (!isset($request['db_name']) || !$request['db_name'] || !json_decode($request['db_name'])) {
            $this->info(date('Y-m-d H:i:s') . ' project_info is empty, can\'t create new project!' . self::NEW_LINE_CHAR);
            return 0;
        }
        if (!array_key_exists('db_pwd', $request)) {
            $this->info(date('Y-m-d H:i:s') . ' db_pwd must not be empty!! '. self::NEW_LINE_CHAR);
            return 0;
        }
        if (!array_key_exists('pro_type', $request)) {
            $this->info(date('Y-m-d H:i:s') . ' pro_type must not be empty!! '. self::NEW_LINE_CHAR);
            return 0;
        }*/
        //  1. 检查table_def， field_def 两张表是否存在
        //$this->info('1. 检查table_def， field_def 两张表是否存在');

        // 获取项目列表
        $projectService = new ProjectService(new ProjectRepository());
        $p_list = $projectService->getProjectList($request);
        if (!$p_list) {
            $this->info(date('Y-m-d H:i:s') . ' project list is empty!! '. self::NEW_LINE_CHAR);
            return 0;
        }
        // 按照项目名称（db_name）重新组织数组
        $proj_list = [];
        foreach ($p_list as $item)
            $proj_list[$item['db_name']] = $item;

        // 调试信息字符串
        $l_str = '';

        if (isset($proj_list[$request['db_name']]) && $proj_list[$request['db_name']]) {
            // 项目增加以及修改项目联合起来，建表、创建字段，添加数据。修改表结构
            $p_arr = $proj_list[$request['db_name']];
            //print_r($p_arr);exit;
            if ( !array_key_exists('table_name',$request) ) {
                // 如果不存在，则需要进行必要的表修补
                $rlt = DbHelper::createDBandBaseTBL($p_arr);
            } else {
                // 如果带有表名称是进行表的字段修复，
                // 1 检查表是否存在，不存在则返回

                // 2 存在则继续依据真实的表结构填充字段定义表
//                $dsn = DbHelper::getDSNstrByProArrOrIniArr($p_arr);
//                $dbR->dbo = &DBO('', $dsn);
//                //$dbR = null;$dbR = new DBR($p_arr);
//                $dbW = new DBW($p_arr);
//                $l_data_arr = array('source'=>'db','creator'=>empty($_SESSION['user']['username'])?'admin':$_SESSION['user']['username']);
//                //print_r($l_data_arr);
//                DbHelper::fill_field($dbR,$dbW,$l_data_arr,$request['table_name'],TABLENAME_PREF.'field_def',TABLENAME_PREF.'table_def', $request['if_repair']);
            }

            $this->info(date('Y-m-d H:i:s') . ' Done!' . self::NEW_LINE_CHAR);
            return 1;
        }
        if (!isset($request['db_name']) || !$request['db_name'] || !json_decode($request['db_name'])) {
            $this->info(date('Y-m-d H:i:s') . ' project_info is empty, can\'t create new project!' . self::NEW_LINE_CHAR);
            return 0;
        }
        $tmp = parse_str($request['db_name']);
        $request = array_merge($tmp, $request);

        // 项目表中没有此项目的话，则需要入库一下，同时创建一个数据库
        $dbR = new \DBR();
        $l_srv_db_dsn = $dbR->getDSN("array");
        // 自动获取默认数组
        $l_ins_arr = $dbR->getInSertArr();
        $data_arr = $l_ins_arr[1];  // 只需要必须的字段
        //print_r($data_arr);

        // 需要在外部修改一下
        $data_arr['name_cn']   = array_key_exists('name_cn',$request)?$request['name_cn']:$request['db_name'];
        $data_arr['type']    = $request['pro_type'];
        $data_arr['db_name']  = $request['db_name'];
        $data_arr['db_pwd']    = array_key_exists('db_pwd',$request)?$request['db_pwd']:$l_srv_db_dsn['password'];
        if (array_key_exists('db_port',$request)) $data_arr['db_port'] = $request['db_port'];

        // 系统本身不应该在此处默认的dbr连接信息的数据库中
        //if ('SYSTEM'!=strtoupper($request['pro_type'])) {
        $dbW = new \DBW();
        $dbW->table_name = env('DB_PREFIX', '') .'project';
        $pid = $dbW->insertOne($data_arr);
        if ($pid <= 0){
            // sql有错误，后面的就不用执行了。
            $response['html_content'] = self::NEW_LINE_CHAR . date('Y-m-d H:i:s') . ' FILE: '.__FILE__.' '. ' FUNCTION: '.__FUNCTION__.' Line: '. __LINE__ . ' SQL: '.$dbW->getSQL().', _arr:' . var_export($data_arr, TRUE);
            $response['ret'] = array('ret'=>1,'msg'=>$l_err[2]);
            return null;
        }
        //$pid = $dbW->LastID();
        //if ($pid>0) {
        $data_arr['id'] = $pid;  // 该项目id, 创建记录成功才会有此项
        //}
        //}else {
        // 还需要补充几个可能没有提供的数据
        //$data_arr['db_host']   = array_key_exists('db_host',$request)?$request['db_host']:$l_srv_db_dsn['hostspec'];
        //$data_arr['db_port']   = array_key_exists('db_port',$request)?$request['db_port']:$l_srv_db_dsn['port'];
        //$data_arr['db_user']   = array_key_exists('db_user',$request)?$request['db_user']:$l_srv_db_dsn['username'];
        //}

        // 增加项目记录成功后，需要创建相应的数据库和建立相应的数据表以及填充必要的数据
        // 依据项目的类型，确定需要建立哪几张基本表，后续需要在这个成功的基础上进行????
        $rlt = DbHelper::createDBandBaseTBL($data_arr);

        $this->info(date('Y-m-d H:i:s') . ' Done!' . self::NEW_LINE_CHAR);
        return 1;
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

}
