<?php
//namespace app\Libs\Classs;

if (!defined('NEW_LINE_CHAR')) define('NEW_LINE_CHAR', "\r\n");

//class DBR extends MysqlR
class DBR
{
    private static $db_obj_list;

    public static function getDBR($dsn=null) {
        // 将MysqlW对象返回，并且放到树上
        if (!$dsn) {
            // 默认的数据库连接
            $alias = '';
        } else if (is_array($dsn) && isset($dsn['db_host'])) {
            // 拼装对应的key，作为树的key
            $alias = DbHelper::getConnectName($dsn);
        } else {
            throw new \Exception('Invalid array p_arr:' . __FILE__ . ' ' . __LINE__);
        }
        if (isset(self::$db_obj_list[$alias]))
            return self::$db_obj_list[$alias];
        self::$db_obj_list[$alias] = new MysqlR($dsn);
        return self::$db_obj_list[$alias];
    }
}
