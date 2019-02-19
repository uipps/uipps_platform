<?php

namespace App\Http\Controllers\Admin;

use App\Services\Admin\ProjectService;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    protected $userService;
    protected $projectService;

    public function __construct(UserService $userService, ProjectService $projectService) {
        $this->userService = $userService;
        $this->projectService = $projectService;
    }

    public function mainpage(Request $request)
    {
        // 检查是否登录
        $l_auth = $this->userService->ValidatePerm($request);
        $_SESSION = session()->all();
        if (!$l_auth || !isset($_SESSION['user']) || !$_SESSION['user']) {
            return redirect('/admin/login');
        }
        $GLOBALS['cfg']['RES_WEBPATH_PREF'] = env('RES_WEBPATH_PREF');

        // 先获取模板
        $l_file = __FUNCTION__ . '.blade.php';
        $l_path = resource_path() . '/views/admin/';
        $content = file_get_contents($l_path . $l_file);
        if (false !== strpos($content, '<!--{'))
            file_put_contents($l_path . $l_file, str_replace(['<!--{', '}-->'], ['{{', '}}'], $content));
        // 加入头尾
        $header = file_get_contents(resource_path() . '/views/admin/'.'header.html');  // 标准头
        $footer = file_get_contents(resource_path() . '/views/admin/'.'footer.html');  // 标准尾
        $data_arr = array(
            'system_name'=>$GLOBALS['language']['SYSTEM_NAME_STR'],
            'RES_WEBPATH_PREF'=>$GLOBALS['cfg']['RES_WEBPATH_PREF'],
            'header'=>$header,
            'footer'=>$footer
        );
        //$content = replace_template_para($data_arr,$content);

        // 替换其中的css地址和js地址 以后采用缓存文件，而不用每次都实时取
        //$content = replace_cssAndjsAndimg($content,$GLOBALS['cfg']['SOURCE_CSS_PATH'],$GLOBALS['cfg']['SOURCE_JS_PATH'],$GLOBALS['cfg']['SOURCE_IMG_PATH']);
        // 将外链的js替换为其相应js内容
        //$content = jssrc2content($content);
        // 替换其中的css地址和js地址 以后采用缓存文件，而不用每次都实时取
        // = replace_cssAndjsAndimg($content,$GLOBALS['cfg']['SOURCE_CSS_PATH'],$GLOBALS['cfg']['SOURCE_JS_PATH'],$GLOBALS['cfg']['SOURCE_IMG_PATH']);// js中还有图片

        //$response['html_content'] = replace_template_para($data_arr,$content);

        return view('admin/mainpage', $data_arr);
        //return __NAMESPACE__ . "<br>\r\n" . __CLASS__ .  "<br>\r\n"  . __FUNCTION__;
    }

    public function frmMainMenu(Request $request) {
        // 检查是否登录
        $l_auth = $this->userService->ValidatePerm($request);
        $_SESSION = session()->all();
        if (!$l_auth || !isset($_SESSION['user']) || !$_SESSION['user']) {
            return redirect('/admin/login');
        }
        $GLOBALS['cfg']['RES_WEBPATH_PREF'] = env('RES_WEBPATH_PREF');
        $GLOBALS['cfg']['db_character'] = env('db_character', 'utf8');
        $GLOBALS['cfg']['out_character'] = env('out_character', 'utf8');

        if (isset($_SESSION['user']['nickname'])) {
            $nickname = convCharacter($_SESSION['user']['nickname']);
        }else {
            $nickname = convCharacter($_SESSION['user']['username']);
        }

        // 先获取模板
        $l_file = __FUNCTION__ . '.blade.php';
        $l_path = resource_path() . '/views/admin/';
        $content = file_get_contents($l_path . $l_file);
        if (false !== strpos($content, '<!--{'))
            file_put_contents($l_path . $l_file, str_replace(['<!--{', '}-->'], ['{{', '}}'], $content));
        // 加入头尾
        $header = file_get_contents(resource_path() . '/views/admin/'.'header.html');  // 标准头
        $footer = file_get_contents(resource_path() . '/views/admin/'.'footer.html');  // 标准尾
        $data_arr = array(
            'nickname'=>$nickname,
            'ip'=>getip(),
            'RES_WEBPATH_PREF'=>$GLOBALS['cfg']['RES_WEBPATH_PREF'],
            'header'=>$header,
            'footer'=>$footer
        );
        return view('admin/frmMainMenu', $data_arr);
    }

    public function GetProjectListJS(Request $request, $pt, $node) {
        // 检查是否登录
        $l_auth = $this->userService->ValidatePerm($request);
        $_SESSION = session()->all();
        if (!$l_auth || !isset($_SESSION['user']) || !$_SESSION['user']) {
            return redirect('/admin/login');
        }
        $GLOBALS['cfg']['RES_WEBPATH_PREF'] = env('RES_WEBPATH_PREF');
        $GLOBALS['cfg']['db_character'] = env('db_character', 'utf8');
        $GLOBALS['cfg']['out_character'] = env('out_character', 'utf8');

        $arr = $this->projectService->getProjectList($request);// 项目数据
        if ("RES"==$pt) {
            $contentjs = $this->buildjsRES($arr, $node);
        }else {
            $contentjs = $this->buildjsPUB($arr, $node);
        }

        // 先获取模板
        $l_file = __FUNCTION__ . '.blade.php';
        $l_path = resource_path() . '/views/admin/';
        $content = file_get_contents($l_path . $l_file);
        if (false !== strpos($content, '<!--{'))
            file_put_contents($l_path . $l_file, str_replace(['<!--{', '}-->'], ['{{', '}}'], $content));
        $data_arr = array(
            'contentjs'=>$contentjs
        );
        return view('admin/GetProjectListJS', $data_arr);
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
  cur_node.setLink('main.php?do=resource_list&p_id=".$val["id"]."', '');
  cur_node = cur_node.addSibling(parent.Tree_LAST, '资源同步配置');
  cur_node.setLink('main.php?do=res_sync_list&p_id=".$val["id"]."', '');
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
  cur_node.setLink('main.php?do=template_list&p_id=".$val["id"]."', '');
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
}
