<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\ListController;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use DBR;

class ProjectController extends ListController
{
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function list(Request $a_request)
    {
        // 检查是否登录
        $l_auth = $this->userService->ValidatePerm($a_request);
        $_SESSION = session()->all();
        if (!$l_auth || !isset($_SESSION['user']) || !$_SESSION['user']) {
            return redirect('/admin/login');
        }

        $actionMap = [];
        $actionError = [];
        $response = [];
        $form = [];
        $get = [];
        $cookie = [];
        $files = [];

        $request = $a_request->all();
        $request['do'] = 'project_list';


        $dbR = new DBR();
        $dbR->table_name = $table_name = "project";


        // 应该自动获取表定义表和字段定义表,此处省略并人为指定????
        $TBL_def = env('DB_PREFIX') . env('TABLE_DEF');
        $FLD_def = env('DB_PREFIX') . env('FIELD_DEF');

        $arr = array();
        $arr['table_name'] = $table_name;
        $arr['TBL_def'] = $TBL_def;
        $arr['FLD_def'] = $FLD_def;
        $arr['html_title'] = $GLOBALS['language']['TPL_XIANGMU_STR'].$GLOBALS['language']['TPL_LIEBIAO_STR'];
        $arr['html_name']  = $GLOBALS['language']['TPL_XIANGMU_STR'].$GLOBALS['language']['TPL_LIEBIAO_STR'];
        $arr['sql_order'] = 'order by id desc';
        $arr['dbR'] = $dbR;


        // 需要加入权限限制所能查看的数据表
        if (1!=$_SESSION['user']['if_super']){
            $l_ps = UserPrivilege::getSqlInProjectByPriv();
            if (''!=$l_ps) $arr['default_sqlwhere'] = "where id in ($l_ps)";
            else $arr['default_sqlwhere'] = 'where id<0 ';  // 将获取不到任何数据, 如果么有权限的话
        }
        $this->Init($request, $arr); // 初始化一下, 需要用到的数据的初始化动作,在parent::之前调用

        parent::getFieldsInfo($arr);
        if(!array_key_exists("f_info",$arr)) {
            $response['ret'] = array('ret'=>1,'msg'=>"the f_info not exist!");
            return 'the f_info not exist!';
        }

        $dbR->table_name = $table_name;
        $resp = parent::execute($arr,$actionMap,$actionError,$request,$response,$form,$get,$cookie);

        $ziduan_arr = getZiduan("id:ID;name_cn:项目名称;db_host:数据库主机;db_name:数据库名称;db_user:数据库用户名;status_:状态");// 需要的字段
        $show_arr = buildH($arr["_arr"], $ziduan_arr);
        $show = $show_arr[0];
        $show_title = $show_arr[1];

        $data_arr = array(
            "show"=>$show,
            "show_title"=>$show_title,
        );

        $content = replace_template_para($data_arr,$resp);
        return $content;

        // 先获取模板
        //$l_file = __FUNCTION__ . env('BLADE_SUFFIX');
        //$l_path = resource_path() . '/views/admin/';
        //$content = file_get_contents($l_path . $l_file);
        //unlink($l_path . $l_file);usleep(2000);
        //if (false !== strpos($content, '<!--{')) file_put_contents($l_path . $l_file, str_replace(['<!--{', '}-->'], ['{{', '}}'], $content));
        //return view('admin/list', $data_arr);
    }
}
