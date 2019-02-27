<?php

namespace App\Http\Controllers\Admin;

use App\Services\Admin\ProjectService;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DBR;

class AdminController extends Controller
{
    protected $userService;
    protected $projectService;

    public function __construct(UserService $userService, ProjectService $projectService) {
        $this->userService = $userService;
        $this->projectService = $projectService;
    }

    public function mainpage(Request $a_request)
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
        $request['do'] = 'project_add';
        //$_SESSION = session()->all();


        // 先获取模板
        $content = file_get_contents(resource_path() . '/views/admin/' . __FUNCTION__ . '.html');
        // 加入头尾
        $header = file_get_contents(resource_path() . '/views/admin/'.'header.html');  // 标准头
        $footer = file_get_contents(resource_path() . '/views/admin/'.'footer.html');  // 标准尾
        $data_arr = array(
            "system_name"=>$GLOBALS['language']['SYSTEM_NAME_STR'],
            "RES_WEBPATH_PREF"=>$GLOBALS['cfg']['RES_WEBPATH_PREF'],
            "get_csrf_token"=>csrf_token(),
            "header"=>$header,
            "footer"=>$footer
        );
        $content = replace_template_para($data_arr,$content);

        // 替换其中的css地址和js地址 以后采用缓存文件，而不用每次都实时取
        $content = replace_cssAndjsAndimg($content,$GLOBALS['cfg']['SOURCE_CSS_PATH'],$GLOBALS['cfg']['SOURCE_JS_PATH'],$GLOBALS['cfg']['SOURCE_IMG_PATH']);
        // 将外链的js替换为其相应js内容
        //$content = jssrc2content($content);
        // 替换其中的css地址和js地址 以后采用缓存文件，而不用每次都实时取
        $content = replace_cssAndjsAndimg($content,$GLOBALS['cfg']['SOURCE_CSS_PATH'],$GLOBALS['cfg']['SOURCE_JS_PATH'],$GLOBALS['cfg']['SOURCE_IMG_PATH']);// js中还有图片

        $response['html_content'] = replace_template_para($data_arr,$content);

        return $response['html_content'];
    }

    public function frmMainMenu(Request $a_request) {
        // 检查是否登录
        $l_auth = $this->userService->ValidatePerm($a_request);
        $_SESSION = session()->all();
        if (!$l_auth || !isset($_SESSION['user']) || !$_SESSION['user']) {
            return redirect('/admin/login');
        }

        if (isset($_SESSION['user']['nickname'])) {
            $nickname = convCharacter($_SESSION['user']['nickname']);
        }else {
            $nickname = convCharacter($_SESSION['user']['username']);
        }

        // 先获取模板
        $l_file = __FUNCTION__ . '.html';
        $l_path = resource_path() . '/views/admin/';
        $content = file_get_contents($l_path . $l_file);
        // 加入头尾
        $header = file_get_contents(resource_path() . '/views/admin/'.'header.html');  // 标准头
        $footer = file_get_contents(resource_path() . '/views/admin/'.'footer.html');  // 标准尾
        $data_arr = array(
            'nickname'=>$nickname,
            'ip'=>getip(),
            'RES_WEBPATH_PREF'=>$GLOBALS['cfg']['RES_WEBPATH_PREF'],
            "get_csrf_token"=>csrf_token(),
            'header'=>$header,
            'footer'=>$footer
        );

        $content = replace_template_para($data_arr,$content);

        // 替换其中的css地址和js地址 以后采用缓存文件，而不用每次都实时取
        $content = replace_cssAndjsAndimg($content,$GLOBALS['cfg']['SOURCE_CSS_PATH'],$GLOBALS['cfg']['SOURCE_JS_PATH'],$GLOBALS['cfg']['SOURCE_IMG_PATH']);
        // 将外链的js替换为其相应js内容
        //$content = jssrc2content($content);
        // 替换其中的css地址和js地址 以后采用缓存文件，而不用每次都实时取
        $content = replace_cssAndjsAndimg($content,$GLOBALS['cfg']['SOURCE_CSS_PATH'],$GLOBALS['cfg']['SOURCE_JS_PATH'],$GLOBALS['cfg']['SOURCE_IMG_PATH']);// js中还有图片

        $response['html_content'] = replace_template_para($data_arr,$content);

        return $response['html_content'];
        //return view('admin/frmMainMenu', $data_arr);
    }

    public function GetProjectListJS(Request $a_request, $pt, $node) {
        // 检查是否登录
        $l_auth = $this->userService->ValidatePerm($a_request);
        $_SESSION = session()->all();
        if (!$l_auth || !isset($_SESSION['user']) || !$_SESSION['user']) {
            return redirect('/admin/login');
        }

        $arr = $this->projectService->getProjectList($a_request);// 项目数据
        if ("RES"==$pt) {
            $contentjs = $this->buildjsRES($arr, $node);
        }else {
            $contentjs = $this->buildjsPUB($arr, $node);
        }

        // 先获取模板
        $l_file = __FUNCTION__ . '.html';
        $l_path = resource_path() . '/views/admin/';
        $content = file_get_contents($l_path . $l_file);
        // 加入头尾
        $header = file_get_contents(resource_path() . '/views/admin/'.'header.html');  // 标准头
        $footer = file_get_contents(resource_path() . '/views/admin/'.'footer.html');  // 标准尾

        $data_arr = array(
            "contentjs"=>$contentjs,
            "RES_WEBPATH_PREF"=>$GLOBALS['cfg']['RES_WEBPATH_PREF'],
            "get_csrf_token"=>csrf_token(),
            "header"=>$header,
            "footer"=>$footer
        );
        $content = replace_template_para($data_arr,$content);

        // 替换其中的css地址和js地址 以后采用缓存文件，而不用每次都实时取
        $content = replace_cssAndjsAndimg($content,$GLOBALS['cfg']['SOURCE_CSS_PATH'],$GLOBALS['cfg']['SOURCE_JS_PATH'],$GLOBALS['cfg']['SOURCE_IMG_PATH']);
        // 将外链的js替换为其相应js内容
        //$content = jssrc2content($content);
        // 替换其中的css地址和js地址 以后采用缓存文件，而不用每次都实时取
        $content = replace_cssAndjsAndimg($content,$GLOBALS['cfg']['SOURCE_CSS_PATH'],$GLOBALS['cfg']['SOURCE_JS_PATH'],$GLOBALS['cfg']['SOURCE_IMG_PATH']);// js中还有图片


        $response['html_content'] = replace_template_para($data_arr,$content);
        return $response['html_content'];

        //return view('admin/GetProjectListJS', $data_arr);
    }

    public function GetTemplateListJS(Request $a_request) {
        $request = $a_request->all();
        //print_r($request);exit;
        $_SESSION = session()->all();
        $GLOBALS['cfg']['RES_WEBPATH_PREF'] = env('RES_WEBPATH_PREF');

        $dbR = new DBR();
        $dbR->table_name = "project";
        $p_arr = $dbR->getOne(" where id = ".($request["p_id"]+0));
        //print_r($p_arr); // 模板信息需要从另一个库中获取信息
        if (!$p_arr) {
            // 漏洞:如果攻击者使用一个不存在的id，则$p_arr返回的是NULL????其他类似漏洞有时间的时候全部处理一下。
            $response['html_content'] = date("Y-m-d H:i:s") . "project not exist!";
            return $response['html_content'];
        }
        //$dsn = \DbHelper::getDSNstrByProArrOrIniArr($p_arr);
        //$dbR->dbo = &DBO('', $dsn);
        //$dbR = null;
        $dbR = new DBR($p_arr);
        $dbR->table_name = "table_def";

        // 需要根据用户权限显示其具有操作权限的表
        if (1==$_SESSION["user"]["if_super"]) {
            $l_where = "";  // " where type='$pt' "
        } else {
            $l_ts = \UserPrivilege::getSqlInTableByPid($request['p_id']);
            if (""!=$l_ts) $l_where = " and id in ($l_ts)";
            else $l_where = "and id<0 ";  // 将获取不到任何数据, 如果么有权限的话
        }
        $arr = $dbR->getAlls("where `name_eng` NOT LIKE '%table_def' and `name_eng` NOT LIKE '%field_def' " . $l_where);

        // 如果还有t_id的话，则需要获取指定的表的数据
        if (isset($request['t_id'])) {
            $dbR->table_name = "table_def";
            $l_t_info = $dbR->getOne('where id="'. ($request['t_id']+0) .'" or name_eng="'. $request['t_id'] .'"');

            if (!empty($l_t_info)) {
                $dbR->table_name = $l_t_info["name_eng"];
                $l_arr_tbl = $dbR->getAlls();
            }
        }

        if (isset($request["cont_type"]) && "json"==trim($request["cont_type"])) {
            if (isset($request['t_id']) && isset($l_arr_tbl)) {
                //
                $for_json = format_for_json($l_arr_tbl,"s_shu_xingqiu_id");
                $contentjs = getJson($for_json,"project","name_cn","pingyin_shouzimu");
            }else {
                // 获取所有的project及其所有的表定义表，而不用一个去获取，以后完善此方式????
                $for_json = format_for_json($arr,"p_id");
                $contentjs = getJson($for_json,$name="project","id","name_cn");
            }
        } else {
            $contentjs = $this->buildjs($arr,$request["node"],$p_arr);
        }

        // 先获取模板
        $content = file_get_contents(resource_path() . '/views/admin/' . __FUNCTION__ . '.html');
        // 加入头尾
        $header = file_get_contents(resource_path() . '/views/admin/'.'header.html');  // 标准头
        $footer = file_get_contents(resource_path() . '/views/admin/'.'footer.html');  // 标准尾
        $data_arr = array(
            "contentjs"=>$contentjs,
            "RES_WEBPATH_PREF"=>$GLOBALS['cfg']['RES_WEBPATH_PREF'],
            "get_csrf_token"=>csrf_token(),
            "header"=>$header,
            "footer"=>$footer
        );
        $content = replace_template_para($data_arr,$content);

        // 替换其中的css地址和js地址 以后采用缓存文件，而不用每次都实时取
        $content = replace_cssAndjsAndimg($content,$GLOBALS['cfg']['SOURCE_CSS_PATH'],$GLOBALS['cfg']['SOURCE_JS_PATH'],$GLOBALS['cfg']['SOURCE_IMG_PATH']);
        // 将外链的js替换为其相应js内容
        //$content = jssrc2content($content);
        // 替换其中的css地址和js地址 以后采用缓存文件，而不用每次都实时取
        $content = replace_cssAndjsAndimg($content,$GLOBALS['cfg']['SOURCE_CSS_PATH'],$GLOBALS['cfg']['SOURCE_JS_PATH'],$GLOBALS['cfg']['SOURCE_IMG_PATH']);// js中还有图片

        $response['html_content'] = replace_template_para($data_arr,$content);
        return $response['html_content'];
    }



    public function buildjsRES($arr, $nodeid=2){
        $str = "
<script language='javascript'>
var parentNode = null;
if(parent.tree && parent.tree != 'undefined')
{
  parentNode=parent.tree.getNode($nodeid);
}
if(parentNode && parentNode.loaded != true)
{
  parentNode.loaded=true;
  ";
        if (!empty($arr)) {
            foreach ($arr as $val){
                $cn_name = convCharacter($val["name_cn"]);  // 昵称

                $str .= "
  var cur_node=parentNode.addChild(parent.Tree_LAST, '".$cn_name."');
  cur_node = cur_node.addChild(parent.Tree_LAST, '资源管理');
  cur_node.setLink('/resource/list?p_id=".$val["id"]."', '');
  cur_node = cur_node.addSibling(parent.Tree_LAST, '资源同步配置');
  cur_node.setLink('/res_sync/list?p_id=".$val["id"]."', '');
    ";
            }
        }

        $str .= "
  parentNode.delChild(0);
}
</script>
       ";

        return $str;
    }

    public function buildjsPUB($arr, $nodeid=2){
        $str = "
<script type=\"text/javascript\">
var parentNode = null;
if(parent.tree && parent.tree != 'undefined')
{
  parentNode=parent.tree.getNode($nodeid);
}
if(parentNode && parentNode.loaded != true)
{
  parentNode.loaded=true;
    ";

        if (!empty($arr)) {
            foreach ($arr as $val){
                $cn_name = convCharacter($val["name_cn"]);  // 昵称

                $str .= "
     var cur_node=parentNode.addChild(parent.Tree_LAST, '".$cn_name."');
  var cur_node = cur_node.addChild(parent.Tree_LAST, '发布列表');
  cur_node.setScript('LoadTemplateListMenu(tree.getSelect().id, ".$val["id"].")');
  cur_node.addChild(".$val["parent_id"].",'loading...');
        ";
                if (isset($_SESSION["user"]["if_super"]) && 1 == $_SESSION["user"]["if_super"]) {
                    $str .= "
  cur_node = cur_node.addSibling(parent.Tree_LAST, '模板管理');
  cur_node.setLink('/template/list?p_id=".$val["id"]."', '');
      ";
                }
            }
        }

        $str .= '
     parentNode.delChild(0);
}
</script>';

        return $str;
    }


    public function buildjs($arr, $nodeid=24, $p_arr){
        $p_id = $p_arr["id"];
        $l_type = $p_arr["type"];
        if ("DATA"==$l_type) {
            $l_do = "/dbdocs/list";
        }else {
            $l_do = "/document/list";
        }

        $str = "
<script type=\"text/javascript\">
var parentNode = null;
if(parent.tree && parent.tree != 'undefined')
{
  parentNode=parent.tree.getNode($nodeid);
}
if(parentNode && parentNode.loaded != true)
{
  parentNode.loaded=true;
    ";
        if (!empty($arr)) {
            foreach ($arr as $val){
                // 如果设置了链接，则不自动拼装，因所用模板也可能是自定义
                $tree_link = "{$l_do}?p_id=$p_id&t_id=".$val["id"];
                if (@$val['tree_link']) $tree_link = $val['tree_link'];

                $str .= "
    var node=parentNode.addChild(parent.Tree_LAST, '".$val["name_cn"]."');
  node.setLink('{$tree_link}');
      ";
            }
        }
        $str .= '
     parentNode.delChild(0);
}
</script>';

        return $str;
    }
}
