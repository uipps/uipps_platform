<?php

class MysqlR
{
    protected $dbo = null;
    protected $sql = null;
    protected $schema = null;
    protected $isconnectionR = false;  // 仅仅用于标识是否连接上
    protected $connectError = array();// 连接的错误信息
    protected $assoc = false;

    public function __construct($_arr=null, $options=false){
        $this->ConnectR($_arr, $options);
    }

    /**
     * 从库连接
     *
     * @return resource
     */
    public function ConnectR($dsn=array(), $options=false){
        $l_name_dsn = DbHelper::FmtDSNAndGetMdb2NameAlias($dsn,'R');

        $this->dbo = &DBO($l_name_dsn['l_name'], $l_name_dsn['dsn'], $options);  // 同时注册到全局变量中去
        $this->dbo->getConnection();  // 进行连接操作

        // 立即检查是否连接上.因为mysql_errno仅返回最近一次 MySQL 函数的执行(不包括mysql_errno自身)
        if ($this->isConnection()) {
            $this->setCharset();      // 理解设置字符编码
            $this->isconnectionR = true;
            $l_srv_db_dsn = $this->getDSN("array");
            // 由于数据库切换的时候，mdb2对于同一个主机、端口、用户的连接认为是一个连接，但是数据库却不会自动切换，需要执行use db语句
            if (!empty($l_srv_db_dsn["database"])) {
                $this->SetCurrentSchema($l_srv_db_dsn["database"]);
            }
            //$this->schema = $this->GetCurrentSchema();//如果dsn中有数据库则初始化一下
        }

        //$this->connectError = $this->errorInfo();
    }

    // 如何判断是否连接上的方法, var_dump( $dbo->connection )有两种值int(0) 或 resource(60) of type (mysql link)
    public function isConnection(){
        $dbo =& $this->dbo;
        if( $dbo->connection ){
            return true;
        } else {
            return false;
        }
    }
    public function errorInfo($error = null){
        $dbo =& $this->dbo;
        return $dbo->errorInfo($error);
    }
    /**
     * 设置当前的 schema
     * @access public
     * @param string  $schema
     * @return boolean
     */
    public function SetCurrentSchema($schema ){
        $dbo =& $this->dbo;
        $l_rlt = $dbo->query("use ".$schema);
        $dbo->setDatabase($schema);
        $this->schema = $this->GetCurrentSchema(); // 顺便切换一下数据库
        return $l_rlt;
    }
    /**
     * 取得当前的 dsn
     * @access  $type string  format to return ("array", "string")
     * @access  $hidepw bool
     * @return string|array
     */
    public function getDSN($type = 'array', $hidepw = false){
        $dbo =& $this->dbo;
        return $dbo->getDSN($type, $hidepw);
    }

    /**
     * 取得当前的 schema
     * @access public
     * @return string
     */
    public function GetCurrentSchema(){
        $dbo =& $this->dbo;
        return $dbo->getDatabase();
    }
    /**
     * 取一个值
     * @access public
     * @param string $sql example : select field_a from table_a Limit 1
     * @return mixed
     */
    public function GetOne($sql){
        global $SHOW_SQL;
        if ("all"==$SHOW_SQL||false!==strpos($SHOW_SQL,"101")) echo $sql.NEW_LINE_CHAR;
        $this->sql = $sql;

        $dbo =& $this->dbo;$this->setCharset();
        if($this->assoc) {
            $dbo->setOption('portability',MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES);
            $dbo->setFetchMode(MDB2_FETCHMODE_ASSOC);
        }
        else $dbo->setFetchMode(MDB2_FETCHMODE_DEFAULT);
        $row = $dbo->queryOne($sql);

        return $row;
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

        $dbo =& $this->dbo;$this->setCharset();
        if($this->assoc) {
            $dbo->setOption('portability',MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES);
            $dbo->setFetchMode(MDB2_FETCHMODE_ASSOC);
        }
        else $dbo->setFetchMode(MDB2_FETCHMODE_DEFAULT);
        $row = $dbo->queryRow($sql);

        return $row;
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

        $dbo =& $this->dbo;$this->setCharset();
        if($this->assoc) {
            $dbo->setOption('portability',MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES);
            $dbo->setFetchMode(MDB2_FETCHMODE_ASSOC);
        }
        else $dbo->setFetchMode(MDB2_FETCHMODE_DEFAULT);
        $data = $dbo->queryCol($sql);

        return $data;
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

        $dbo =& $this->dbo;$this->setCharset();
        if($this->assoc) {
            $dbo->setOption('portability',MDB2_PORTABILITY_FIX_ASSOC_FIELD_NAMES);
            $dbo->setFetchMode(MDB2_FETCHMODE_ASSOC);
        }
        else $dbo->setFetchMode(MDB2_FETCHMODE_DEFAULT);
        $data = $dbo->queryAll($sql);

        return $data;
    }
    // --------------- 很少使用的 public method ----------------- //
    /**
     * 取得结果集的行数
     * @access public
     * @param resource result set
     */
    public function CountResultRows(&$rs){
        return $rs->numRows();
    }
    /**
     * 取到最后一次操作所影响的行数
     * @access public
     * @return integer
     */
    public function CountAffectedRows(){
        $dbo =& $this->dbo;
        return $dbo->_affectedRows($dbo->getConnection());
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
    /**
     * 执行一条读取查询命令
     * @access private
     * @param string $sql sql
     * @return resource|boolean
     */
    public function &Query($sql){
        $dbo =& $this->dbo;  // 兼容php4的做法
        //echo "\r\n---- " . __FUNCTION__ . " ----"."\r\n";
        // 事先判断 查询语句是否是 select ，不是就返回false
        $sql = ltrim($sql);$this->sql = $sql;
        $prex = strtolower(substr($sql,0,4));
        if ($prex==="sele" || $prex==="desc" || $prex==="show"){
            $this->setCharset();
            return $dbo->query($sql);
        }
        else return false; // 非select操作的被禁止
    }

    /**
     * 获取sql语句
     *
     * @return string or bool
     */
    public function getSQL(){
        return $this->sql;
    }

    public function setCharset($charset = null){
        $dbo =& $this->dbo;
        if ("" == $charset){
            // 空的时候
            if ("utf8"==strtolower($GLOBALS['cfg']['db_character'])) $dbo->setCharset("utf8", $dbo->getConnection());//mysql_query("set names utf8;");// 数据库字符编码转换问题
            else if("gb2312"==strtolower($GLOBALS['cfg']['db_character'])) $dbo->setCharset("gbk", $dbo->getConnection());//mysql_query("set names gbk;");
        }else {
            // 指定的时候
            if (in_array($charset, array("utf8","gbk","latin1"))) $dbo->setCharset($charset, $dbo->getConnection());
            else $dbo->setCharset("latin1", $dbo->getConnection());
        }
    }

    public function Disconnect(&$dbo){
        $dbo->disconnect();
    }
    public function __destruct(){
        $this->Disconnect($this->dbo);
    }
}
