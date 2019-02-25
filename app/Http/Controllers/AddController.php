<?php

namespace App\Http\Controllers;

class AddController extends Controller
{
    public static function getFieldsInfo(&$a_arr){
        // 先去表定义表找对应的table_id，
        $dbR = $a_arr["dbR"];
        $dbR->table_name = $a_arr["TBL_def"];
        $t_info = $dbR->getOne(" where name_eng='".$a_arr["table_name"]."' ");
        if ($t_info) {
            $t_id = $t_info["id"];
        }else {
            echo "table_empty";//作为错误信息显示出来
            return null;
        }
        $a_arr["t_def"] = $t_info;

        // 也可以获取真实的、实时的字段定义，直接从该表结构中获取到
        //$dbR->table_name = $a_arr["table_name"];
        //$f_real_info = $dbR->getTblFields2();
        //print_r($f_real_info);

        // 获取数据表的字段。完全依据field_def，所有的字段操作必须同field_def中一致
        $dbR->table_name = $a_arr["FLD_def"];
        $f_info = $dbR->getAlls(" where t_id='$t_id' and status_='use' ".$a_arr["sql_order"]);
        if (!$f_info) {
            // 未获取到字段定义信息
            echo "field_empty";  // 作为错误信息，显示给用户
            return null;
        }
        // 数字索引变为字段索引
        $f_info = \cArray::Index2KeyArr($f_info, array("key"=>"name_eng", "value"=>array()));
        $a_arr["f_info"] = $f_info;

        return $f_info;
    }
}
