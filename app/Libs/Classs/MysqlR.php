<?php

class MysqlR extends MysqlDB
{
    public function __construct($_arr=null, $options=false){
        $this->ConnectDB($_arr, $options);
    }

    public function GetOne($sql){
        global $SHOW_SQL;
        if ("all"==$SHOW_SQL||false!==strpos($SHOW_SQL,"101")) echo $sql.NEW_LINE_CHAR;
        $this->sql = $sql;

        /*$dbo =& $this->dbo;$this->setCharset();
        if($this->assoc) {
            $dbo->setOption('portability',MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES);
            $dbo->setFetchMode(MDB2_FETCHMODE_ASSOC);
        }
        else $dbo->setFetchMode(MDB2_FETCHMODE_DEFAULT);
        $row = $dbo->queryOne($sql);*/
        $row = collect($this->dbo->select($sql))->toArray();
        if ($row && isset($row[0])) {
            return cArray::ObjectToArray($row[0]);
        }
        return [];
    }
    /**
     * 取一行(一维数组)
     * @access public
     * @param string $sql example : select field_a from table_a Limit 1
     * @return array| false
     */
    public function GetRow($sql){
        global $SHOW_SQL;
        if ("all"==$SHOW_SQL||false!==strpos($SHOW_SQL,"102")) echo $sql.NEW_LINE_CHAR;
        $this->sql = $sql;
        $row = collect($this->dbo->select($sql))->toArray();//print_r($row);
        //$row = collect($this->dbo->statement($sql))->toArray();print_r($row);
        if ($row && isset($row[0])) {
            return cArray::ObjectToArray($row[0]);
        }
        return [];
    }
    /**
     * 取一列(一维数组)
     * @access public
     * @param string $sql sql语句
     * @return array
     */
    public function GetCol($sql){
        global $SHOW_SQL;
        if ("all"==$SHOW_SQL||false!==strpos($SHOW_SQL,"103")) echo $sql.NEW_LINE_CHAR;
        $this->sql = $sql;

        /*$dbo =& $this->dbo;$this->setCharset();
        if($this->assoc) {
            $dbo->setOption('portability',MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES);
            $dbo->setFetchMode(MDB2_FETCHMODE_ASSOC);
        }
        else $dbo->setFetchMode(MDB2_FETCHMODE_DEFAULT);
        $data = $dbo->queryCol($sql);*/
        $row = collect($this->dbo->select($sql))->toArray();//print_r($row);
        if ($row && isset($row[0])) {
            return cArray::ObjectToArray($row[0]);
        }
        return [];
    }
    /**
     * 取多行(二维数组)
     * @access public
     * @param string $sql
     * @return array
     */
    public function GetPlan($sql){
        global $SHOW_SQL;
        if ("all"==$SHOW_SQL||false!==strpos($SHOW_SQL,"104")) echo $sql.NEW_LINE_CHAR;
        $this->sql = $sql;

        /*$dbo =& $this->dbo;
        $this->setCharset();
        if($this->assoc) {
            $dbo->setOption('portability',MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES);
            $dbo->setFetchMode(MDB2_FETCHMODE_ASSOC);
        }
        else $dbo->setFetchMode(MDB2_FETCHMODE_DEFAULT);
        $data = $dbo->queryAll($sql);*/
        $row = collect($this->dbo->select($sql))->toArray();
        //$row = collect($this->dbo->statement($sql))->toArray();
        $data = cArray::ObjectToArray($row);
        return $data;
    }
    // --------------- 很少使用的 public method ----------------- //
    /**
     * 取得结果集的行数
     * @access public
     * @param resource result set
     */
    public function CountResultRows(&$rs){
        return $rs->affectingStatement();
        //return $rs->numRows();
    }


    /**
     * 取一行
     * @access private
     * @param resource $rs 结果集
     * @return array
     */
    public function fa(&$rs){
        if(!$this->assoc)
        {
            return $rs->fetchRow();
        }
        else
        {
            return $rs->fetchRow(MDB2_FETCHMODE_ASSOC);
        }
    }
}
