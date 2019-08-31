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

}
