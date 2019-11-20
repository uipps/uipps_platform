<?php

// 用注册树模式改造一下，鉴于很多地方都是 new DBW的方式，因此可以保留，但是返回的依然是db对象
class DBW
{
    private static $db_obj_list;

    public static function getDBW($dsn=null) {
        // 将MysqlW对象返回，并且放到树上
        if (!$dsn) {
            // 默认的数据库连接
            $alias = '';
        } else if (is_array($dsn) && isset($dsn['db_host'])) {
            // 拼装对应的key，作为树的key
            $alias = DbHelper::getConnectName($dsn,false);
        } else {
            throw new \Exception('Invalid array p_arr:' . __FILE__ . ' ' . __LINE__);
        }
        if (isset(self::$db_obj_list[$alias]))
            return self::$db_obj_list[$alias];
        self::$db_obj_list[$alias] = new MysqlW($dsn);
        return self::$db_obj_list[$alias];
    }
}
