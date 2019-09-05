<?php

class DbHelper{
    const DOT_REPLACE_TO_STR = '---______---'; // 配置中不能有. ,否则会被当成数组的层级，因此需要将配置中的.替换成特殊符号便于还原
    //
    protected $ColumnTypes = array(
        "VARCHAR","TINYINT","TEXT","DATE",
        "SMALLINT","MEDIUMINT","INT","BIGINT","FLOAT","DOUBLE","DECIMAL",
        "DATETIME","TIMESTAMP","TIME","YEAR",
        "CHAR","TINYBLOB","TINYTEXT","BLOB","MEDIUMBLOB","MEDIUMTEXT",
        "LONGBLOB","LONGTEXT","ENUM","SET","BIT","BOOL","BINARY","VARBINARY");

    // 常量无法获取
    public static function get_s_ON_UPDATE_CURRENT_TIMESTAMP() {
        return '@@ON__CURRENT_TIMESTAMP@@';
    }

    // 将数据库project表的数组或mysql_config.ini中数组类型变为dsn字符串
    public static function getDSNstrByProArrOrIniArr($dsn){
        if (is_array($dsn))
            $dsn = self::getConnectName($dsn, true, false);
        return $dsn;
    }

    /**
     * 拼装 别称 和 用于dbo字符串类型dsn(数组也要转化为字符串)
     * 别称一定要保留原味别称
     *
     * @param array,string $dsn: $dsn可能是数组、可能是mysql://root、也可能就是存在或不存在的别称三种情况
     * @param string $a_type:数据库连接类型,R:读库,W:写库
     * @return unknown
     */
    public static function FmtDSNAndGetMdb2NameAlias($dsn=array(),$a_type='R'){
        $l_name = "";
        if ('R'!=$a_type) $a_type='W';  // 强制只有两张类型

        if (!is_null($dsn) && !is_array($dsn)) {
            // dsn需要解析 , ???? 需要判断host是否为空
            if (false===strpos($dsn,"://")) {
                $l_name = $dsn;  // 当dsn就是昵称Alias的时候
                // 可能是某个dsn的别名，此时只需要获取到相应的dsn赋值给$dsn即可
                // /path/to/ini:biz_r 这样的，也可能是biz_r这样单串字符，当前只支持单串
                // 依然用默认的mysql_config.ini和路径
                if ('R'==$a_type) {
                    __gener_conf($GLOBALS['cfg']['INI_CONFIGS_PATH'], $GLOBALS['cfg']['INI_DB_DSN_CONFIGS_FILE'],"",$dsn);
                }else {
                    __gener_conf($GLOBALS['cfg']['INI_CONFIGS_PATH'], $GLOBALS['cfg']['INI_DB_DSN_CONFIGS_FILE'],$dsn,"");
                }
                $dsn = $_SERVER["SRV_DB_DSN_".$a_type];
            }
        }

        $dsn = empty($dsn) ? $_SERVER["SRV_DB_DSN_".$a_type]: $dsn;
        if (empty($dsn)){ echo "dsn error!"; exit;}

        // 兼容性修改, 兼容new dbR($p_arr)的关键字
        if (is_array($dsn)) {
            $dsn = DbHelper::getDSNstrByProArrOrIniArr($dsn);
        }
        // 此时的dsn已经被强制为string类型，并且是dsn串，类似: mysql://username:pass@.....
        // name变成了 localhost:3307:root:db_pass:dpa形式的别名, 当然如果外部强制给出了别名, 则以别名为主
        if (""==$l_name) {
            $l_name = cString::GetAliasByDsnString($dsn,$l_name);
        }

        return array('l_name'=>$l_name,'dsn'=>$dsn);
    }

    public static function getAutocreamentDbname($a_proj, $a_f_name="db_name", $a_data=array(), $a_default=''){
        if (''==$a_default) $a_default = $GLOBALS['cfg']['DB_DEFALUT_TYPE'];

        // 如果提交了数据库连接信息$a_data，则需要联合这些信息判断，防止人为没有注册的数据库存在。今后完善之
        $l_p_arr = array(0, 0);  // max必须需要两个参数
        $l_f_name = empty($a_f_name)?"db_name":$a_f_name;
        if(!empty($a_proj)){
            foreach ($a_proj as $p_val){
                if($a_default==substr(strtolower(trim($p_val[$l_f_name])),0,strlen($a_default))){
                    $l_p_arr[] = str_replace($a_default,"",strtolower(trim($p_val[$l_f_name]))) * 1;
                }
            }
        }
        $defaul_dbname = (max($l_p_arr)+1);
        $defaul_dbname = str_pad($defaul_dbname, 3, "0", STR_PAD_LEFT);  //将少于3位的1补全为001
        $defaul_dbname = $a_default.$defaul_dbname;
        return $defaul_dbname;
    }

    public function getAutocreamentTBname($a_proj, $a_f_name="Name", $a_data=array(), $a_default=''){
        if (''==$a_default) $a_default = $GLOBALS['cfg']['DB_TB_DEFALUT_TYPE'];
        return DbHelper::getAutocreamentDbname($a_proj, $a_f_name, $a_data, $a_default);
    }

    public function getAutocreamentFieldname($a_proj, $a_f_name="Field", $a_data=array(), $a_default=''){
        if (''==$a_default) $a_default = $GLOBALS['cfg']['DB_FIELD_DEFALUT_TYPE'];
        return DbHelper::getAutocreamentDbname($a_proj, $a_f_name, $a_data, $a_default);
    }

    public static function getAllDB(&$dbR, $l_no_need_db = array("information_schema", "mysql", "test", "performance_schema")){
        $l_rlt = array();
        $l_tmp = $dbR->SHOW_DATABASES();
        foreach ($l_tmp as $l_arr){
            $l_d_n = trim($l_arr["Database"]);
            // 过滤掉一些默认的表
            if (!in_array($l_d_n, $l_no_need_db)) $l_rlt[] = $l_d_n;
        }

        return $l_rlt;
    }
    /**
     * 创建基本表
     *
     * @param array $data_arr
     * @param string $a_sql 额外的需要执行的sql语句
     * @param sting $db_charset
     */
    public static function createDBandBaseTBL($p_arr, $a_sql="", $db_charset="utf8", $source="db", $a_e_wai=true){
        if (!array_key_exists('db_name', $p_arr) && !array_key_exists('db_port', $p_arr)) return 0;
        $db_name = $p_arr['db_name'];
        $p_arr['type'] = strtoupper($p_arr['type']); // 强制转大写

        // 检查数据库是否能连上，再判断该创建的数据库是否不存在，不存在则创建数据库，并进行use;
        $tmp_info = $p_arr;
        if (isset($tmp_info['db_name'])) unset($tmp_info['db_name']); // 数据库可能并不存在，不unset连接数据库会报错
        $dbr2 = new DBR($tmp_info);
        $all_database_list = DbHelper::getAllDB($dbr2, []); // 获取全部数据库
        // 在指定的主机上建库
        if (!in_array($p_arr['db_name'], $all_database_list)) {
            $dbW = new DBW($tmp_info);
            try {
                // 如果没有建库权限，可能会报错
                $dbW->create_db($p_arr['db_name']);
            } catch (\Exception $l_err) {
                echo 'create database ' . $p_arr['db_name'] . ' error!: ' . $l_err->getMessage();
                exit;
            }
        }
        // TODO 前缀功能暂未实现、暂不支持 以后有空了再完善
        //if (isset($p_arr['db_prefix'])){};

        // 依据项目的类型，确定需要建立哪几张基本表
        switch ($p_arr['type']) {
            case "NORMAL":
            case "PHP_PROJECT":
                //$a_sql .= file_get_contents(database_path('migrations/cms.sql'));

                // 同时创建资源库和基本表
                // $db_name_res = $form["db_name"]."_res";
                //$l_e_wai = file_get_contents($GLOBALS['cfg']['PATH_RUNTIME']."/DataDriver/sql/php_project_init.sql");
                break;
            case "CMS":
                $a_sql .= file_get_contents(database_path('migrations/cms.sql'));

                // 同时创建资源库和基本表
                // $db_name_res = $form["db_name"]."_res";
                $l_e_wai = file_get_contents(database_path('migrations/cms_init_insert.sql'));
                break;
            case "SYSTEM":
                //$a_sql .= file_get_contents(database_path('migrations/uipps.sql'));

                $l_e_wai = file_get_contents(database_path('migrations/uipps_init_insert.sql'));
                // TODO 其他sql可能也需要进行替换，先将涉及到的替换了再说
                if (env('DB_PREFIX')) {
                    $l_e_wai = table_field_def_tmpl_design_sql_replace($l_e_wai, env('DB_PREFIX'));
                }

                $p_arr["name_cn"] = convCharacter($GLOBALS['language']['SYSTEM_NAME_STR'],true);
                if (!array_key_exists("id", $p_arr)) {
                    $p_arr["id"] = 1;  // 应当自动获取其id，暂时先手动指定
                }
                break;
            case "PUB":

                break;
            case "RES":
                // 具备文件目录结构的数据表, 此数据表各项需要重新整理一下
                $a_sql .= file_get_contents(database_path('migrations/file_dir_res.sql'));
                break;
            case "GRAB":
                $a_sql .= file_get_contents(database_path('migrations/grab.sql'));

                $l_e_wai = file_get_contents(database_path('migrations/grab_init.sql'));

                break;
            default:

                break;
        }
        // 检查表定义表和字段定义表是否存在，所有的数据库都必须有这两张表
        $_SESSION = session()->all();
        $creator = 1;
        if (isset($_SESSION['user']) && $_SESSION['user'] && isset($_SESSION['user']['id'])) {
            $creator = $_SESSION['user']['id'];
        }
        $a_data_arr = array("source"=>$source,"creator"=>$creator);  // 能在外部增加字段的

        // 表定义表和字段定义表有可能是挂靠在其他项目上的
        $table_field_belong_project_id = 0;
        if ($p_arr['table_field_belong_project_id'] > 0 && (!isset($p_arr['id']) || $p_arr['id'] != $p_arr['table_field_belong_project_id'])) {
            // 需要获取对应的项目信息，并且检查该项目中的是否存在表定义表和字段定义表，如果该项目也挂靠在其他项目则报错；暂不支持多级挂靠，避免出现互相挂靠而死循环
            // $p_info_t_def = \App\Models\Admin\Project::find(1); 也可，不过不好加缓存
            $p_obj = new \App\Repositories\Admin\ProjectRepository();
            $p_info_t_def = $p_obj->getProjectById($p_arr['table_field_belong_project_id']);

            $table_field_belong_project_id = $p_arr['table_field_belong_project_id'];

            // 切换到指定的数据库，需要携带数据库名称信息，重新连一下数据库。
            $dbR = new DBR($p_info_t_def);
            $l_real_tbls = $dbR->getDBTbls($p_info_t_def['db_name']); // 获取当前连接库的所有数据表，加不加 $p_info_t_def['db_name'] 都可

            if ($l_real_tbls) {
                $l_real_tbls = array_column($l_real_tbls, 'Name', 'Name');
            }
            $table_def = (isset($p_arr['table_def_table']) && $p_arr['table_def_table']) ? $p_arr['table_def_table'] : 'table_def';
            $field_def = (isset($p_arr['field_def_table']) && $p_arr['field_def_table']) ? $p_arr['field_def_table'] : 'field_def';
            // 检查一下表定义表和字段定义表是否存在，tmpl_design_table
            if (!in_array($table_def, $l_real_tbls) || !in_array($field_def, $l_real_tbls)) {
                // 没有相应表，则需要报错，
                echo ' can not find table_def , field_def in this project id ' . var_export($p_arr, true);
                exit;
            }

            // 暂时不支持cms类型的项目进行字段定义表分离
            if ('CMS' == $p_arr['type']) {
                echo ' project type ' . $p_arr['type'] . ' , do not suppert table_def , field_def in different project:' . var_export($p_arr, true);
                exit;
            }
        } else {
            // 如果就在项目本身，需要检查表定义表和字段定义表是否存在，不存在则创建
            $a_sql .= file_get_contents(database_path('migrations/table_field.sql'));
        }

        // 首先创建相应的数据表
        DbHelper::execDbWCreateInsertUpdate($p_arr, $a_sql);

        if ($table_field_belong_project_id) {
            // 字段定义表,表定义表
            if (isset($p_info_t_def['table_def_table']) && $p_info_t_def['table_def_table']) {
                $table_def = $p_info_t_def['table_def_table'];
                $field_def = $p_info_t_def['field_def_table'];
            } else if (isset($p_info_t_def['db_prefix']) && $p_info_t_def['db_prefix']) {
                $table_def = $p_info_t_def['db_prefix'] . 'table_def';
                $field_def = $p_info_t_def['db_prefix'] . 'field_def';
            } else {
                // $table_def = 'table_def';
                // $field_def = 'field_def';
            }
            $project_arr = $p_info_t_def; // 需要执行sql的项目连接信息
        } else {
            $table_def = (isset($p_arr['table_def_table']) && $p_arr['table_def_table']) ? $p_arr['table_def_table'] : 'table_def';
            $field_def = (isset($p_arr['field_def_table']) && $p_arr['field_def_table']) ? $p_arr['field_def_table'] : 'field_def';
            $project_arr = $p_arr; // 需要执行sql的项目连接信息
        }

        // 字段定义表已经存在，无需创建表，但是需要插入数据
        DbHelper::fill_table($project_arr, $a_data_arr, "all", $field_def, $table_def, $p_arr);
        DbHelper::fill_field($project_arr, $a_data_arr, "all", $field_def, $table_def, true, $p_arr);

        // 作为表定义表的一部分，通常情况下需要进行字段算法更新的
        if ($a_e_wai) {
            $l_e_tmpl = file_get_contents(database_path('migrations/tmpl_design_init_insert.sql'));
            if (env('DB_PREFIX'))
                $l_e_tmpl = table_field_def_tmpl_design_sql_replace($l_e_tmpl, env('DB_PREFIX'));
            DbHelper::execDbWCreateInsertUpdate($project_arr, $l_e_tmpl,array("INSERT INTO ", "REPLACE INTO ", "UPDATE "));
        }

        // ------ 如果有额外的初始化数据需要insert或update的时候
        if ($a_e_wai && isset($l_e_wai) && '' != $l_e_wai) {
            // insert或update一些初始数据
            DbHelper::execDbWCreateInsertUpdate($p_arr, $l_e_wai, array("INSERT INTO ", "REPLACE INTO ", "UPDATE "));
        }

        // 如果重新创建的系统，则需要修改mysql数据库连接信息初始值
        // if ("SYSTEM"==strtoupper($p_arr["type"])) cFile::modifyMysqlConfigIniAndLANGConfigFileWhenCreateSYSTEM($p_arr);

        return 1;
    }

    //
    public static function execDbWCreateInsertUpdate($p_arr, $a_sql, $a_spe_arr=array("CREATE ","INSERT INTO ","UPDATE ","REPLACE INTO ")){
        // 换另外一种更合理的算法
        $a_sql = cString::lineDelBySpe($a_sql,"--");  // 仅仅去掉行注释
        $a_sql = str_ireplace('ON UPDATE CURRENT_TIMESTAMP',DbHelper::get_s_ON_UPDATE_CURRENT_TIMESTAMP(),$a_sql);  // 替换掉其中的ON UPDATE CURRENT_TIMESTAMP为特定字符串执行sql的时候然后替换回来，因为里面还有sql关键词'update '
        $dbW = new DBW($p_arr);

        if (!empty($a_spe_arr)) {
            $l_str = implode("|",$a_spe_arr);
            $l_str = "/($l_str)/i";

            if( preg_match_all($l_str,trim($a_sql),$l_matches,PREG_SET_ORDER) ) {
                $l_arr = preg_split($l_str, $a_sql);  // 正则分割成多块
                //
                foreach ($l_matches as $l_k => $l_v){
                    $l_arr[$l_k+1] = $l_v[1] . $l_arr[$l_k+1]; // 字符串补全，还原
                }
            }
        }
        if (!isset($l_arr) || !$l_arr) return 1;

        // 然后逐一执行
        foreach ($l_arr as $l_sql) {
            if (""!=trim($l_sql)) {
                $l_sql = str_ireplace(DbHelper::get_s_ON_UPDATE_CURRENT_TIMESTAMP(),'ON UPDATE CURRENT_TIMESTAMP',$l_sql);  // sql字符串复原
                try{
                    $dbW->exec($l_sql);//$l_err = $dbW->errorInfo();
                }catch (\Exception $l_err) {
                    // 需要进行错误处理，稍后完善???? sql有错误，后面的就不用执行了。
                    echo "\r\n".  date("Y-m-d H:i:s") . " FILE: ".__FILE__." ". " FUNCTION: ".__FUNCTION__." Line: ". __LINE__."\n" . "sql: $l_sql,  _arr:" . $l_err->getCode() . ' '. var_export($l_err->getMessage(), TRUE);
                    exit;
                    //return $l_err[2];
                }
            }
        }
        return 1;
    }

    /**
     * 各个项目自动检测
     * 数据表中可以为空的可以不必填写
     * 数据表中不能为空的必须填写，没有填写的采用默认值，没有默认值的一定要填写，否则返回错误。
     *
     * @param array $form  提交的表单数组
     * @param array $f_info  字段数组
     * @param bool $with_null 是否携带空值, 修改的时候需要修带，而增加的时候无需空值. 默认全带
     * @param bool $if_edit 是否编辑数据, 编辑数据的时候，一些必须字段如果并不存在于form数组中，则认为该字段不做修改
     * @param $_FILES $a_files 上传文件，图片和其他文件
     * @return array    结果数组, 也可能是错误信息
     */
    public static function getInsertArrByFormFieldInfo($form, $f_info, $with_null=true, $if_edit=false, $a_files=array()){
        $data_arr = array();

        // 图片上传处理 begin  （TODO 目前只做图片，附件以后做）
        $upload_result = upload_imgs($a_files, $GLOBALS['cfg']['IMG_UPLOAD_PATH'], $GLOBALS['cfg']['IMG_URL_PRE']);
        if (isset($upload_result['url']) && !empty($upload_result['url'])) {
            // 有图片上传, 需要将相应字段填充上返回的数值
            foreach ($upload_result['url'] as $l_up_field => $img_url) {
                $l__field_name = substr($l_up_field, strlen($GLOBALS['cfg']['UPLOADIMG_PRE'])); // 去掉前缀
                // 检查字段是否存在于现有字段中
                if (array_key_exists($l__field_name, $f_info)) {
                    // 如果强制使用上传文件，则覆盖form中的图片路径
                    if (1 == $form[$GLOBALS['cfg']['RADIO_UPLOADIMG_CHANGE'] . $l__field_name] || '' == trim($form[$l__field_name])) {
                        $form[$l__field_name] = $img_url;
                    }
                }
            }
        }
        // 图片上传处理 end

        //
        foreach ($f_info as $l_arr){
            // 给出的字段可能是中文、英文两种可能
            if (array_key_exists($l_arr["name_cn"], $form)) {
                $form_d = $form[$l_arr["name_cn"]];
                $form[$l_arr["name_eng"]] = $form_d;// 用英文字段替换掉中文字段
                // unset($form[$l_arr["name_cn"]]);  // 可以去掉中文的字段
            }
            if (!array_key_exists($l_arr["name_eng"], $form)) {
                // 如果form中不存在该字段，要么赋空值，要么就跳过--编辑数据的时候可以不用包含全部数据
                if ($if_edit) continue;
                $form_d = null;
            }else {
                $form_d = $form[$l_arr["name_eng"]];  // 不能使用trim, 需要保持提交数据的原始性
            }

            // 如果有算法得到的数值
            if ( array_key_exists(Parse_Arithmetic::getArithmetic_Result_str(),$l_arr) ) {
                if ( array_key_exists("value",$l_arr[Parse_Arithmetic::getArithmetic_Result_str()]) ) {
                    $form_d = $l_arr[Parse_Arithmetic::getArithmetic_Result_str()]["value"];
                }else if (array_key_exists("method2val",$l_arr[Parse_Arithmetic::getArithmetic_Result_str()])) {
                    if ("Form::Password"==$l_arr['f_type'] && strlen($form_d)>=32) {
                        // 如果是密码类型的，当长度大于30的时候，认为是已经加密过的，则无需进行重复加密算法
                    }else {
                        $l_func = $l_arr[Parse_Arithmetic::getArithmetic_Result_str()]["method2val"];
                        $form_d = $l_func($form_d);
                    }
                }
            }

            if (""===$form_d || is_null($form_d)) { // 千万不要用empty进行判断，因为0也是empty
                if ("NO"==strtoupper( $l_arr["is_null"] )) {
                    // 不为空的字段必须填写或采用默认值
                    if (""!=($l_arr["default"]) && false === strpos( strtoupper(trim($l_arr["default"])), 'CURRENT_TIMESTAMP') ) {
                        // not null类型的默认值不可能为null，因此只需要判断""即可 剔除特殊情况默认 CURRENT_TIMESTAMP
                        $data_arr[$l_arr["name_eng"]] = $l_arr["default"];
                    }else if ( "ENUM"==strtoupper(trim($l_arr["type"])) ){
                        if (is_array($l_arr["length"])) {
                            // 数组中找不到空值则说明有错
                            if (!in_array("", $l_arr["length"])) {
                                $data_arr["___ERR___"][] = __FILE__ . ' ' . __LINE__ . ' ' . $l_arr["name_eng"];
                            }
                        }else {
                            // 枚举串中找不到空值则说明有错
                            if ( false===strpos( $l_arr["length"], "''")) {
                                $data_arr["___ERR___"][] = __FILE__ . ' ' . __LINE__ . ' ' . $l_arr["name_eng"];
                            }
                        }
                        // 如果是枚举型，并且有一个''的枚举项，不填写数据也不需要写该字段sql也能入库。
                        //$data_arr[$l_arr["name_eng"]] = '';  // 是否赋值无关紧要,sql没有此字段也能入库。
                    }else {
                        // 自增和时间类型的可以不用填写, 否则返回一个错误
                        if ("auto_increment"!=strtolower(trim($l_arr["extra"])) && "timestamp" != strtolower(trim($l_arr["type"]))) {
                            $data_arr["___ERR___"][] = __FILE__ . ' ' . __LINE__ . ' ' . $l_arr["name_eng"];
                        }
                    }
                }else {
                    // 可以赋值为空，因为修改数据的时候，从有到无进行修改的时候就需要这个字段，否则会丢失此字段
                    // 另一方法，也可能是后台发布，没有经过前端算法进行赋值的，也能运行到此步骤
                    if ($with_null) {
                        if (''===$form_d) $form_d = null;
                        $data_arr[$l_arr["name_eng"]] = $form_d;
                    }
                }
            }else {
                // 添加或修改数据的时候，先进行还原数据, 保证数据在入库之前先要进行htmlspecialchars一下
                // 实际测试中发现不需要 htmlspecialchars_decode($form_d) 这样，也许有其他地方进行了转换。以后研究????
                $data_arr[$l_arr["name_eng"]] = $form_d;
            }
        }

        return $data_arr;
    }

    // 自动填充 table_def 表
    public static function fill_table($p_arr, $data_arr, $tbl_name="all", $f_def="field_def", $t_def="table_def", $real_p_arr=[], $no_table=array()){
        $if_repair = true;
        if (""==$tbl_name ) {
            return null;
        }
        if (!$real_p_arr) $real_p_arr = $p_arr;
        $real_p_id = $real_p_arr['id'];

        $dbR = new DBR($p_arr);
        $dbW = new DBW($p_arr);

        if ($p_arr['id'] != $real_p_id) {
            // 项目和字段定义表分离，不在同一个库里面的情况
            $dbReal = new DBR($real_p_arr);
            $all_table = $dbReal->getDBTbls($real_p_arr['db_name']); // 需要创建的项目的数据表
        } else {
            // 先获取所有的表
            $all_table = $dbR->getDBTbls($p_arr['db_name']);
        }
        if (!$all_table){
            return null;
        }

        foreach ($all_table as $l_table){
            // 如果是特定的或者是全部则允许通过
            if ($l_table["Name"] == $tbl_name || "all"==$tbl_name) {
                if (!in_array($l_table["Name"],$no_table)) {
                    $l_comment = trim($l_table["Comment"]);
                    if(""!=$l_comment){
                        $l_data_arr = array("description"=>$l_comment);// 表的注释部分写入描述字段中去
                    }else{
                        $l_data_arr = array();
                    }
                    DbHelper::ins2table_def($dbR,$dbW,array_merge($l_data_arr,$data_arr), $l_table["Name"], $f_def, $t_def, $real_p_id);
                }
            }
        }
        // end

        if ($if_repair) {
            // 还需要进行修复, 对于废弃的表需要删除或改为废弃状态，当前直接删除
            // 对于在字段定义表中属于多余表的那些字段，统统删除
            $dbR->table_name = $t_def;
            $l_old_tbls = $dbR->getAlls("where p_id = $real_p_id and status_='use' order by id");
            $l_old_tbls = cArray::Index2KeyArr($l_old_tbls, array("key"=>"name_eng", "value"=>"name_eng"));
            $all_table = cArray::Index2KeyArr($all_table, array("key"=>"Name", "value"=>"Name"));
            $l_duo = array_diff($l_old_tbls,$all_table);  // 在old中，但不在实际的表结构中
            if (!$l_duo)
                return 1;
            // 多出的字段需要删除或修改为废弃状态
            foreach ($l_duo as $l_tbl) {
                $l_row = $dbR->getOne("where p_id = $real_p_id and name_eng = '" . $l_tbl . "' and status_='use' ");

                if ($l_row) {
                    $dbW->table_name = $t_def;
                    //$dbW->delOne(array("id"=>$l_row["id"]),"id");
                    $dbW->updateOne(array('status_' => 'del'), "id=" . $l_row["id"]);
                    // 同时还需要删除字段定义表中的该表的所有字段
                    $dbW->table_name = $f_def;
                    //$dbW->delOne(array("t_id"=>$l_row["id"]),"t_id");
                    $dbW->updateOne(array('status_' => 'del'), "t_id=" . $l_row["id"]);
                }
            }
        }


        return 1;
    }

    // 往表定义表中插入数据
    public static function ins2table_def(&$dbR, &$dbW, $a_data_arr, $a_tablename, $f_def="field_def", $t_def="table_def",$p_id=0){
        $name_eng = $a_tablename;   //
        $name_cn = $name_eng;      // 暂时用英文的或者用表注释
        if(isset($a_data_arr["description"]) && $a_data_arr["description"]) $name_cn = $a_data_arr["description"];

        $_SESSION = session()->all();
        $creator = 1;
        if (isset($_SESSION['user']) && $_SESSION['user'] && isset($_SESSION['user']['id'])) {
            $creator = $_SESSION['user']['id'];
        }
        $dbW->table_name = $t_def;
        //print_r($dbW->getExistorNot("name_eng='".$name_eng."'"));
        if($dbW->getExistorNot("name_eng='".$name_eng."'  AND p_id = $p_id ")){
            // 表如果存在是否需要进行修复???? 对于tpl_type等的修改基于什么呢？如何获取这样的数据呢？
            // 暂时先不更新存在中的数据表
            usleep(300);
            return true;
        }

        // 不存在则插入数据库中
        $data_arr = array(
            "p_id"        => $p_id,
            "field_def_table"=> $f_def,
            "creator"     => convCharacter($creator,true),
            "createdate"    => date("Y-m-d"),
            "createtime"    => date("H:i:s"),
            "menddate"      => date("Y-m-d"),
            "js_code_add_edit"      => '',
            "name_eng"     => trim($name_eng),
            "name_cn"     => convCharacter($name_cn,true)
        );
        $data_arr = array_merge($data_arr,$a_data_arr);  // 外面给出的数据可修改里面的参数
        try {
            $last_id = $dbW->insertOne($data_arr);
        } catch (\Exception $e) {
            echo $dbW->getSQL();
            echo "insert error!";
            print_r($e->getMessage());
        }

        usleep(300);
        return $last_id;
    }

    // 自动填充 field_def 表，填充字段定义表，但需要处理的数据库跟字段定义表可能不在同一个库。
    // $p_arr是字段定义表所在库；而 $real_p_arr 是项目数据表所在库
    // tabel_def的修复前面已经在前面做了，即fill_table；这里只关注字段定义表
    public static function fill_field($p_arr, $data_arr, $want_tbl="all", $f_def="field_def", $t_def="table_def", $if_repair=true, $real_p_arr=[]){
        if (!$p_arr || '' == $want_tbl) {
            return 0;
        }
        if (!$real_p_arr) $real_p_arr = $p_arr;

        $dbR = new DBR($p_arr);

        // 自动完成所有表的导入，包括自身也需要导入
        $dbR->table_name = $t_def;
        if ("all"==$want_tbl) {
            $_tbls = $dbR->getAlls("where status_='use' AND  p_id = " . $real_p_arr['id']); // fill_table 已经修正数据表了
        } else {
            $_tbl = $dbR->getOne("where name_eng = '$want_tbl' AND  p_id = " . $real_p_arr['id']);
            $_tbls = array($_tbl);
        }
        if (!$_tbls)
            return 0;

        // 需要for 循环
        foreach ($_tbls as $_tbl){
            if ($_tbl["id"]>0) {
                DbHelper::ins2field_def($p_arr, $data_arr,$_tbl["id"],$f_def,$t_def,$if_repair, $real_p_arr);
            }
        }
        return 1;
    }

    // 往字段定义表中插入数据
    public static function ins2field_def($p_arr, $a_data_arr, $t_id=0, $f_def="field_def", $t_def="table_def",$if_repair=true, $real_p_arr=[]){
        $dbR = new DBR($p_arr); // p_arr是字段定义表所在项目
        $dbW = new DBW($p_arr);

        if (!$real_p_arr) $real_p_arr = $p_arr;

        // 在 table_def 中的 id
        $dbR->table_name = $t_def;
        $_tbl_name = $dbR->getOne("where id = $t_id");
        if (!$_tbl_name || !isset($_tbl_name['name_eng'])) {
            echo __FILE__ . ' LINE:' . __LINE__ . ' table not exists in ' . $t_def . ' , id not found:' . $t_id . ' $_tbl_name:' . var_export($_tbl_name, true);
            exit;
        }

        if ($p_arr['id'] != $real_p_arr['id']) {
            // 项目和字段定义表分离，不在同一个库里面的情况
            $dbReal = new DBR($real_p_arr);
            $all_field = $dbReal->getTblFields($_tbl_name["name_eng"]);
        } else {
            $all_field = $dbR->getTblFields($_tbl_name["name_eng"]);
        }
        if (!$all_field) {
            return 0;
        }

        $_SESSION = session()->all();
        if ('cli' == php_sapi_name() && !isset($_SESSION['user'])) {
            $_SESSION['user'] = ['id' => 1];
        }

        // 获取数组的维度
        $l_depth=cString_num::get_array_depth($a_data_arr);

        $dbW->table_name = $f_def;
        // 循环插入
        foreach ($all_field as $l_arr) {
            $name_eng   = strtolower($l_arr["Field"]);   // 很特殊的key Tables_in_auto
            $name_cn   = $name_eng;     // 暂时用英文的
            $l_jiben = DbHelper::getFieldDefBixu($l_arr);
            if (false!==strpos($l_jiben["type"],"text")) $l_jiben["f_type"] = "Form::TextArea";  // 增加一个数据

            // 如果字段有Comment信息，则将中文名替换为描述的前部分
            if (array_key_exists("Comment",$l_arr)) {
                $l_comment = trim($l_arr["Comment"]);
                if($l_comment) {
                    $l_jiben["description"] = $l_comment;// 字段的注释部分写入描述字段中去
                    // 同时将中文名使用描述信息的前半部分
                    $l_tmp = preg_split("/[,.:; ]/",$l_comment,-1,PREG_SPLIT_NO_EMPTY);// 暂时只能用半角符号。测试发现全角标点符号，。：；经常匹配出乱码，导致入库sql报错而影响入库
                    if (""!=trim($l_tmp[0])) $name_cn = trim($l_tmp[0]);
                }
            }

            if($l_db_row = $dbW->getExistorNot("t_id = $t_id  and name_eng='".$name_eng."' and status_ = 'use' ")){
                // 进行修复，只修改同表结构相关的字段
                if ($if_repair) {
                    $data_arr = array(
                        "mender"     => convCharacter($_SESSION["user"]["id"],true),
                        "menddate"    => date("Y-m-d"),
                        "mendtime"      => date("H:i:s"),
                        "name_cn"     => $name_cn
                    );
                    $data_arr = array_merge($data_arr,$l_jiben);
                    $dbW->table_name = $f_def;
                    try{
                        $dbW->updateOne($data_arr, "id=".$l_db_row["id"]);
                    } catch (\Exception $l_err) {
                        // 需要进行错误处理，稍后完善???? sql有错误，后面的就不用执行了。
                        echo "\r\n".  date("Y-m-d H:i:s") . " FILE: ".__FILE__." ". " FUNCTION: ".__FUNCTION__." Line: ". __LINE__."\n" . " sql: ". $dbW->getSQL() ." _err:" . $l_err->getMessage(). ' ' . var_export($l_err->getMessage(), TRUE);
                        //
                    }
                }
                //echo "t_id = $t_id  and name_eng='".$name_eng."' exist!".NEW_LINE_CHAR;
                continue;
            } else {
                // 不存在则插入数据库中
                $data_arr = array(
                    "creator"     => convCharacter($_SESSION["user"]["id"],true),
                    "createdate"    => date("Y-m-d"),
                    "createtime"    => date("H:i:s"),
                    "default"       => '',
                    "menddate"      => date("Y-m-d"),
                    "t_id"          => $t_id,
                    "name_eng"     => trim($name_eng),
                    "name_cn"     => $name_cn
                );
                $l_data_arr = array();
                if (array_key_exists($name_eng,$a_data_arr) && is_array($a_data_arr[$name_eng])) {
                    $l_data_arr = $a_data_arr[$name_eng];
                }else {
                    if (1==$l_depth) $l_data_arr = $a_data_arr;
                }

                $data_arr = array_merge($data_arr,$l_jiben,$l_data_arr);  // 外面给出的数据可修改里面的参数
                try {
                    $last_id = $dbW->insertOne($data_arr);
                } catch (\Exception $l_err) {
                    // 需要进行错误处理，稍后完善???? sql有错误，后面的就不用执行了。
                    echo "\r\n".  date("Y-m-d H:i:s") . " FILE: ".__FILE__." ". " FUNCTION: ".__FUNCTION__." Line: ". __LINE__."\n" . " sql: ". $dbW->getSQL() ." _err:" . var_export($l_err->getMessage(), TRUE);
                    //
                }
            }
            usleep(300);
        }
    }

    // 通过父级id数组，逐级获取并最终获取到最后一级的数据
    public static function getProTblFldArr(&$dbR, $a_data, $a_p_self_ids=array()){
        $l_rlt = array();  // 返回所有级别的数组，并以

        if (empty($a_p_self_ids) || !is_array($a_p_self_ids)) {
            return $l_rlt;
        }

        /*//$l_name0_r = $GLOBALS['cfg']['SYSTEM_DB_DSN_NAME_R'];
        //$dbR->dbo = &DBO($l_name0_r);
        $l_srv_db_dsn = $dbR->getDSN("array");
        if (!empty($l_srv_db_dsn["database"])) $dbR->SetCurrentSchema($l_srv_db_dsn["database"]);
        $l_err = $dbR->errorInfo();
        if ($l_err[1]>0){
            // 数据库连接失败后
            echo date("Y-m-d H:i:s") . " 出错了， 错误信息： " . $l_err[2]. ".";
            return $l_rlt;
        }*/
        //echo __LINE__ . "\r\n";exit;
        // 父级id都是1开始, 加上自身，因此至少有一项
        // 先手动写，以后完善之???? foreach去掉了对$a_p_self_ids索引的依赖
        $i=1;
        foreach ($a_p_self_ids as $l_p_self_id){
            // 数组中必须包含此字段的数据，否则跳出本来循环
            if (empty($a_data[$l_p_self_id["ziduan"]])) {
                continue;
            }

            if (1==$i) {
                // 逐级循环, 第一级是项目表，包含了数据库连接信息
                $l_tbl_name = (isset($l_p_self_id["table_name"]) && $l_p_self_id["table_name"]) ? $l_p_self_id["table_name"] : env('DB_PREFIX')."project";
                $dbR->table_name = $l_tbl_name;
                // 都是id，整型数据，因此无需引号
                $l_p_s1 = $dbR->getOne(" where id=".$a_data[$l_p_self_id["ziduan"]]);
                if (!$l_p_s1) {
                    Log::info('project empty!');
                    return [];
                }
                $l_rlt["p_def"] = $l_p_s1;

                // 表定义表、字段定义表可能不在项目内，这里需要依据项目设定的表定义表所在项目进行获取
                //$table_field_belong_project_id = 0;
                if ($l_p_s1['table_field_belong_project_id'] > 0 && ($l_p_s1['id'] != $l_p_s1['table_field_belong_project_id'])) {
                    $p_obj = new \App\Repositories\Admin\ProjectRepository();
                    $p_info_t_def = $p_obj->getProjectById($l_p_s1['table_field_belong_project_id']);
                    //$table_field_belong_project_id = $l_p_s1['table_field_belong_project_id'];

                    // 字段定义表,表定义表在其他项目中
                    if (isset($p_info_t_def['table_def_table']) && $p_info_t_def['table_def_table']) {
                        $table_def = $p_info_t_def['table_def_table'];
                        $field_def = $p_info_t_def['field_def_table'];
                    } else if (isset($p_info_t_def['db_prefix']) && $p_info_t_def['db_prefix']) {
                        $table_def = $p_info_t_def['db_prefix'] . 'table_def';
                        $field_def = $p_info_t_def['db_prefix'] . 'field_def';
                    } else {
                        $table_def = 'table_def';
                        $field_def = 'field_def';
                    }
                    $project_arr = $p_info_t_def; // 需要执行sql的项目连接信息
                } else {
                    $table_def = (isset($l_p_s1['table_def_table']) && $l_p_s1['table_def_table']) ? $l_p_s1['table_def_table'] : 'table_def';
                    $field_def = (isset($l_p_s1['field_def_table']) && $l_p_s1['field_def_table']) ? $l_p_s1['field_def_table'] : 'field_def';
                    $project_arr = $l_p_s1; // 需要执行sql的项目连接信息
                }
                $l_rlt['p_def']['TBL_def'] = $table_def;
                $l_rlt['p_def']['FLD_def'] = $field_def;

                // 该项目下所有的表
                /*$dsn = DbHelper::getDSNstrByProArrOrIniArr($l_p_s1);
                $dbR->dbo = &DBO('', $dsn);
                $dbR->SetCurrentSchema($l_p_s1['db_name']);*/
                $dbR = new DBR($project_arr);
                $dbR->table_name = empty($l_p_self_id["t_table_name"]) ? $table_def :$l_p_self_id["t_table_name"];
                $l_t_all = $dbR->getAlls(" where status_!='stop' AND p_id = " . $l_p_s1['id'], 'id, name_eng, name_cn');
                $l_rlt["t_all_"] = $l_t_all;

                /*$dbR->table_name = $l_p_self_id["table_name"];
                $l_p_s1 = $dbR->getOne(" where ".$l_p_self_id["ziduan"]." = ".$a_data[$l_p_self_id["ziduan"]]);
                $l_rlt[$l_p_self_id["ziduan"]] = $l_p_s1;*/
            } else if (2==$i) {
                // 在此处会存在两种可能：1 表定义表；2 该表的文档列表
                // 某个模板id，即数据库下的哪张表，应该去访问表定义表

                /*$dsn = DbHelper::getDSNstrByProArrOrIniArr($l_p_s1);
                $dbR->dbo = &DBO('', $dsn);
                $dbR->SetCurrentSchema($l_p_s1['db_name']);*/
                $dbR = new DBR($project_arr);
                //$dbR = null;$dbR = new DBR($l_p_s1);  // 涉及到数据库重连的问题，包含了数据库连接信息
                $dbR->table_name = $l_tbl_name = $table_def; // 表定义表的数据必须获取到
                $l_t_def_arr = $dbR->getOne(" where id = ".$a_data[$l_p_self_id["ziduan"]]);  // 在没有$a_p_self_ids设置的情况下也能获取到数据
                $l_rlt["t_def"] = $l_t_def_arr;
                //print_r($l_t_def_arr);exit;

                // 需要将表级别的信息也一同获取到，例如表模板设计表的数据也需要一同获取到其信息
                $dbReal = new DBR($l_p_s1);
                $l_real_tbls = $dbReal->getDBTbls();
                $l_real_tbls = cArray::Index2KeyArr($l_real_tbls,array("key"=>"Name","value"=>"Name"));
                $l_tmpl_design = TABLENAME_PREF . "tmpl_design";
                if (array_key_exists($l_tmpl_design,$l_real_tbls)) {
                    $dbR->table_name = $l_tmpl_design;
                    $l_tmpl_design = $dbR->getAlls("where tbl_id=".$a_data[$l_p_self_id["ziduan"]]." and status_='use'");
                    if (!empty($l_tmpl_design))$l_rlt["t_def"]["tmpl_design"] = $l_tmpl_design;
                }

                // 如果指定了字段，或者不获取的字段则需要进行范围确定
                $l_f_range = "";
                if (array_key_exists("nof",$a_data)) {
                    //require_once("common/lib/cString.cls.php");
                    $l_nof = cString_SQL::decodestr2sql($a_data["nof"]);
                    if(""!=$l_nof) $l_f_range = " and name_eng not in ($l_nof)";
                }else if (array_key_exists("fid",$a_data)) {
                    //require_once("common/lib/cString.cls.php");
                    $l_fid = cString_SQL::decodestr2sql($a_data["fid"]);
                    if(""!=$l_fid) $l_f_range = " and name_eng in ($l_fid)";
                }

                // 同时获取该表所有字段定义信息, 多行数据
                $dbR->table_name = empty($l_p_self_id["table_name2"]) ? $field_def:$l_p_self_id["table_name2"]; // 字段定义表的数据必须获取到
                $l_f_def_tbl = $dbR->getAlls(" where t_id = ".$a_data[$l_p_self_id["ziduan"]] . $l_f_range. " order by list_order asc,id asc ");  // 字段定义表中的定义. 在没有$a_p_self_ids设置的情况下也能获取到数据
                $l_f_def_real= DbHelper::getFieldInfoByTbl($dbReal, $l_t_def_arr["name_eng"]); // 实际、真实的表获取
                // 同时获取实际表(真实表,实际的表,真实的表)表结构中的字段信息,然后组合成完整的信息
                // 兼容以前的必须保留之前的字段
                $l_tmp_arr = DbHelper::BaseReplaceDuo($l_f_def_real, $l_f_def_tbl, DbHelper::getField7Attribute());
                $l_rlt["f_info"] = $l_tmp_arr[0];
                $l_rlt["f_def_duo"] = $l_tmp_arr[1];
                $l_rlt["f_def_stop"] = $l_tmp_arr[2];
                unset($l_tmp_arr);unset($l_f_def_tbl);unset($l_f_def_real);
                // 返回两个区别对待的数组

                // 涉及到分页， 应当在具体的业务中进行

            }else if (3==$i){
                // 具体某张表中的具体数据了，不过先需要去字段定义表中获取到字段的算法以后，再才从具体表中获取数据
                $l_tmp_tbl = empty($l_rlt["t_def"]["name_eng"])? $field_def : $l_rlt["t_def"]["name_eng"];
                $dbR->table_name = $l_p_self_id["table_name"] ? $l_p_self_id["table_name"] : $l_tmp_tbl; // 字段定义表
                $l_f_def_arr = $dbR->getOne(" where id=".$a_data[$l_p_self_id["ziduan"]]);
                $l_rlt["f_data"] = $l_f_def_arr;  // 非表定义表字段定义表中具体的单行数据


            }else {
                // 更多级别以后完善之

            }

            $i++;
        }

        return $l_rlt;
    }

    // 将字段定义表中的数据依据实际表的字段信息进行替换并返回实际表组成的.内部使用
    public static function BaseReplaceDuo($a_base_arr, $a_mult_arr, $a_base_replace_mult_key){
        // 数字索引变为字段索引
        $l_base_tmp = cArray::Index2KeyArr($a_base_arr, array("key"=>"name_eng", "value"=>array()));
        $l_mult_arr = cArray::Index2KeyArr($a_mult_arr, array("key"=>"name_eng", "value"=>array()));

        $l_base_arr  = array();  // 按照字段定义表中的顺序重新组织基本字段数组
        $l_stop_arr = array();  // 停用的字段
        $l_duo_arr  = array();  // 数值的差

        // 将多字段的数组中的部分字段替换为base中的字段数值
        if(!empty($l_mult_arr)){
            foreach ($l_mult_arr as $l_f=>$l_val){
                // 先替换字段定义表中的很多字段
                if ("use"!=$l_val["status_"]) {
                    $l_stop_arr[$l_f] = $l_val;
                }else if (array_key_exists($l_f,$l_base_tmp)) {
                    // 字段也在真实表中，则需要进行替换工作
                    if (is_array($a_base_replace_mult_key)) {
                        // 基于base数组进行值替换
                        foreach ($a_base_replace_mult_key as $l_k){
                            $l_mult_arr[$l_f][$l_k] = $l_base_tmp[$l_f][$l_k];
                        }
                    }
                    // 同时也需要重组基本表, 相当于增加了字段，中文名也一同给替换了
                    $l_base_arr[$l_f] = $l_mult_arr[$l_f];
                }else {
                    $l_duo_arr[$l_f] = $l_val;
                }
            }
        }
        return array($l_base_arr,$l_duo_arr,$l_stop_arr);
    }
    // 字段定义表中的字段七大基本属性字段
    public static function getField7Attribute(){
        return array("is_null","key","extra","type","length","attribute","default");
    }
    // 获取并分解表字段的7大特征属性：Null,Key,Extra,type,length,attribute,Default
    public static function getFieldDefBixu($l_arr){
        $data_arr = array();
        if (array_key_exists("Field", $l_arr)) {
            $_type_all   = DbHelper::explode_type_length_attribute($l_arr);
            $data_arr = array(
                "is_null"      => $l_arr["Null"],
                "key"        => $l_arr["Key"],
                "extra"        => $l_arr["Extra"],
                "type"        => $_type_all["type"],
                "length"      => $_type_all["length"],
                "attribute"      => $_type_all["attribute"],
                "default"      => convCharacter($l_arr["Default"],true)
            );
        }

        return $data_arr;
    }

    // 依据数据表英文名，获取到字段全部信息，类似field_def中的字段
    public static function getFieldInfoByTbl(&$dbR, $tbl_name_eng, $a_field_def_arr=array()){
        $l_rlt = array();

        // 先获取字段定义表的数据, 只需要其中中文名称即可
        if(!empty($a_field_def_arr)) $l_name_arr = cArray::Index2KeyArr($a_field_def_arr, array("key"=>"name_eng", "value"=>"name_cn"));
        else $l_name_arr = array();

        $all_field = $dbR->getTblFields($tbl_name_eng);

        // 获取不同字段
        if (!empty($all_field)) {
            foreach ($all_field as $l_arr){
                $name_eng   = strtolower($l_arr["Field"]);   // 很特殊的key Tables_in_auto
                $name_cn   = array_key_exists($name_eng,$l_name_arr)?$l_name_arr[$name_eng]:$name_eng;  // 中文名不存在则用英文名
                $l_jiben = DbHelper::getFieldDefBixu($l_arr);

                $data_arr = array(
                    "name_eng"     => trim($name_eng),
                    "name_cn"     => convCharacter($name_cn,true)
                );
                $data_arr = array_merge($data_arr,$l_jiben);
                $l_rlt[] = $data_arr;
            }
        }

        //
        return $l_rlt;
    }

    /**
     * 在数据库不为空，并且没有默认值的非自增字段
     * 可以放在判断是否执行提交操作的地方
     * 可以用于validate类里面去做自动的校验，当然也可以人工写
     *
     * @param obj $dbR 数据库连接对象
     * @param array $a_arr  预留的，可以输入很多参数的数组
     * @return array
     */
    public static function getBiXuFields(&$dbR, $a_arr=array()){
        $l_rlt = array();

        //
        if (array_key_exists("f_info", $a_arr) && !empty($a_arr["f_info"])) {
            foreach ($a_arr["f_info"] as $l_f_arr) {
                if ("NO"==strtoupper($l_f_arr["is_null"]) && ""==trim($l_f_arr["default"])) {
                    $l_rlt[] = trim($l_f_arr["name_eng"]);

                    // 只需要返回一个
                    return $l_rlt;
                }
            }
        }

        // 获取表名, 进行相关字段的判断过程类似上面。以后完善之????
        /*if(array_key_exists("table_name", $a_arr)) {
          $dbR->table_name = $a_arr["table_name"];
        }*/

        // 获取数据表结构

        return $l_rlt;
    }




    //define("PMA_MYSQL_INT_VERSION",51000);
    public static function explode_type_length_attribute(&$row){
        $type             = $row['Type'];
        $type_and_length  = DbHelper::PMA_extract_type_length($row['Type']);

        // reformat mysql query output - staybyte
        // loic1: set or enum types: slashes single quotes inside options
        if (preg_match('@^(set|enum)\((.+)\)$@i', $type, $tmp)) {
            $tmp[2]      = substr(preg_replace('@([^,])\'\'@', '\\1\\\'', ',' . $tmp[2]), 1);
            $type         = $tmp[1] . '(' . str_replace(',', ', ', $tmp[2]) . ')';
            $type         = htmlspecialchars($type);  // for the case ENUM('&#8211;','&ldquo;')
            $binary       = 0;
            $unsigned     = 0;
            $zerofill     = 0;
            $timestamp    = 0;
        } else {
            // strip the "BINARY" attribute, except if we find "BINARY(" because
            // this would be a BINARY or VARBINARY field type
            if (!preg_match('@BINARY[\(]@i', $type)) {
                $type     = preg_replace('@BINARY@i', '', $type);
            }
            $type         = preg_replace('@ZEROFILL@i', '', $type);
            $type         = preg_replace('@UNSIGNED@i', '', $type);
            if (empty($type)) {
                $type     = ' ';
            }

            if (!preg_match('@BINARY[\(]@i', $row['Type'])) {
                $binary           = stristr($row['Type'], 'blob') || stristr($row['Type'], 'binary');
            } else {
                $binary           = false;
            }

            $unsigned     = stristr($row['Type'], 'unsigned');
            $zerofill     = stristr($row['Type'], 'zerofill');
            $timestamp    = ("timestamp"==$row['Type'])?true:false;
        }

        $attribute     = ' ';
        if ($binary) {
            $attribute = 'BINARY';
        }
        if ($unsigned) {
            $attribute = 'UNSIGNED';
        }
        if ($zerofill) {
            $attribute = 'UNSIGNED ZEROFILL';
        }
        // MySQL 4.1.2+ TIMESTAMP options
        // (if on_update_current_timestamp is set, then it's TRUE)
        if ($timestamp) {
            $attribute = 'ON UPDATE CURRENT_TIMESTAMP';
        }

        if (""==trim($row['Default'])) {
            if ($row['Null'] == 'YES') {
                $row['Default'] = 'NULL';
            }
        }

        if ($type_and_length['type'] == 'bit') {
            $row['Default'] = DbHelper::PMA_printable_bit_value($row['Default'], $type_and_length['length']);
        }
        return array_merge($type_and_length,array("attribute"=>$attribute));
    }
    /**
     * Converts a bit value to printable format;
     * in MySQL a BIT field can be from 1 to 64 bits so we need this
     * function because in PHP, decbin() supports only 32 bits
     *
     * @uses    ceil()
     * @uses    decbin()
     * @uses    ord()
     * @uses    substr()
     * @uses    sprintf()
     * @param   numeric $value coming from a BIT field
     * @param   integer $length
     * @return  string  the printable value
     */
    public static function PMA_printable_bit_value($value, $length) {
        $printable = '';
        for ($i = 0; $i < ceil($length / 8); $i++) {
            $printable .= sprintf('%08d', decbin(ord(substr($value, $i, 1))));
        }
        $printable = substr($printable, -$length);
        return $printable;
    }
    /**
     * Extracts the true field type and length from a field type spec
     *
     * @uses    strpos()
     * @uses    chop()
     * @uses    substr()
     * @param   string $fieldspec
     * @return  array associative array containing the type and length
     */
    public static function PMA_extract_type_length($fieldspec) {
        $first_bracket_pos = strpos($fieldspec, '(');
        if ($first_bracket_pos) {
            $length = chop(substr($fieldspec, $first_bracket_pos + 1, (strpos($fieldspec, ')') - $first_bracket_pos - 1)));
            $type = chop(substr($fieldspec, 0, $first_bracket_pos));
        } else {
            $type = $fieldspec;
            $length = '';
        }
        return array(
            'type' => $type,
            'length' => $length
        );
    }

    // 通过数据库的项目信息，设置对应的配置信息，用于数据库操作
    public static function getConfigInfoByProjectData($p_arr) {
        $db_other_setting = [];
        if (isset($p_arr['other_setting']) && $p_arr['other_setting'] && json_decode($p_arr['other_setting'])) {
            $db_other_setting = json_decode($p_arr['other_setting']);
        }
        // 对需要的字段进行映射
        $db_connect_info = [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => '',
            'username' => 'root',
            'password' => '',
            'prefix' => isset($p_arr['db_prefix']) ? $p_arr['db_prefix']: '', // 以下参数均在项目表中可配置
            'charset' => isset($db_other_setting['charset']) ? $db_other_setting['charset'] : 'utf8',
            'collation' => isset($db_other_setting['collation']) ? $db_other_setting['collation'] : 'utf8_general_ci',
            'prefix_indexes' => isset($db_other_setting['prefix_indexes']) ? $db_other_setting['prefix_indexes'] : true,
            'strict' => isset($db_other_setting['strict']) ? $db_other_setting['strict'] : false,
            'engine' => isset($db_other_setting['engine']) ? $db_other_setting['engine'] : null,
            'timezone' => isset($db_other_setting['timezone']) ? $db_other_setting['timezone'] : env('DB_TIMEZONE', '+00:00'),

            /*'write' => [
                'host' => env('DB_MASTER_HOST', '127.0.0.1'),
                'port' => env('DB_MASTER_PORT', 3306),
                'username' => env('DB_MASTER_USERNAME', 'uipps'),
                'password' => env('DB_MASTER_PASSWORD', '')
            ],
            'read' => [
                [
                    'name' => 'readConn1',
                    'host' => env('DB_SLAVE_HOST', '127.0.0.1'),
                    'port' => env('DB_SLAVE_PORT', 3306),
                    'username' => env('DB_SLAVE_USERNAME', 'uipps'),
                    'password' => env('DB_SLAVE_PASSWORD', '')
                ]
            ],*/

        ];
        $connect_name = self::getConnectName($p_arr);

        $db_connect_info['host']     = $p_arr['db_host'];
        $db_connect_info['port']     = $p_arr['db_port'];
        $db_connect_info['database'] = isset($p_arr['db_name']) ? $p_arr['db_name'] : '';
        $db_connect_info['username'] = $p_arr['db_user'];
        $db_connect_info['password'] = $p_arr['db_pwd'];

        // 设置从库
        if (isset($p_arr['if_use_slave']) && 'T' == $p_arr['if_use_slave'] && $p_arr['slave_db_host']) {
            $db_connect_info['read']['name']     = 'readConn1';
            $db_connect_info['read']['host']     = $p_arr['slave_db_host'];
            $db_connect_info['read']['port']     = $p_arr['slave_db_port'];
            $db_connect_info['read']['username'] = $p_arr['slave_db_user'];
            $db_connect_info['read']['password'] = $p_arr['slave_db_pwd'];
            // 主库
            $db_connect_info['write']['host']     = $p_arr['db_host'];
            $db_connect_info['write']['port']     = $p_arr['db_port'];
            $db_connect_info['write']['username'] = $p_arr['db_user'];
            $db_connect_info['write']['password'] = $p_arr['db_pwd'];
        }
        \Config::set("database.connections.{$connect_name}", $db_connect_info);
        // $result = DB::connection($connect_name)->select('show tables');
        // print_r($result);exit;
        return 1;
    }

    public static function getDBTbls($p_arr, $assoc=true) {
        self::getConfigInfoByProjectData($p_arr);
        $sql = "show table status from " . cString_SQL::FormatField($p_arr['db_name']);
        $connect_name = self::getConnectName($p_arr);
        //$rlt = collect(DB::connection($connect_name)->select($sql))->toArray();
        $rlt = DB::connection($connect_name)->select($sql); // 返回数组, 无需toArray
        if ($rlt) {
            $rlt = cArray::ObjectToArray($rlt);
        }
        return $rlt;
    }
    // $p_arr 可以是数组也可以是连接名，为了兼容性处理
    public static function getTblFields($p_arr, $table_name, $FULL='FULL',$assoc=true) {
        if (!$table_name) return [];

        if (is_array($p_arr)) {
            self::getConfigInfoByProjectData($p_arr);
            $connect_name = self::getConnectName($p_arr);
        } else {
            $connect_name = $p_arr; // 数据库配置的连接名
        }
        $sql = "SHOW $FULL COLUMNS FROM ".cString_SQL::FormatField($table_name); // 或 show FULL fields from table 或 desc table
        $rlt = DB::connection($connect_name)->select($sql);
        if ($rlt) {
            $rlt = cArray::ObjectToArray($rlt);
        }
        return $rlt;
    }
    // 由于config::set的限制，必须保证返回的字符串中没有点符号'.'
    public static function getConnectName($p_arr, $with_dbname = false, $replace_dot = true) {
        //return $p_arr'db_name'] . '_m';
        if (!is_array($p_arr)) {
            throw new \Exception('Invalid array p_arr');
        }
        // dsn不需要携带db_name信息，因为可能并没有选择数据库
        if (!isset($p_arr["db_port"]) || '' == $p_arr["db_port"]) $p_arr["db_port"] = 3306; // 补充默认端口，统一格式
        if (array_key_exists("db_pwd", $p_arr)) {
            $dsn = "mysql://".$p_arr["db_user"].":".$p_arr["db_pwd"]."@".$p_arr["db_host"].":".$p_arr["db_port"]."/";
        } else if (array_key_exists("db_pass", $p_arr)) {
            $dsn = "mysql://".$p_arr["db_user"].":".$p_arr["db_pass"]."@".$p_arr["db_host"].":".$p_arr["db_port"]."/";
        } else {
            throw new \Exception('Invalid array p_arr');
        }
        if ($with_dbname) $dsn = $dsn . $p_arr['db_name'];

        if ($replace_dot)
            return str_replace('.', self::DOT_REPLACE_TO_STR, $dsn); // 保证没有.符号, 防止config.set的时候出现问题
        return $dsn;
    }
}
