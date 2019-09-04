<?php

namespace App\Http\Controllers;

class ListController extends Controller
{
    public $type_name= '';
    public $action= 'list';

    public $pageSize = 100;
    public $flag = "p";          // 当前页面标记
    public $pagesize_flag = "pagesize";

    public static function getFieldsInfo(&$a_arr){
        return AddController::getFieldsInfo($a_arr);
    }

    public function Init(&$request, &$a_arr){
        // 通过do而得到的分解出来的细致动作，仅仅依赖于do动作。放在最前面
        $l_act_type = \cString::getNameActType($request["do"]);
        $this->type_name = $l_act_type[0];
        $this->action = $l_act_type[1];

        // 初始化一些需要用到的默认数据
        if(!array_key_exists("parent_ids_arr", $a_arr)) $a_arr["parent_ids_arr"] = array();
        if(!array_key_exists("a_options", $a_arr)) $a_arr["a_options"] = array();
        if(!array_key_exists("sql_order", $a_arr)) $a_arr["sql_order"] = "order by `id` desc";
        if(!array_key_exists("tplname", $a_arr)) $a_arr["tplname"] = "list"; // $actionMap->getProp("path")
        if(!array_key_exists("default_sqlwhere", $a_arr)) $a_arr["default_sqlwhere"] = "where `status_` = 'use'"; // $actionMap->getProp("path")

        // 获取父级。依据指定的父级元素、及本级元素而获取到的id列表等相关数据。放在上面初始化之后
        $a_arr["parent_rela"] = GetParentIds($request, $a_arr["parent_ids_arr"], $a_arr["a_options"]);
    }

    public function execute(&$arr,&$actionMap,&$actionError,$request,&$response,$form,$get,$cookie){
        //$dbR = $arr['dbR'];
        if (isset($arr['p_def']) && $arr['p_def']['table_field_belong_project_id'] > 0 && ($arr['p_def']['id'] != $arr['p_def']['table_field_belong_project_id'])) {
            $p_obj = new \App\Repositories\Admin\ProjectRepository();
            $p_info_t_def = $p_obj->getProjectById($arr['p_def']['table_field_belong_project_id']);
            $dbR = new \DBR($p_info_t_def); // 字段定义表不在项目所在库
            //$p_id = $arr['p_def']['id'];
        } elseif(isset($arr['p_def'])) {
            $dbR = new \DBR($arr['p_def']); // 字段定义表在项目数据库
            //$p_id = $arr['p_def']['id'];
        } else {
            //$dbR = new \DBR();
            $dbR = $arr['dbR'];
            //$p_id = 1;
        }
        //$arr['default_sqlwhere'] .= ' AND p_id = ' . $p_id;

        if (array_key_exists("f_info",$arr)) {
            $l_fieldarr = getFieldArr($arr["f_info"],array("key"=>"name_eng","value"=>"name_cn"));
        }elseif (isset($arr['p_def'])) {
            $dbReal = new \DBR($arr['p_def']);
            $l_tmp = \DbHelper::getTblFields($dbReal, $arr['table_name']);
            $l_fieldarr = getFieldArr($l_tmp);
        } else {
            $dbReal = new \DBR();
            $l_tmp = \DbHelper::getTblFields($dbReal, $arr['table_name']);
            $l_fieldarr = getFieldArr($l_tmp);
        }

        // 查询 begin
        $sql_where = getSqlWhere($request,$arr["default_sqlwhere"]);
        $field_option = buildOptions($l_fieldarr,"",false);
        $method_option = get_method_option();
        // 查询 end

        // 分页
        $pagebar_arr = getPagebar($dbR, $this->pageSize, $this->flag,$this->pagesize_flag, $request, $sql_where);
        $page_bar_size = $pagebar_arr["page_bar_size"];

        // 具体数据
        $offset = ($pagebar_arr["_p"]-1)*$pagebar_arr["pageSize"];
        $l_arr = $dbR->getAlls($sql_where. " ".$arr["sql_order"]." limit $offset , ". $pagebar_arr["pageSize"]." ");
        //$sql = 'SELECT * FROM ' . $arr['table_name'] . ' ' . $sql_where. " ".$arr["sql_order"]." limit $offset , ". $pagebar_arr["pageSize"]." ";
        //$l_arr = collect(\DB::connection($dbR)->select($sql))->toArray();
        $arr["_arr"] = $l_arr;
        // 先获取模板
        //$content = file_get_contents(resource_path() . '/views/admin/' . $arr["tplname"] . env('BLADE_SUFFIX'));
        $l_file = $arr["tplname"] . '.html';
        $l_path = resource_path() . '/views/admin/';
        $content = file_get_contents($l_path . $l_file);
        // 加入头尾
        $header = file_get_contents(resource_path() . '/views/admin/'.'header.html');
        $footer = file_get_contents(resource_path() . '/views/admin/'.'footer.html');

        $data_arr = array(
            // 导航应该放到总类里面去执行，因为具备通用性
            "html_title" => $arr["html_title"],
            "html_name"  => $arr["html_name"],

            "parent_nav" => $arr["parent_rela"] ? @$arr["parent_rela"]["parent_nav"] : '',
            "parent_elements_str"=>$arr["parent_rela"] ? @$arr["parent_rela"]["parent_elements_str"] : '',// 多项用逗号隔开，单项的分号后面是表名简称没有前缀的
            "parent_ids_input_hidden"=>$arr["parent_rela"] ? @$arr["parent_rela"]["parent_ids_input_hidden"] : '',
            "parent_ids_url_build_query"=>$arr["parent_rela"] ? @$arr["parent_rela"]["parent_ids_url_build_query"] : '',

            "do"=>$request["do"],
            "type_name"=>$this->type_name,
            "action"   =>$this->action,
            $this->flag=>$pagebar_arr["_p"],
            $this->pagesize_flag=>$pagebar_arr['pageSize'],
            "flag"=>$this->flag,
            "pagesize_flag"=>$this->pagesize_flag,
            "pagebar"=>$page_bar_size,

            "sql_where"=>urlencode($sql_where),

            "field_option"=>$field_option,
            "method_option"=>$method_option,

            "tpl_xiangmuliebiao"=>$GLOBALS['language']['TPL_XIANGMU_STR'].$GLOBALS['language']['TPL_LIEBIAO_STR'],
            "tpl_dangqianweizhi"=>$GLOBALS['language']['TPL_DANGQIANWEIZHI_STR'],
            "tpl_xitongtongzhi"=>$GLOBALS['language']['TPL_XITONGTONGZHI_STR'],

            "tpl_sousuo"=>$GLOBALS['language']['TPL_SOUSUO_STR'],
            "tpl_chaxun"=>$GLOBALS['language']['TPL_CHAXUN_STR'],
            "tpl_bingcha"=>$GLOBALS['language']['TPL_BINGCHA_STR'],
            "tpl_tongshi"=>$GLOBALS['language']['TPL_TONGSHI_STR'],
            "tpl_huozhe"=>$GLOBALS['language']['TPL_HUOZHE_STR'],

            "tpl_zengjia"=>$GLOBALS['language']['TPL_ZENGJIA_STR'],
            "tpl_xiugai"=>$GLOBALS['language']['TPL_XIUGAI_STR'],
            "tpl_shanchu"=>$GLOBALS['language']['TPL_SHANCHU_STR'],

            "tpl_meiyexianshi"=>$GLOBALS['language']['TPL_MEIYEXIANSHI_STR'],
            "tpl_tiao"=>$GLOBALS['language']['TPL_TIAO_STR'],
            "tpl_shezhi"=>$GLOBALS['language']['TPL_SHEZHI_STR'],

            "tpl_qingquerenshifouzhendeshanchu"=>$GLOBALS['language']['TPL_QINGQUERENSHIFOUZHENDESHANCHU_STR'],
            "tpl_qingninxuanzhongyitiaoxinxi"  =>$GLOBALS['language']['TPL_QINGNINXUANZHONGYITIAOXINXI_STR'],

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

        return replace_template_para($data_arr,$content);
    }

}
