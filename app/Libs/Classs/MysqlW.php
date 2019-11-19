<?php

class MysqlW extends MysqlDB
{
    public function __construct($_arr=null, $options=false){
        $this->ConnectDB($_arr, $options);
    }

    public function InsertIntoTbl($tablename, $ar){
        if (!is_array($ar)||empty($ar)) {  // 确保$ar为非空数组
            return false;
        }

        $i=0;
        $ziduan = "";
        $vals = "";
        foreach ($ar as $key => $val){
            if ($i > 0){
                $ziduan .= ",";
                $vals .= ","; // 至少第一项以后才能有逗号
            }
            $ziduan .= cString_SQL::FormatField(trim($key));
            $vals   .= cString_SQL::FormatValue(trim($val), 'string');
            $i++;
        }
        $sql = "insert into {$tablename} ( $ziduan ) values ( $vals )";$this->sql = $sql;
        global $SHOW_SQL;
        if ("all"==$SHOW_SQL||false!==strpos($SHOW_SQL,"201")) echo $sql.NEW_LINE_CHAR;
        $affected = $this->dbo->insert($sql); // 成功返回true
        //$affected = $this->dbo->insertGetId($sql); // 报错，Call to undefined method Illuminate\Database\MySqlConnection::insertGetId()
        if ($affected)
            return $this->dbo->getPdo()->lastInsertId(); // 返回insertID
        return $affected;
    }
    /**
     * 取得上一步 INSERT 操作产生的 ID
     * @access public
     * @return integer
     */
    public function LastID(){
        $dbo =& $this->dbo;
        return $dbo->getPdo()->lastInsertId();
    }
    /**
     * 彻底删除记录，谨慎操作
     *
     * @param string $tablename
     * @param string $condition
     * @param string $limit
     * @return resource|boolean
     */
    public function DeleteData($tablename, $condition, $limit = ""){
        $sql = "delete from {$tablename} $condition $limit ";$this->sql = $sql;
        global $SHOW_SQL;
        if ("all"==$SHOW_SQL||false!==strpos($SHOW_SQL,"202")) echo $sql.NEW_LINE_CHAR;
        //$affected =& $this->exec($sql);
        $affected = $this->dbo->statement($sql);
        return $affected;
    }
    /**
     * 更新命令
     * 也可用来--增加计数（也可以用来减，+负数就是了）
     *
     * @param string $tablename
     * @param string $condition
     * @param array $ar
     * @param boolean $bAdd
     * @return boolean
     */
    public function UpdateTableArray($tablename, $ar, $condition, $if_addcount = false){
        if (!is_array($ar)||empty($ar)) {  // 确保$ar为非空数组
            return false;
        }
        $sql = "update {$tablename} set ";
        $sql .= cString_SQL::FmtFieldValArr2Str($ar, ",", $if_addcount);
        $sql .= " where {$condition}";$this->sql = $sql;
        global $SHOW_SQL;
        if ("all"==$SHOW_SQL||false!==strpos($SHOW_SQL,"203")) echo $sql.NEW_LINE_CHAR;
        //$affected =& $this->exec($sql);
        $affected = $this->dbo->statement($sql);
        return $affected;
    }

    /**
     * 执行一条查询命令,只能用于update操作
     * @access private
     * @param string $sql sql
     * @return resource|boolean
     */
    public function exec($sql){
        // 事先判断 查询语句是否是 update ，不是就返回false
        //$dbo =& $this->dbo;
        //$this->setCharset();// 兼容php4的做法
        $sql = ltrim($sql);
        $this->sql = $sql;
        //$affected = $this->dbo->insert($sql);
        $affected = $this->dbo->statement($sql);
        return $affected;
    }

    /**
     * 用于注册，为保证用户唯一性，验证用户是否存在需要在主库进行，而不是去从库查询信息
     *
     * @param string $sql
     * @return unknown
     */
    public function Query_master_select($sql){
        return $this->Query($sql);
    }



    /////////////////// copy from DBW.php ////
    public function getExistorNot($condition, $assoc=true){
        $sql = "select * from ".cString_SQL::FormatField($this->table_name)." where $condition  limit 1";
        $this->assoc = $assoc;
        $rows = $this->Query_master_select($sql);
        if (!$rows || !isset($rows[0]))
            return [];
        return cArray::ObjectToArray($rows[0]);
    }

    /**
     * 修改表结构, 比较复杂，可以参考phpmyadmin的tbl_addfield.php文件的做法稍后完善????
     *
     * @param array $ar    一维数组，兼容自动抓取，只需要提供多出的字段即可
     * @param array $a_data 至少是一个二维数组，每个字段的详细信息，包括类型、长度、默认值、是否为空等信息
     * @return result
     */
    public function alter_table($ar, $a_data=array(), $a_act='ADD'){
        // ALTER TABLE `vendor` ADD `peizhi_m` VARCHAR( 255 ) NULL ;
        // ALTER TABLE `vendor` ADD `peizhi_1` VARCHAR( 255 ) NULL , ADD `peizhi_2` VARCHAR( 255 ) NULL , ADD `peizhi_3` VARCHAR( 255 ) NULL ;
        // ALTER TABLE `vendor` CHANGE `f1` `f1` ENUM( '', 'INT', 'OUT' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

        $sql = " ALTER TABLE  ". cString_SQL::FormatField($this->table_name);

        $i=0;
        foreach ($ar as $l_k=>$l_val){
            if ($i > 0) $sql .= " , "; // 至少第一项以后才能有逗号
            if (is_numeric($l_k)) {
                $val = $l_val;    // 字段英文名
                $l_comment = "";
            }else {
                // 通常 $ar = array('field_1','field_2') 数字索引; 也有可能是 字段英文名=>字段中文名
                $val = $l_k;
                $l_comment = " COMMENT '$l_val'";
            }

            if (is_array($a_data) && array_key_exists($val, $a_data)) {
                // 当有字段的额外信息时
                if (is_array($a_data[$val])) {
                    $field_primary = "";  // 地址调用，必须先申明一个，具体有啥用处以后完善之????
                    $l_arr = $a_data[$val];
                    // 也可能是修改字段，则需要使用到之前的字段英文名
                    $l_change = array_key_exists("name_eng_old", $l_arr)? cString_SQL::FormatField($l_arr["name_eng_old"]) . ' ' : "" ;
                    $sql .= " $a_act " . $l_change . PMA_Table::generateFieldSpec($l_arr["name_eng"], $l_arr["type"], $l_arr["length"], $l_arr["attribute"], isset($l_arr["collation"]) ? $l_arr["collation"] : '', ("YES"==$l_arr["is_null"])?false:true, $l_arr["default"], isset($l_arr["default_current_timestamp"]), $l_arr["extra"], $l_arr["description"], $field_primary, isset($l_arr["index"]) ? $l_arr["index"] : "id", isset($l_arr["default_orig"]) ? $l_arr["default_orig"] : false);
                }
            }else {
                $sql .= " $a_act ".cString_SQL::FormatField($val)." VARCHAR( 255 ) NULL " . $l_comment;
            }
            $i++;
        }

        return $this->exec($sql);
    }

    public function create_db($db_name, $db_charset="utf8"){
        $sql = 'CREATE DATABASE IF NOT EXISTS '.cString_SQL::FormatField($db_name).' DEFAULT CHARACTER SET '.$db_charset.' COLLATE '.$db_charset.'_general_ci';
        return $this->exec($sql);
    }

    public function create_table($a_name="tbl_001",$sql_query="`id` int(11) unsigned NOT NULL auto_increment COMMENT '自增ID',`creator` varchar(100) NOT NULL default '0' COMMENT '创建者',`createdate` date NOT NULL default '0000-00-00' COMMENT '创建日期',`createtime` time NOT NULL default '00:00:00' COMMENT '创建时间',`mender` varchar(100) default NULL COMMENT '修改者',`menddate` date default NULL COMMENT '修改日期',`mendtime` time default NULL COMMENT '修改时间',`updated_at` timestamp NOT NULL COMMENT '最近修改时间', PRIMARY KEY  (`id`)",$MySQL_ENGINE="MyISAM",$MySQL_CHARSET="utf8"){
        $MySQL_CHARSET = $GLOBALS['cfg']['db_character'];
        $sql = " CREATE TABLE  ". cString_SQL::FormatField($a_name) . ' (' . $sql_query . ')'."ENGINE=".$MySQL_ENGINE." DEFAULT CHARSET=".$MySQL_CHARSET;
        return $this->exec($sql);
    }

    public function rename_table($a_old_name,$a_new_name){
        $sql = "RENAME TABLE ". cString_SQL::FormatField($a_old_name)." TO ". cString_SQL::FormatField($a_new_name);
        return $this->exec($sql);
    }

    public function insertOne($data_arr){
        return $this->InsertIntoTbl($this->table_name,$data_arr);
    }

    public function updateOne($ar, $condition, $if_addcount = false){
        return $this->UpdateTableArray($this->table_name, $ar, $condition, $if_addcount);
    }

    public function delOne($arr,$id_ziduan="d_id"){
        $condition = " where ";
        if (!empty($arr)) {
            foreach ($arr as $key => $val){
                // uid是否也需要引号
                if ($id_ziduan==$key) {
                    $condition .= $key."=".$val." and "; // id 不需要单引号
                } else {
                    $condition .= $key."='".$val."' and ";
                }
            }
            if ("and"==substr(rtrim($condition),-3)) { // 截取后三个字符
                $condition = substr(rtrim($condition),0,-3);
            }
        }

        return $this->DeleteData($this->table_name, $condition);
    }
}
