<?php

class MysqlDB
{
    public $dbo = null;
    public $sql = null;
    public $schema = null;
    public $assoc = false;
    public $isconnectionDB = false;  // 仅仅用于标识是否连接上
    public $connectError = array();// 连接的错误信息

    public function ConnectDB($dsn=array(), $options=false){
        if (!$dsn) {
            $this->dbo = \DB::connection();
            return ;
        }
        if (is_array($dsn) && isset($dsn['db_host'])) {
            DbHelper::getConfigInfoByProjectData($dsn);
            $connect_name = DbHelper::getConnectName($dsn);
            $this->dbo = \DB::connection($connect_name);
            return ;
        }
        throw new \Exception('Invalid array p_arr:' . __FILE__ . ' ' . __LINE__);
        /*$l_name_dsn = DbHelper::FmtDSNAndGetMdb2NameAlias($dsn,'R');

        $this->dbo = &DBO($l_name_dsn['l_name'], $l_name_dsn['dsn'], $options);  // 同时注册到全局变量中去
        $this->dbo->getConnection();  // 进行连接操作

        // 立即检查是否连接上.因为mysql_errno仅返回最近一次 MySQL 函数的执行(不包括mysql_errno自身)
        if ($this->isConnection()) {
            $this->setCharset();      // 理解设置字符编码
            $this->isconnectionDB = true;
            $l_srv_db_dsn = $this->getDSN("array");
            // 由于数据库切换的时候，mdb2对于同一个主机、端口、用户的连接认为是一个连接，但是数据库却不会自动切换，需要执行use db语句
            if (!empty($l_srv_db_dsn["database"])) {
                $this->SetCurrentSchema($l_srv_db_dsn["database"]);
            }
            //$this->schema = $this->GetCurrentSchema();//如果dsn中有数据库则初始化一下
        }*/

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
        return [0,0];
        //$dbo =& $this->dbo;
        //return $dbo->errorInfo($error);
    }
    /**
     * 设置当前的 schema
     * @access public
     * @param string  $schema
     * @return boolean
     */
    public function SetCurrentSchema($schema ){
        //$dbo =& $this->dbo;
        $l_rlt = $this->dbo->query("use ".$schema);
        $this->dbo->setDatabase($schema);
        $this->schema = $this->GetCurrentSchema(); // 顺便切换一下数据库
        return $l_rlt;
    }
    /**
     * 取得当前的 dsn
     * @access  $type string  format to return ("array", "string")
     * @access  $hidepw bool
     * @return string|array
     */
//    public function getDSN($type = 'array', $hidepw = false){
//        //$dbo =& $this->dbo;
//        return $this->dbo->getDSN($type, $hidepw);
//    }

    /**
     * 取得当前的 schema
     * @access public
     * @return string
     */
    public function GetCurrentSchema(){
        return $this->dbo->getDatabaseName();
    }

    // --------------- 很少使用的 public method ----------------- //
    /**
     * 取到最后一次操作所影响的行数
     * @access public
     * @return integer
     */
    public function CountAffectedRows(){
        return $this->dbo->affectingStatement();
        //$dbo =& $this->dbo;
        //return $dbo->_affectedRows($dbo->getConnection());
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
     * 获取sql语句
     *
     * @return string or bool
     */
    public function getSQL(){
        return $this->sql;
    }

    public function setCharset($charset = null){
        return 1;
        /*$dbo =& $this->dbo;
        if ("" == $charset){
            // 空的时候
            if ("utf8"==strtolower($GLOBALS['cfg']['db_character'])) $dbo->setCharset("utf8", $dbo->getConnection());//mysql_query("set names utf8;");// 数据库字符编码转换问题
            else if("gb2312"==strtolower($GLOBALS['cfg']['db_character'])) $dbo->setCharset("gbk", $dbo->getConnection());//mysql_query("set names gbk;");
        }else {
            // 指定的时候
            if (in_array($charset, array("utf8","gbk","latin1"))) $dbo->setCharset($charset, $dbo->getConnection());
            else $dbo->setCharset("latin1", $dbo->getConnection());
        }*/
    }

    /**
     * 执行一条读取查询命令
     * @access private
     * @param string $sql sql
     * @return resource|boolean
     */
    public function Query($sql){
        //$dbo =& $this->dbo;  // 兼容php4的做法
        //echo "\r\n---- " . __FUNCTION__ . " ----"."\r\n";
        // 事先判断 查询语句是否是 select ，不是就返回false
        $sql = ltrim($sql);$this->sql = $sql;
        /*$prex = strtolower(substr($sql,0,4));
        if ($prex==="sele" || $prex==="desc" || $prex==="show"){
            $this->setCharset();
            return $dbo->query($sql);
        }
        else return false; // 非select操作的被禁止
        */
        return collect($this->dbo->select($sql))->toArray();
    }

    public function Disconnect(&$dbo){
        $dbo->disconnect();
    }
    /*public function __destruct(){
        $this->Disconnect($this->dbo);
    }*/

    public function getDsn(array $config = [])
    {
        //$app = new Application();
        $app = Illuminate\Container\Container::getInstance();
        //$config = $app->make('config')->get('app');
        //$config = $app->make('config')->get('database.default');
        if (!$config) {
            $default = $app['config']['database.default'];
            $connections = $app['config']['database.connections'];
            $config = $connections[$default];
        }
        return $this->hasSocket($config)
            ? $this->getSocketDsn($config)
            : $this->getHostDsn($config);
    }
    protected function hasSocket(array $config)
    {
        return isset($config['unix_socket']) && ! empty($config['unix_socket']);
    }
    protected function getSocketDsn(array $config)
    {
        return "mysql:unix_socket={$config['unix_socket']};dbname={$config['database']}";
    }
    protected function getHostDsn(array $config)
    {
        extract($config, EXTR_SKIP);

        // 检查是否有主从分离配置
        if (isset($config['write']))
            extract($config['write'], EXTR_SKIP);

        // 兼容数据库中的设置db_host, db_sock, db_name, db_port
        if (isset($db_host) && isset($db_name) && isset($db_port))
            return (3306 == $db_port || '' == $db_port)
                ? "mysql:host={$db_host};dbname={$db_name}"
                : "mysql:host={$db_host};port={$db_port};dbname={$db_name}";

        if (isset($host) && $host && isset($database) && $database) {
            if (!isset($port) || '' == $port || 3306 == $port)
                return "mysql:host={$host};dbname={$database}";
            else
                return  "mysql:host={$host};port={$port};dbname={$database}";
        }

        if (isset($db_sock) && trim($db_sock))
            return "mysql:unix_socket={$db_sock};dbname={$db_name}";
        return ''; // 这种情况应该不存在
    }

    public function __call($method, $parameters)
    {
        //return $this->dbo->$method($parameters); // 报错“Array to string conversion”
        return call_user_func_array([$this->dbo, $method], $parameters);
    }
}
