<?php

namespace App\Http\Controllers\Template;


use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\ListController;
use DBR;
use DbHelper;

class TemplateController extends ListController
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
        $request['do'] = 'template_list';


        $dbR = DBR::getDBR();
        $table_name = TABLENAME_PREF . env('TABLE_DEF');//TABLENAME_PREF."table_def";

        // 获取发布主机列表 , 用于ui
        $a_p_self_ids = array(
            1=>array("ziduan"=>"p_id"),
        );
        // 获取到前两级的数据数组
        $p_self_info = DbHelper::getProTblFldArr($dbR, $request, $a_p_self_ids);
        //print_r($p_self_info);
        if (!isset($p_self_info["p_def"]) || !$p_self_info["p_def"]) {
            return  $response['html_content'] = '项目信息为空！';
        }

        // 应该自动获取表定义表和字段定义表,此处省略并人为指定????
        //$TBL_def = env('DB_PREFIX') . env('TABLE_DEF');// TABLENAME_PREF."table_def";
        //$FLD_def = env('DB_PREFIX') . env('FIELD_DEF'); // TABLENAME_PREF."field_def";

        $arr = array();
        $arr["table_name"] = $table_name;
        //$arr["real_p_id"] = $p_self_info['p_def']['table_field_belong_project_id']; // 字段定义表所在项目
        $arr["TBL_def"] = $p_self_info['p_def']['TBL_def'];
        $arr["FLD_def"] = $p_self_info['p_def']['FLD_def'];;
        $arr["html_title"] = $GLOBALS['language']['TPL_MOBAN_STR'].$GLOBALS['language']['TPL_LIEBIAO_STR'];
        $arr["html_name"] = $p_self_info["p_def"]["name_cn"].$arr["html_title"];
        $arr["default_sqlwhere"]  = "where `name_eng` NOT LIKE '%table_def' AND `name_eng` NOT LIKE '%field_def' AND p_id = " . $request['p_id']; // 表定义表和字段定义表不用显示
        $arr["sql_order"] = "order by id desc";
        $arr["parent_ids_arr"] = array(1=>"p_id");  // 父级元素列表,p2, p3...分别表示二、三级父级元素例如项目id，模板id，文档id等
        $arr["a_options"] = array(
            "nav"=>array(
                "p_id"=>array(
                    "script_name"=>"main.php", // 可有可无
                    "do"   =>"project_list",
                    "value"=>$request["p_id"],
                    "name_cn"=>$GLOBALS['language']['TPL_XIANGMU_STR'].$GLOBALS['language']['TPL_LIEBIAO_STR'],
                )
            )
        );
        $arr["dbR"] = $dbR;

        $this->Init($request, $arr); // 初始化一下, 需要用到的数据的初始化动作,在parent::之前调用

        parent::getFieldsInfo($arr);
        if(!array_key_exists("f_info",$arr)) {
            $response['ret'] = array('ret'=>1,'msg'=>"the f_info not exist!");
            return $response['ret']['msg'];
        }

        $dbR->table_name = $table_name;
        $resp = parent::execute($arr,$actionMap,$actionError,$request,$response,$form,$get,$cookie);

        $ziduan_arr = getZiduan("id:模板ID;name_cn:表中文名称;name_eng:表名;p_id:所属项目id");// 需要的字段
        $document_show_arr = buildH($arr["_arr"],$ziduan_arr);
        $show = $document_show_arr[0];
        $show_title = $document_show_arr[1];

        $data_arr = array(
            "show"=>$show,
            "show_title"=>$show_title,
            "get_csrf_token"=>csrf_token(),
            "INPUT_other"=>'',//表的字段管理在各个表里面进行，'<input type=button onClick="action_onclick(\'main.php?do=tempdef_list&p_id='.$request["p_id"].'\',self.document.myform,\'id\',\'list\',\'t_id\');return false" value="'.$GLOBALS['language']['TPL_MOBAN_STR'].$GLOBALS['language']['TPL_YU_STR'].$GLOBALS['language']['TPL_GUANLI_STR'].'" />',
        );

        $response['html_content'] = replace_template_para($data_arr,$resp);
        $response['ret'] = array('ret'=>0);
        return $response['html_content'];  // 总是返回此结果
    }
}
