<?php

namespace App\Http\Controllers\Project;

use App\Http\Controllers\AddController;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use DBR;
use DBW;
use Parse_Arithmetic;
use DbHelper;

class ProjectAddController extends AddController
{
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function execute(Request $a_request)
    {
        $l_prefix = TABLENAME_PREF;
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
        $request['do'] = 'project_add';
        $_SESSION = session()->all();

        if (1!=$_SESSION["user"]["if_super"]) {
            $response['html_content'] = "权限不够!";
            //$response['ret'] = array('ret'=>1);
            return $response['html_content'];  // 总是返回此结果
        }
        $table_name = $l_prefix ."project";
        $arr = array();
        $arr["html_title"] = $GLOBALS['language']['TPL_ZENGJIA_STR'].$GLOBALS['language']['TPL_XIANGMU_STR'];
        $arr["html_name"]  = $arr["html_title"];
        $arr["table_name"] = $table_name;
        $arr['sql_order'] = 'order by list_order, id'; // 排序

        // 没有提交数据的时候，需要依据被增加数据数据表的字段进行显示表单;
        // 当有数据提交的时候，需要依据字段属性自动筛选和默认赋值等
        $TBL_def = $l_prefix ."table_def";
        $FLD_def = $l_prefix ."field_def";

        $arr["dbR"] = new DBR();

        $arr["TBL_def"] = $TBL_def;
        $arr["FLD_def"] = $FLD_def;

        $this->Init($request, $arr);  // 需要初始化一下，并且放在调用的最前面

        parent::getFieldsInfo($arr);  // 获取表和字段定义信息
        if(!array_key_exists("f_info",$arr)) {
            //$response['ret'] = array('ret'=>1,'msg'=>"the f_info not exist!");
            return 'the f_info not exist!';
        }

        // 需要获取到必须填写的一个字段, 依据数据库结构进行判断
        //$l_bixuziduanform = DbHelper::getBiXuFields($arr["dbR"], array("table_name"=>$table_name, "f_info"=>$arr["f_info"]));

        if ($a_request->isMethod('get')) {
            // 在列出表单之前，先将字段定义的算法进行必要的解析以后再列出表单。
            // 列出表单只解析真实表结构本身的字段
            Parse_Arithmetic::parse_for_list_form($arr,$actionMap,$actionError,$request,$response,$form,$get,$cookie);

            $l_resp = parent::executeListForm($arr,$actionMap,$actionError,$request,$response,$form,$get,$cookie);
            $data_arr = array(
                //"p_id"=>isset($request["p_id"])?$request["p_id"]:1,  // 默认就是系统本身,
                // 可以不显示
                "get_csrf_token"=>csrf_token(),
                "l_other" => "<tr style='display:none' id='id_project_add_3'>
    <td><a href='/project/import'>复制发布项目</a></td>
  </tr>
  <tr style='display:none' id='id_project_add_4'>
    <td><a href='/project/import_from_file'>从文件导入</a></td>
  </tr>
  <tr style='display:none' id='id_project_add_2'>
    <td><a href='#' onclick=' id_project_add_3.style.display = \"none\";  id_project_add_4.style.display = \"none\";  id_project_add_2.style.display = \"none\";  id_project_add_1.style.display = \"inline\"; '>新建发布项目</a></td>
  </tr>"
            );

            $content = replace_template_para($data_arr,$l_resp);
            //$response['ret'] = array('ret'=>0);
            return $content;  // 总是返回此结果
        } else {

            if (!isset($form['db_name']) || ''==$form['db_name']) {
                $dbR = new DBR();
                $dbR->table_name = $table_name;

                // 从数据表中获取
                $a_proj = $dbR->getAlls('','db_name');

                // 同时还要从当前的数据库获取，以防止有其他未注册项目的存在而产生冲突????
                // 默认的数据库名称 aaaa 加数字 ，需要执行查询统计
                $request['db_name'] = $form['db_name'] = DbHelper::getAutocreamentDbname($a_proj, 'db_name', $form);
            }

            // 检查数据库是否能连上，再判断该创建的数据库是否不存在，不存在则创建数据库，并进行use;
            $tmp_info = $form;
            if (isset($tmp_info['db_name'])) unset($tmp_info['db_name']);
            $dbr2 = new DBR($tmp_info);
            $all_database_list = DbHelper::getAllDB($dbr2, []); // 获取全部数据库
            //$b = $dbr2->GetCurrentSchema(); echo $b . "\r\n"; // 空串
            if (!in_array($form['db_name'], $all_database_list)) {
                $dbW = new DBW($tmp_info);
                try {
                    // 如果没有建库权限，可能会报错
                    $dbW->create_db($form['db_name']); // 返回true；数据库已经存在也返回true；
                } catch (\Exception $l_err) {
                    echo 'create database ' . $form['db_name'] . ' error!: ' . $l_err->getMessage();
                    exit;
                }
            }

            // 切换到新创建的数据库，需要携带数据库名称信息，重新连一下数据库。
            /* TODO 临时测试2019.08.30，之后请删除
            $dbR = new DBR($form);
            $dbW = new DBW($form);
            $l_real_tbls = $dbR->getDBTbls($form['db_name']); // 获取所有数据表
            if ($l_real_tbls) {
                //$l_real_tbls = \cArray::Index2KeyArr($l_real_tbls, array("key"=>"Name", "value"=>"Name"));
                $l_real_tbls = array_column($l_real_tbls, 'Name', 'Name');
            }*/

            // 同表单呈现一样，填充之前需要将字段的各个算法执行一下，便于修正字段的相关限制和取值范围
            Parse_Arithmetic::parse_for_list_form($arr,$actionMap,$actionError,$request,$response,$form,$get,$cookie);
            // 各个项目自动检测，对于没有填写的采用默认值，默认为null的则剔除该项目
            $data_arr = DbHelper::getInsertArrByFormFieldInfo($form, $arr["f_info"], false);

            // 如果返回有错误，则退出
            if (array_key_exists("___ERR___", $data_arr)) {
                $response['html_content'] = date("Y-m-d H:i:s") . "field empty: ". var_export($data_arr["___ERR___"], TRUE);
                //$response['ret'] = array('ret'=>1);
                \Log::info($response['html_content']);
                return $response['html_content'];
            }
            // 自动填充几个数据，关于创建者、时间的字段. 首先确保数据表有这些字段
            if (array_key_exists("creator",    $arr["f_info"])) $data_arr["creator"] = $_SESSION["user"]["username"];
            if (array_key_exists("createdate", $arr["f_info"])) $data_arr["createdate"] = ("0000-00-00"==$data_arr["createdate"] || empty($data_arr["createdate"])) ? date("Y-m-d") : $data_arr["createdate"];
            if (array_key_exists("createtime", $arr["f_info"])) $data_arr["createtime"] = ("00:00:00"==$data_arr["createtime"] || empty($data_arr["createtime"]))   ? date("H:i:s") : $data_arr["createtime"];


            // 项目是否已经存在于项目表中（修改项目的时候）TODO
            $p_obj = new \App\Repositories\Admin\ProjectRepository();
            $project_exists_list = $p_obj->getProjectDsnList();
            $l_dsn = DbHelper::getConnectName($form, true, false);
            if ($project_exists_list && isset($project_exists_list[$l_dsn])) {
                // 如果已经存在，获取id
                $pid = $project_exists_list[$l_dsn]['id'];
            } else {
                // 不存在
                $dbW = new DBW();
                $dbW->table_name = $table_name;
                $pid = $dbW->insertOne($data_arr);
            }
            if (!is_numeric($pid) || $pid <= 0) {
                // 增加失败后
                $response['html_content'] = date("Y-m-d H:i:s") . var_export($data_arr, true). " 发生错误,sql: ". $dbW->getSQL();
                return $response['html_content'];
            }

            $form["id"] = $pid;  // 该项目id, 创建记录成功才会有此项

            // 增加项目记录成功后，需要创建相应的数据库和建立相应的数据表以及填充必要的数据
            // 依据项目的类型，确定需要建立哪几张基本表，后续需要在这个成功的基础上进行????
            $rlt = DbHelper::createDBandBaseTBL($form);

            // 添加成功以后，需要对定义的各种任务需要一一完成(即执行相应的成功后算法). 创建项目的时候基本不需要此步骤
            // Parse_Arithmetic::do_arithmetic_by_add_action($arr,$actionMap,$actionError,$request,$response,$form,$get,$cookie);


            // $response['html_content'] = "";
            //return "main.php?do=project_list";  // 总是返回此结果
            $response['html_content'] = "<script type='text/javascript'>window.parent.frames['frmMainMenu'].location.reload();window.parent.frames['frmCenter'].location.href='/project/list';</script>".NEW_LINE_CHAR;
            //$response['ret'] = array('ret'=>0);
            return $response['html_content'];  // 总是返回此结果

        }
    }
}
