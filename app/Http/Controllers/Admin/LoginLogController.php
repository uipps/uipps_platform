<?php

namespace App\Http\Controllers\Admin;


use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\ListController;
use DBR;
use DBW;
use Pager;


class LoginLogController extends ListController
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
        $request['do'] = 'loginlog_list';



        if (!isset($request["pagesize"])) $request["pagesize"] = $this->pageSize;
        if (!isset($request["pagesize_form"])) $request["pagesize_form"] = 0;

        // 显示页面数据
        $dbR = new DBR();
        $dbR->table_name = TABLENAME_PREF."loginlog";

        // 有查询的时候，查询sql语句保留
        $sql_where = isset($request["sql_where"]) ? urldecode($request["sql_where"]) : "";
        // 如果有查询条件 begin
        if (key_exists("search_field_1",$request)) {

            $sql_where = getWhere($sql_where,$request);

            // 有查询条件的时候，同时将sql语句注入到 request 数组中，便于作为链接的一部分
            $request["sql_where"] = $sql_where;
        }
        //$field_option = buildOptions(array("username"=>"用户名","nickname"=>"昵称","succ_or_not"=>"成功或失败"),"",false);
        $field_option = buildOptions(getFieldArr($dbR->getTblFields()),"",false);
        $method_option = get_method_option();
        // 查询 end


        // 分页部分 开始
        if (intval($request["pagesize_form"])>=1) {
            $pageSize = intval($request["pagesize_form"]); // 替换掉request中旧的
            $request[$this->pagesize_flag] = $request["pagesize_form"];
            unset($request["pagesize_form"]);
        }else {
            $pageSize = ($request[$this->pagesize_flag]>=1)?(int)$request[$this->pagesize_flag]:$this->pageSize;  // how many  per page
        }
        $itemSum = $dbR->getCountNum($sql_where);
        $_p = isset($request[$this->flag])?$request[$this->flag]:1; // page number $currentPageNumber
        $_p = (int)$_p;                   // int number
        $_p = ($_p>ceil($itemSum/$pageSize))?ceil($itemSum/$pageSize):$_p;
        $_p = ($_p<1)?1:$_p;
        $pager = new Pager("?".http_build_query(get_url_gpc($request)),$itemSum,$pageSize,$_p,$this->flag);
        $pagebar = $pager->getBar();
        $page_bar_size = $pagebar." &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  每页显示 <a href='".$pager->buildurl(array($this->pagesize_flag=>5))."'>5条</a> <a href='".$pager->buildurl(array($this->pagesize_flag=>50))."'>50条</a> <a href='".$pager->buildurl(array($this->pagesize_flag=>100))."'>100条</a>";
        //."(共找到：".$pager->itemSum." 条)";
        // 分页部分 结束

        // 具体数据
        $offset = ($_p-1)*$pageSize;
        $_arr = $dbR->getAlls("$sql_where order by id desc limit $offset , $pageSize ");

        $ziduan_arr = getZiduan("id:日志ID;username:用户名;nickname:昵称;logindate:登录时间;clientip:登录IP;serverip:主机IP;succ_or_not:请求成败");// 需要的字段
        $show_arr = buildH($_arr,$ziduan_arr);
        $show = $show_arr[0];
        $show_title = $show_arr[1];

        // 先获取模板
        $content = file_get_contents(resource_path() . '/views/admin/' . $request['do'] . '.html');
        // 加入头尾
        $header = file_get_contents(resource_path() . '/views/admin/'.'header.html');  // 标准头
        $footer = file_get_contents(resource_path() . '/views/admin/'.'footer.html');  // 标准尾
        $data_arr = array(
            "do"=>$request["do"],
            $this->flag=>$_p,
            $this->pagesize_flag=>$pageSize,

            "sql_where"=>urlencode($sql_where),

            "field_option"=>$field_option,
            "method_option"=>$method_option,

            "flag"=>$this->flag,
            "pagesize_flag"=>$this->pagesize_flag,
            "loginlog_show"=>$show,
            "loginlog_show_title"=>$show_title,
            "nav"=>"计划任务列表",
            "pagebar"=>$page_bar_size,
            "RES_WEBPATH_PREF"=>$GLOBALS['cfg']['RES_WEBPATH_PREF'],
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
        $response['ret'] = array('ret'=>0);
        return $response['html_content'];  // 总是返回此结果
    }
}
