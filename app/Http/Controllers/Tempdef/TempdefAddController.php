<?php

namespace App\Http\Controllers\Tempdef;

use App\Http\Controllers\AddController;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use DBR;
use DBW;
use Parse_Arithmetic;
use DbHelper;
use cArray;

class TempdefAddController extends AddController
{
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function execute(Request $a_request)
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
        $form = $a_request->post();
        $get = $a_request->query();
        $cookie = $a_request->cookie();
        $files = $a_request->file();

        $request = $a_request->all();
        $request['do'] = 'tempdef_add';



        // 对什么表进行操作，需要依据参数而定
        // 模板定义表增加的时候其实需要修改表结构，同时在字段定义表中需要增加记录
        // 找到父级元素, 有两级父级
        $p_id = $request["p_id"];  // 第一个父级id, 也是project id。
        $dbR = DBR::getDBR();      // 系统默认数据库连接信息，开始都从这个入口

        $a_p_self_ids = array(
            1=>array("ziduan"=>"p_id"),
            2=>array("ziduan"=>"t_id"),
        );
        // 获取到前两级的数据数组, 此处的dbr会修改数据库连接信息。
        $p_self_info = DbHelper::getProTblFldArr($dbR, $request, $a_p_self_ids);

        $TBL_def = TABLENAME_PREF."table_def";
        $FLD_def = TABLENAME_PREF."field_def";

        // 依据t_id获取到数据表名称等信息, 需要从表定义表中获取其信息
        $dbR->table_name = $TBL_def;
        $t_arr = $dbR->getOne(" where id = ".$request["t_id"]);


        $arr = array();
        $arr["dbR"] = null;//$dbR;
        $arr["table_name"] = $FLD_def;  // 执行插入操作的数据表
        $arr["parent_ids_arr"] = array(1=>"p_id", 2=>"t_id");//,2=>"id"可有可无，编辑的时候一定要有
        $arr["TBL_def"] = $TBL_def;
        $arr["FLD_def"] = $FLD_def;
        $arr["html_title"] = $GLOBALS['language']['TPL_ZENGJIA_STR'].$GLOBALS['language']['TPL_WENDANG_STR'];
        $arr["html_name"] = $p_self_info["t_def"]["name_cn"].$arr["html_title"];
        $arr["a_options"] = array(
            "nav"=>array(
                "p_id"=>array(
                    "script_name"=>"main.php", // 可有可无
                    "do"   =>"project_list",
                    "value"=>$request["p_id"],
                    "name_cn"=>$GLOBALS['language']['TPL_XIANGMU_STR'].$GLOBALS['language']['TPL_LIEBIAO_STR'],
                ),
                "t_id"=>array(
                    "do"   =>"template_list",
                    "value"=>$request["t_id"],
                    "name_cn"=>$GLOBALS['language']['TPL_MOBAN_STR'].$GLOBALS['language']['TPL_LIEBIAO_STR'],
                )
            )
        );

        $table_name = $arr["table_name"];

        $this->Init($request, $arr);  // 需要初始化一下

        $arr = array_merge($arr, $p_self_info);
        parent::getFieldsInfo($arr);  // 实际上是用表定义表、字段定义表中的数据填充$arr的f_info
        if(!array_key_exists("f_info",$arr)) {
            $response['ret'] = array('ret'=>1,'msg'=>"the f_info not exist!");
            return "the f_info not exist!";
        }

        //$l_bixuziduanform = DbHelper::getBiXuFields($arr["dbR"], array("table_name"=>$table_name, "f_info"=>$arr["f_info"]));
        if ($a_request->isMethod('get')) {
            // 因为还没有数据表，因此可以执行外部sql，也可以选择某些类型还可以使用默认表
            // 需要根据项目类型(是否CMS)来决定显示相应的静态模板
            $arr["default_over"] = array(
                "p_id"=>array("value"=>$p_id,"hidden"=>1),
                "t_id"=>array("value"=>$request["t_id"],"hidden"=>1)
            );  // 作为参数传递过来的, 最好是隐藏的，并且列出中文名
            $arr["no_need_field"] = array("creator","createdate","createtime","mender","menddate","mendtime");
            // 在列出表单之前，先将字段定义的算法进行必要的解析以后再列出表单。
            // 列出表单只解析真实表结构本身的字段
            Parse_Arithmetic::parse_for_list_form($arr,$actionMap,$actionError,$request,$response,$form,$get,$cookie);
            $l_resp = parent::executeListForm($arr,$actionMap,$actionError,$request,$response,$form,$get,$cookie);

            $response['html_content'] = $l_resp;
            $response['ret'] = array('ret'=>0);
            return $response['html_content'];  // 总是返回此结果
        }else {

            //有两项任务: 1)需要修改表结构， 2)并且在字段定义表中增加一条记录。

            // 如果提交的数据的唯一项是数据库中的默认值，则需要进行替换成程序安排的默认递增值
            // 默认的字段名称 aups_f 加数字 ，需要执行查询统计
            $n_finfo = cArray::Index2KeyArr($arr["f_info"],array("key"=>"name_eng","value"=>array()));// 默认表名不是想要的
            if ( !isset($form["name_eng"]) || ""==$form["name_eng"] || trim($n_finfo["name_eng"]["default"]) == trim($form["name_eng"]) ) {
                //$dbR->table_name = $t_arr["name_eng"];  // 需要修改表结构的表依据t_id获取
                //$a_tmpl = $dbR->getTblFields();
                // 还得从字段定义表中获取，因为有一些不存在的字段（万能的Application::SQLResult）只有数据但并不入库的动态内容。
                $dbR->table_name = $table_name;    // 获取到字段定义表的所有字段
                $a_tmpl = $dbR->getAlls("","name_eng");
                $request["name_eng"] = $form["name_eng"] = DbHelper::getAutocreamentFieldname($a_tmpl,"name_eng");
            }

            $dbW = DBW::getDBW($arr["p_def"]);

            // 如果字段算法类型为Application::SQLResult等以Application::开头的则不能修改表结构，只在字段定义表中添加一条记录即可
            $l_temptype = cArray::getTempTypeCNnameArr();
            if (1==$l_temptype[$request["f_type"]]["if_into_db"]) {
                // 1) 修改表结构
                // 由于提交的数据中有数据类型、长度等信息，因此可以创建更加准确的字段，稍后完善????
                // 于表开头添加字段,   语法: ALTER TABLE `dpps_tmpl_design` ADD `default_field` VARCHAR( 10 ) NOT NULL DEFAULT 'url_1' COMMENT 'ffff' FIRST ;
                // 于某字段之后添加字段,语法: ALTER TABLE `dpps_tmpl_design` ADD `default_field` VARCHAR( 60 ) NOT NULL DEFAULT 'url_1' COMMENT '发布地址存放字段, 发布成功以后得到的地址存放到哪个字段中' AFTER `content_type` ;
                $dbW->table_name = $t_arr["name_eng"];  // 需要修改表结构的表依据t_id获取
                $duoziduan = array($request["name_eng"]);  // 每次只增加一个字段
                try {
                    $dbW->alter_table($duoziduan, array($request["name_eng"]=>$form));  // 借助phpmyadmin并放到dbhelper进行封装
                } catch (\Exception $l_err) {
                    // sql有错误，后面的就不用执行了。
                    \Log::Debug(" FILE: ".__FILE__." ". " FUNCTION: ".__FUNCTION__." Line: ". __LINE__ . " ". $dbW->getSQL() ." ". var_export($l_err->getMessage(),true));
                    $response['html_content'] = date("Y-m-d H:i:s") . $dbW->getSQL() . " alter table err!!!!";
                    $response['ret'] = array('ret'=>1,'msg'=>$l_err->getMessage());
                    return $response['html_content'];
                }
            }

            // 在列出表单之前，先将字段定义的算法进行必要的解析以后再列出表单。
            // 列出表单只解析真实表结构本身的字段
            Parse_Arithmetic::parse_for_list_form($arr,$actionMap,$actionError,$request,$response,$form,$get,$cookie);

            // 2)在字段定义表中增加一条记录
            $data_arr = DbHelper::getInsertArrByFormFieldInfo($form, $arr["f_info"], false);
            if (array_key_exists("___ERR___", $data_arr)) {
                $response['html_content'] = date("Y-m-d H:i:s") . "field empty: ". var_export($data_arr["___ERR___"], TRUE);
                $response['ret'] = array('ret'=>1);
                return $response['html_content'];
            }
            // 自动填充几个数据，关于创建者、时间的字段 if (!array_key_exists("creator", $data_arr))
            if (array_key_exists("creator",    $arr["f_info"])) $data_arr["creator"] = $_SESSION["user"]["username"];
            if (array_key_exists("createdate", $arr["f_info"])) $data_arr["createdate"] = ("0000-00-00"==$data_arr["createdate"] || empty($data_arr["createdate"])) ? date("Y-m-d") : $data_arr["createdate"];
            if (array_key_exists("createtime", $arr["f_info"])) $data_arr["createtime"] = ("00:00:00"==$data_arr["createtime"] || empty($data_arr["createtime"]))   ? date("H:i:s") : $data_arr["createtime"];

            // 表定义表和字段定义表有可能是挂靠在其他项目上的
            if ($arr['p_def']['table_field_belong_project_id'] > 0 && ($arr['p_def']['id'] != $arr['p_def']['table_field_belong_project_id'])) {
                // 需要获取对应的项目信息，并且检查该项目中的是否存在表定义表和字段定义表，如果该项目也挂靠在其他项目则报错；暂不支持多级挂靠，避免出现互相挂靠而死循环
                // $p_info_t_def = \App\Models\Admin\Project::find(1); 也可，不过不好加缓存
                $p_obj = new \App\Repositories\Admin\ProjectRepository();
                $p_info_t_def = $p_obj->getProjectById($arr['p_def']['table_field_belong_project_id']);
                // 切换到指定的数据库，需要携带数据库名称信息，重新连一下数据库。
            } else {
                // 沿用$arr["p_def"]的DBW
                $p_info_t_def = $arr['p_def'];
            }
            $dbW = DBW::getDBW($p_info_t_def); // 后面插入表数据需要在这个连接上操作

            $dbW->table_name = $table_name;  // 字段定义表
            try {
                $fid = $dbW->insertOne($data_arr);
            } catch (\Exception $l_err) {
                //$l_err = $dbW->errorInfo();
                //$fid = $dbW->LastID();  // 获取字段id

                // 创建失败后, 本需要将修改的表结构修改回来，保证事务性，时间关系，以后完善????
                $response['html_content'] = date("Y-m-d H:i:s") . var_export($l_err->getMessage(),true) . $dbW->getSQL() . " insert err!!!!";
                $response['ret'] = array('ret'=>1,'msg'=>$l_err->getMessage());
                return $response['html_content'];
            }

            // 添加成功以后，需要对定义的各种任务需要一一完成(即执行相应的成功后算法)
            Parse_Arithmetic::do_arithmetic_by_add_action($arr,$actionMap,$actionError,$request,$response,$form,$get,$cookie);

            if ($fid>0) {
                $response['ret'] = array('ret'=>0);
                $response['html_content'] = date("Y-m-d H:i:s") . " 成功添加了信息, <a href='/tempdef/list?do=tempdef_list".$arr["parent_rela"]["parent_ids_url_build_query"]."'>返回列表页面</a> ".NEW_LINE_CHAR;  // 总是返回此结果
                return $response['html_content'];
            } else {
                $response['html_content'] = date("Y-m-d H:i:s") . var_export($data_arr,true) . $dbW->getSQL() . " insert err!!!!";
                $response['ret'] = array('ret'=>1);
                return $response['html_content'];
            }
        }
    }
}
