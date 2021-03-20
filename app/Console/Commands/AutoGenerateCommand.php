<?php
/*
 调用示例：单个
php artisan autoGenerate:code  --r=Admin --B=Language --d=sys_language --o=1

-- 所有表
php artisan autoGenerate:code  --all_table=1 --o=1

-- ucwords, ucfirst 函数测试
php -r "$key_word='aaa_bbb_ccc';echo ucfirst($key_word) .  '  ';echo ucwords($key_word, ' _');"


 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

//define('NEW_LINE_CHAR', "\n");
define('NEW_LINE_HTML', '
');

class AutoGenerateCommand extends Command
{
    const NEW_LINE_CHAR = "\r\n";

    protected $name = 'autoGenerate';
    protected $description = '自动生成代码';

    protected $signature = 'autoGenerate:code {--r=} {--B=} {--d=} {--C=} {--o=} {--all_table=} {--except=}';

    public function handle() {
        $options = $this->option();

        $start_time = microtime(true);
        $start_mem = memory_get_usage();
        $this->info(date('Y-m-d H:i:s') . ' begin:');

        $GLOBALS['except'] = ['user', 'language', 'sys_language'];
        $GLOBALS['delete_status_tabs'] = ['user', 'role', 'sys_country', 'sys_language', 'sys_department'];

        $data = $this->main($options);
        $this->info('  result:' . var_export($data, true));

        $end_mem = memory_get_usage();
        $this->info(date('Y-m-d H:i:s') . ' end, cost:' . (microtime(true) - $start_time) . ' seconds! memory_use: ' . ($end_mem - $start_mem) . ' = '. $end_mem . ' - ' . $start_mem );
    }

    private function main($_o) {
        if (isset($_o['all_table']) && $_o['all_table']) {
            return $this->processMultiple($_o);
        }
        return $this->processOneTable($_o);
    }

    private function processMultiple($_o) {
        // user表手动处理，不能自动生成，稍微复杂
        $except = $GLOBALS['except'];
        if (isset($_o['except']) && $_o['except']) {
            $l_except_list = explode(',', $_o['except']);
            $except = array_merge($except, $l_except_list); // 合并数组，不能用+
            $except = array_unique($except); // 去重
        }

        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        if (!$tables) return 0;
        foreach ($tables as $table_name) {
            if (in_array(strtolower($table_name), $except)) {
                $this->info( $table_name . ' is excepted, continue!' );
                continue;
            }

            // 所有表，每张表需要强制填充 r,B,d参数
            $_o['r'] = $this->getRouteByTable($table_name);
            $_o['B'] = $this->getFileModelByTable($table_name);
            $_o['d'] = $table_name; // 数据表名

            // 逐个处理
            $this->info( date('Y-m-d H:i:s') . ' begin to process table: ' . $table_name . ' !' );
            $this->processOneTable($_o);
            $this->info( date('Y-m-d H:i:s') . ' process complete , ' . $table_name . '  , sleep a moment!' );
            sleep(1);
            //break;
        }
        return 1;
    }

    public function getRouteByTable($table_name) {
        $str = 'Admin'; // 默认放到Admin目录下

        $table_name = strtolower(trim($table_name));

        $key_word = 'customer';
        if ($key_word == substr($table_name, 0, strlen($key_word))) {
            return ucfirst($key_word);
        }

        $key_word = 'order';
        if ('order' == substr($table_name, 0, 5)) {
            $str = ucfirst($key_word) . 'PreSale'; // 售前订单
            return $str;
        }

        $key_word = 'goods';
        if ($key_word == substr($table_name, 0, strlen($key_word))) {
            return ucfirst($key_word);
        }

        return $str;
    }

    public function getFileModelByTable($table_name) {
        $table_name = strtolower(trim($table_name));

        // 默认文件命名跟表名一样，但是有些sys_area这些去掉sys_, 但有几个例外：sys_routing，sys_privilege，sys_opt_record
        $orig = ['sys_routing', 'sys_privilege', 'sys_opt_record', 'sys_config'];
        $key_word = 'sys_'; // 删除sys_前缀
        if ($key_word == substr($table_name, 0, strlen($key_word)) && !in_array($table_name, $orig)) {
            $table_name = substr($table_name, strlen($key_word));
        }

        $str = str_replace('_', '', ucwords($table_name, ' _')); // 下划线_分隔的单词首字母大写，然后去掉下划线
        return $str;
    }

    private function processOneTable($_o) {

        if (!isset($_o['r']) || !isset($_o['B']) || !$_o['r'] || !$_o['B']) {
            // 输出提示
            $this->info( "please use like : php artisan autoGenerate:code  --r=Admin --B=Language --d=sys_language" . "\r\n");
            return ;
        }

        $database = isset($_o['d']) ? $_o['d'] : '';
        $route = $_o['r'];  // 路由,
        $name = $_o['B'];   // 大写的方法名等
        $source_path = app()->basePath();
        $source_path = isset($_o['C']) ? $_o['C'] : $source_path; // 项目路径
        $source_path = str_replace('\\', '/', $source_path); //
        $GLOBALS['overwrite'] = isset($_o['o']) ? $_o['o'] : 0;

        // user表手动处理，不能自动生成，稍微复杂
        $except = $GLOBALS['except'];
        if (isset($_o['except']) && $_o['except']) {
            $l_except_list = explode(',', $_o['except']);
            print_r($l_except_list);
            $except = array_merge($except, $l_except_list); // 合并数组，不能用+
            $except = array_unique($except); // 去重
        }
        if (in_array(strtolower($name), $except) || in_array(strtolower($database), $except)) {
            $this->info( " the $name / $database  Model can not auto generate!" . "\r\n");
            return ;
        }

        // 1. 修改路由 api.php，添加路由，TODO

        // 1. 新增control, 样例： app/Http/Controllers/Admin/DepartmentController.php
        BuildControl($source_path . "/app/Http/Controllers", $route, $name);

        // 2. 新增service, 样例： app/Services/Admin/DepartmentService.php
        BuildDto($source_path . "/app/Dto", $route, $name, $database);

        // 3. 新增service, 样例： app/Services/Admin/DepartmentService.php
        BuildService($source_path . "/app/Services", $route, $name);

        // 4-5. 新增repository, 样例： app/Repositories/Admin/DepartmentRepository.php
        BuildRepository($source_path . "/app/Repositories", $route, $name, $database);
        BuildRepositoryImpl($source_path . "/app/Repositories", $route, $name, $database);

        // 6. 新增model, 样例： app/Models/Admin/Department.php
        BuildModel($source_path . "/app/Models", $route, $name, $database);
    }

    // 获取数据库列表、表列表、字段列表
    private function db_table_fields($request) {
        $this->info('  ' . date('Y-m-d H:i:s') . ' start autoGenerateCode');

        $current_db_name = DB::connection()->getDoctrineSchemaManager()->getSchemaSearchPaths(); var_dump($current_db_name[0]); // 当前数据库名称
        //$db_list = DB::connection()->getDoctrineSchemaManager()->listDatabases();print_r($db_list);

        // 获取所有数据表：
        //$tables = DB::select('show tables');print_r($tables); // -- 也可，就是要自己替换一下
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();print_r($tables);

        // 字段详情
        //$field_list = DB::connection()->getDoctrineSchemaManager()->listTableColumns('user');print_r($field_list);
        $field_arr = Schema::getColumnListing('user');print_r($field_arr);

        //  $tables = DB::connection()->getDoctrineSchemaManager()->listSequences();print_r($tables); -- failed
        //
        // $table_detail = DB::connection()->getDoctrineSchemaManager()->listTableDetails('user');print_r($table_detail);

        $this->info(date('Y-m-d H:i:s') . ' Done!' . self::NEW_LINE_CHAR);
        return 1;
    }

}




// 增加一个control文件
function BuildControl($path, $route, $name){
    //$base_name = basename($name);
    //$name_lower = lcfirst($name);

    $route = ucfirst($route); // 首字母大写
    $name = ucfirst($name);

    $l_tmpl = "<?php
" . GetComment($route, $name, 'Controller') . "
namespace App\Http\Controllers\\".$route.";

use App\Http\Controllers\CommonController;
use App\Services\\".$route."\\".$name."Service;


class ".$name."Controller extends CommonController
{
    protected \$theService;

    public function __construct() {
        \$this->theService = new ".$name."Service();
        parent::__construct();
    }

    public function getList() {
        return \$this->response_json(\$this->theService->getList());
    }

    public function addOrUpdate() {
        return \$this->response_json(\$this->theService->addOrUpdate());
    }

    public function detail() {
        return \$this->response_json(\$this->theService->detail());
    }
}
";

    $filename = $name . "Controller.php";
    writefile($l_tmpl, $path . '/'. $route, $filename);
}

function BuildDto($path, $route, $name, $database=''){
    $table_name = $database ? $database : strtolower($name);

    //$route = ucfirst($route); // 首字母大写
    $name = ucfirst($name);

    // 获取所有的表
    $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
    if (!in_array($table_name, $tables)) {
        echo ' table-name: ' . $table_name . ' not exists!';
        exit;
    }
    // 获取表结构 $field_arr = Schema::getColumnListing($table_name);
    $sql = 'SHOW FULL FIELDS FROM `' . $table_name . '`';
    $rlt = DB::select($sql);  // 主键、字段类型、长度等详情，暂时不用 int(


    $l_tmpl = "<?php

namespace App\Dto;

class ".$name."Dto extends BaseDto
{";

//    public $id = 0;
//    public $email = '';
//    public $picsrc = '';
//    public $department_id = 0;
//    public $role_id = 0;
//    public $real_name = '';
//    public $phone = '';
//    public $authority_list = '';

    $str_pad_num = 48;
    // 字段自动获取，并依据类型默认成0或空字符串
    foreach ($rlt as $row) {
        $zero_string = "''";
        if ((false !== strpos($row->Type, 'int(')) || (false !== strpos($row->Type, 'decimal(')))
            $zero_string = 0;

        $public_str = '    public $'. $row->Field .' = '. $zero_string .';';
        $public_str = str_pad($public_str, $str_pad_num) . '// ' . $row->Comment; // 加上字段的备注，并且对齐

        $l_tmpl .= NEW_LINE_HTML . $public_str;
    }

    $l_tmpl .= "
}
";

    $filename = $name . "Dto.php";
    writefile($l_tmpl, $path, $filename);
}

function BuildService($path, $route, $name) {
    $route = ucfirst($route); // 首字母大写
    $name = ucfirst($name);

    $l_tmpl = "<?php

namespace App\Services\\".$route.";

use App\Libs\Utils\ErrorMsg;
use App\Dto\DataListDto;
use App\Dto\ResponseDto;
use App\Dto\\".$name."Dto;
use App\Repositories\\".$route."\\".$name."Repository;
use App\Repositories\Admin\UserRepository;
use App\Services\BaseService;
use Illuminate\Support\Facades\Validator;


class ".$name."Service extends BaseService
{
    protected \$theRepository;

    public function __construct() {
        \$this->theRepository = new ".$name."Repository();
        \$this->userRepository = new UserRepository(); // 用于权限检查
    }

    public function getList() {
        \$request = request()->all(); // 参数接收
        \$responseDto = new ResponseDto();

        //\$login_user_info = self::getCurrentLoginUserInfo(); // TODO 当前登录用户是否有权限，统一一个方法放到BaseService中

        // 参数校验数组
        \$rules = [
            'page' => 'sometimes|integer',
            'limit' => 'sometimes|integer',
        ];
        \$validate = Validator::make(\$request, \$rules);
        if (\$validate->fails()) {
            \$error_list = \$validate->errors()->all();
            \$responseDto->status = ErrorMsg::PARAM_ERROR;
            \$responseDto->msg = implode(\"\\r\\n\", \$error_list);
            return \$responseDto;
        }

        // 获取数据，包含总数字段
        \$list = \$this->theRepository->getList(\$request);
        if (!\$list || !isset(\$list[\$responseDto::DTO_FIELD_TOTOAL]) || !isset(\$list[\$responseDto::DTO_FIELD_LIST])) {
            ErrorMsg::FillResponseAndLog(\$responseDto, ErrorMsg::DATA_EMPTY);
            return \$responseDto;
        }
        if (\$list[\$responseDto::DTO_FIELD_LIST]) {
            // 成功，返回列表信息
            foreach (\$list[\$responseDto::DTO_FIELD_LIST] as \$key => \$v_detail) {
                \$v_info = new ".$name."Dto();
                \$v_info->Assign(\$v_detail);
                \$list[\$responseDto::DTO_FIELD_LIST][\$key] = \$v_info;
            }
        }
        \$data_list = new DataListDto();
        \$data_list->Assign(\$list);
        \$responseDto->data = \$data_list;

        return \$responseDto;
    }

    public function addOrUpdate(\$request = null) {
        if (!\$request) \$request = request()->all();
        \$responseDto = new ResponseDto();

        if ('cli' != php_sapi_name()) \$current_uid = auth('api')->id();
        else \$current_uid = (\$request['creator_id'] ?? 0) + 0;
        // 参数校验数组, 当前登录用户是否有权限暂不验证，后面统一处理
        //\$field_id = 'id';
        \$rules = [
            'id' => 'sometimes|integer',
        ];
        \$validate = Validator::make(\$request, \$rules);
        if (\$validate->fails()) {
            \$error_list = \$validate->errors()->all();
            \$responseDto->status = ErrorMsg::PARAM_ERROR;
            \$responseDto->msg = implode(\"\\r\\n\", \$error_list);
            return \$responseDto;
        }

        \$curr_datetime = date('Y-m-d H:i:s');
        \$data_arr = \$request; // 全部作为
        if (isset(\$request['id']) && \$request['id']) {
            // 修改的情况
            \$data_arr['id'] = \$request['id'];
            // 检查该记录是否存在
            \$v_detail = \$this->theRepository->getInfoById(\$request['id']);
            if (!\$v_detail) {
                ErrorMsg::FillResponseAndLog(\$responseDto, ErrorMsg::DATA_NOT_EXISTS);
                return \$responseDto;
            }
            \$data_arr['updator_id'] = \$current_uid;
            //\$data_arr['deleted_time'] = \$data_arr['deleted_time'] ?? \$this->theRepository::DATETIME_NOT_NULL_DEFAULT;
        } else {
            // 新增，注：有些需要检查对应的唯一key是否存在
            //\$v_detail = \$this->theRepository->getByUniqueKey(\$request);
            //if (\$v_detail) {
            //    ErrorMsg::FillResponseAndLog(\$responseDto, ErrorMsg::DATA_EXISTS);
            //    return \$responseDto;
            //}
            \$data_arr['creator_id'] = \$current_uid;
            \$data_arr['updator_id'] = \$data_arr['creator_id'];
            \$data_arr['created_time'] = \$curr_datetime;
            //\$data_arr['deleted_time'] = \$this->theRepository::DATETIME_NOT_NULL_DEFAULT;
        }
        // 数据增加几个默认值
        \$data_arr['updated_time'] = \$curr_datetime;

        if (isset(\$request['id']) && \$request['id']) {
            // 更新
            \$rlt = \$this->theRepository->updateData(\$request['id'], \$data_arr);
            if (!\$rlt) {
                ErrorMsg::FillResponseAndLog(\$responseDto, ErrorMsg::UPDATE_DB_FAILED);
                return \$responseDto;
            }
        } else {
            \$v_id = \$this->theRepository->insertGetId(\$data_arr);
            if (!\$v_id) {
                ErrorMsg::FillResponseAndLog(\$responseDto, ErrorMsg::INSERT_DB_FAILED);
                return \$responseDto;
            }
            // 暂不返回详情，前端跳列表页
            \$responseDto->data = ['id'=>\$v_id];
        }
        return \$responseDto;
    }

    public function detail(\$id) {
        \$request['id'] = \$id;
        \$responseDto = new ResponseDto();

        // uid参数校验; 当前登录用户是否有权限暂不验证，后面统一处理
        \$field_id = 'id';
        \$rules = [
            \$field_id => 'required|integer|min:1'
        ];
        \$validate = Validator::make(\$request, \$rules);
        if (\$validate->fails()) {
            \$error_list = \$validate->errors()->all();
            \$responseDto->status = ErrorMsg::PARAM_ERROR;
            \$responseDto->msg = implode(\"\\r\\n\", \$error_list);
            return \$responseDto;
        }

        \$v_detail = \$this->theRepository->getInfoById(\$request[\$field_id]);
        if (!\$v_detail) {
            ErrorMsg::FillResponseAndLog(\$responseDto, ErrorMsg::DATA_EMPTY);
            return \$responseDto;
        }
        // 成功，返回信息
        \$v_info = new ".$name."Dto();
        \$v_info->Assign(\$v_detail);
        \$responseDto->data = \$v_info;

        return \$responseDto;
    }

    public function delete(\$id) {
        // 进行软删除，更新状态即可
        return true;
    }
    
    // 更新单条
    public function updateOne(\$id) {
        \$request = request()->all();
        \$request['id'] = \$id;
        \$responseDto = new ResponseDto();

        // 参数校验数组, 当前登录用户是否有权限暂不验证，后面统一处理
        \$rules = [
            'id' => 'required|integer|min:1'
        ];
        \$validate = Validator::make(\$request, \$rules);
        if (\$validate->fails()) {
            \$error_list = \$validate->errors()->all();
            \$responseDto->status = ErrorMsg::PARAM_ERROR;
            \$responseDto->msg = implode(\"\\r\\n\", \$error_list);
            return \$responseDto;
        }
        return self::addOrUpdate(\$request);
    }
}
";

    $filename = $name . "Service.php";
    writefile($l_tmpl, $path . '/'. $route, $filename);
}


function BuildRepository($path, $route, $name, $database='') {
    $table_name = $database ? $database : strtolower($name);
    $route = ucfirst($route); // 首字母大写
    $name = ucfirst($name);

    $l_tmpl = "<?php

namespace App\Repositories\\".$route.";

class ".$name."Repository extends ".$name."RepositoryImpl
{
    const CACHE_EXPIRE = 1;  // 单位秒，缓存时间

    private static function GetCacheKey(\$id) {
        return 'db:".$table_name.":detail-id-' . \$id;
    }

    // 通过id获取信息
    public function getInfoById(\$id) {
        // 先从cache获取数据
        \$cache_key = self::GetCacheKey(\$id);
        \$cached_result = \Cache::get(\$cache_key);
        if (\$cached_result)
            return \$cached_result;

        // 再从数据库获取，获取到了则种cache
        \$db_result = parent::getInfoById(\$id);
        if (!\$db_result)
            return \$db_result;
        \Cache::put(\$cache_key, \$db_result, self::CACHE_EXPIRE);

        return \$db_result;
    }

}
";

    $filename = $name . "Repository.php";
    writefile($l_tmpl, $path . '/'. $route, $filename);
}


function BuildRepositoryImpl($path, $route, $name, $database=''){
    $route = ucfirst($route); // 首字母大写
    $name = ucfirst($name);

    $delete_status_str = " else {
            \$builder = \$builder->where('status', '!=', -1);
        }";
    if (!in_array($database, $GLOBALS['delete_status_tabs'])) {
        $delete_status_str = '';
    }

    $l_tmpl = "<?php

namespace App\Repositories\\".$route.";

use App\Models\\".$route."\\".$name.";
use App\Repositories\BaseRepository;

class ".$name."RepositoryImpl extends BaseRepository
{
    protected \$model ;

    public function __construct() {
        \$this->model = new ".$name."();
    }

    public function getList(\$params, \$field = ['*']) {
        \$page = isset(\$params['page']) ? \$params['page'] : 1;
        \$limit = (isset(\$params['limit']) && \$params['limit'] > 0) ? \$params['limit'] : parent::PAGE_SIZE;
        //if (\$limit > parent::PAGE_SIZE_MAX) \$limit = parent::PAGE_SIZE; // 是否限制最大数量

        \$builder = \$this->model;
        if (isset(\$params['status'])) {
            \$builder = \$builder->where('status', \$params['status']);
        }
        \$builder = \$builder->orderBy('id', 'asc');
        return \$this->pager(\$builder, \$page, \$limit, \$field);
    }

    // 通过id获取信息
    public function getInfoById(\$id) {
        \$db_result = \$this->model->find(\$id);
        if (!\$db_result)
            return \$db_result;
        return \$db_result->toArray();
    }

    // 新增并返回主键ID
    public function insertGetId(\$data_arr) {
        \$data_arr = \$this->filterFields4InsertOrUpdate(\$data_arr);
        return \$this->model->insertGetId(\$data_arr);
    }

    public function updateData(\$id, \$data_arr) {
        \$sql_where = [
            'id' => \$id,
        ];
        \$data_arr = \$this->filterFields4InsertOrUpdate(\$data_arr);
        if (isset(\$data_arr['id'])) unset(\$data_arr['id']);

        return \$this->model->where(\$sql_where)->update(\$data_arr);
    }
}
";

    $filename = $name . "RepositoryImpl.php";
    writefile($l_tmpl, $path . '/'. $route, $filename);
}

function BuildModel($path, $route, $name, $database=''){
    $table_name = $database ? $database : strtolower($name);

    $route = ucfirst($route); // 首字母大写
    $name = ucfirst($name);


    // 获取表结构 $field_arr = Schema::getColumnListing($table_name);
    $sql = 'SHOW FULL FIELDS FROM `' . $table_name . '`';
    $rlt = DB::select($sql);  // 主键、字段类型、长度等详情，暂时不用 int(

    $l_tmpl = "<?php

namespace App\Models\\".$route.";

use Illuminate\Database\Eloquent\Model;

class ".$name." extends Model
{
    protected \$table = '".$table_name."';
    public \$timestamps = false;
    
    protected \$fillable = [";
//    'month',                                // 分区字段 订单时间取yyyyMM(UTC+8)
//    'order_source',                         // 订单来源,15网盟,16shopify,17分销,4crm

    $str_pad_num = 48;
    // 字段自动获取，并依据类型默认成0或空字符串
    foreach ($rlt as $row) {
        $public_str = '        \''. $row->Field .'\',';
        $public_str = str_pad($public_str, $str_pad_num) . '// ' . $row->Comment; // 加上字段的备注，并且对齐

        $l_tmpl .= NEW_LINE_HTML . $public_str;
    }

    $l_tmpl .= "
    ];
}
";

    $filename = $name . ".php";
    writefile($l_tmpl, $path . '/'. $route, $filename);
}


function git_add($path, $filename) {
    // 新创建的路径则不能添加，需手动； 或者逐级目录进行svn add操作，TODO ，有时间再完善
    //$cmd = '"C:/Git/cmd/git.exe" add ' . $path . "/" . $filename . ' 2>&1';
	$git_command = '"git"';
    if ('WIN' === strtoupper(substr(PHP_OS, 0, 3)))
        $git_command = '"C:/Git/cmd/git.exe"';
    $cmd = $git_command . ' -C "' . $path . '" add "' . $path . "/" . $filename . '" ' . ' 2>&1';
    //echo $cmd;
    exec($cmd, $out_put, $ret);
    echo date("Y-m-d H:i:s") . " " . $path . "/" . $filename . " git_add succ!" . "\r\n";
    return ;
}

function GetComment($route, $name, $a_t='Model') {
    $str = "/**
 * $name$a_t
 * @author dev@xhat.com
 * @since " . date("Y-m-d") . "
 */";

    return $str;
}

function writefile($l_tmpl, $path, $filename) {
    if (!file_exists($path . "/" . $filename) || $GLOBALS['overwrite']) {
        writeContent($l_tmpl, $path . "/" . $filename);
        echo date("Y-m-d H:i:s") . " " . $path . "/" . $filename . " succ!" . "\r\n";
    } else {
        // 可以不用输出调试信息
        echo date("Y-m-d H:i:s") . " " . $path . "/" . $filename . " file exist!" . "\r\n";
    }
    //git_add($path, $filename);
}

//建立目地文件夹
function createdir($dir='')
{
    if (!is_dir($dir)){
        // 该参数本身不是目录 或者目录不存在的时候
        $temp = explode('/',$dir);
        $cur_dir = '';
        for($i=0;$i<count($temp);$i++)
        {
            $cur_dir .= $temp[$i].'/';
            if (!is_dir($cur_dir))
            {
                @mkdir($cur_dir,0775);
            }
        }
    }
}

function writeContent( $content, $filePath, $mode='w' ){
    createdir(dirname($filePath));
    if ( !file_exists($filePath) || is_writable($filePath) ) {

        if (!$handle = @fopen($filePath, $mode)) {
            return "can't open file $filePath";
        }

        if (!fwrite($handle, $content)) {
            return "cann't write into file $filePath";
        }

        fclose($handle);

        return '';

    } else {
        return "file $filePath isn't writable";
    }
}
