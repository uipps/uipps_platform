<?php

namespace App\Libs\Utils;

class ErrorMsg {
    // 定义错误信息
    const PARAM_ERROR = 2;
    const UN_LOGIN = 3;
    const INSERT_DB_FAILED = 5;
    const UPDATE_DB_FAILED = 6;
    const DATA_NOT_EXISTS = 15;
    const IP_NOT_ALLOWED = 20;
    const UNKNOWN_ERROR = 30;
    const DATA_EMPTY = 35;
    const PARAM_FORMAT_ERROR = 40;
    const REQUEST_DATA_INCOMPLETE = 45;

    const USER_NOT_EXISTS = 70;
    const GOOGLE_AUTHENTICATOR_ERROR = 72;
    const PASSWORD_ERROR = 75;
    const USER_STATUS_ERROR = 77;

    static $error_msg_array = array(
        self::PARAM_ERROR       => '参数错误',
        self::UN_LOGIN          => '未登录',
        self::INSERT_DB_FAILED  => '入库操作失败',
        self::UPDATE_DB_FAILED  => '入库操作失败',
        self::DATA_NOT_EXISTS  => '没有数据',
        self::IP_NOT_ALLOWED  => 'IP受限',
        self::UNKNOWN_ERROR  => '未知错误',
        self::DATA_EMPTY  => '数据为空',
        self::PARAM_FORMAT_ERROR  => '请求参数格式错误',
        self::REQUEST_DATA_INCOMPLETE  => '数据不全',

        self::USER_NOT_EXISTS  => '用户不存在',
        self::GOOGLE_AUTHENTICATOR_ERROR  => 'Google验证码错误',
        self::PASSWORD_ERROR  => '密码错误',
        self::USER_STATUS_ERROR  => '用户状态异常',
    );

    private function __construct() {}

    public static function GetErrorMsg() {
        $argc = func_num_args();
        if (0 == $argc)
            return NULL;
        $argv = func_get_args();
        if (array_key_exists($argv[0], self::$error_msg_array)) {
            if (1 == $argc)
                return self::$error_msg_array[$argv[0]];
            $argv[0] = self::$error_msg_array[$argv[0]];
            return call_user_func_array('sprintf', $argv);
        } //else
        //Log::alert('Invalid errno:' . $argv[0]);
        return NULL;
    }

    /**
     * 填充response数组并记录日志
     *
     * @param obj $response
     * @param string $status
     * @param array $other_info
     */
    public static function FillResponseAndLog(&$response, $status, $other_info=array()) {
        $response->ret = $status;

        // 如果有超过两个或更多的参数则都传递给如下方法
        array_unshift($other_info, $status); // 在数组插入一个单元
        $response->msg = call_user_func_array('App\Libs\Utils\ErrorMsg::GetErrorMsg', $other_info);

        $call_info = debug_backtrace();
        $log_title = isset($call_info[1]) ? $call_info[0]['file'] . ' ' .
            $call_info[0]['line'] . ': ' . $call_info[1]['function'] .
            ' failed:' :
            $call_info[0]['file'] . ' ' . $call_info[0]['line'] . ': ';
        //Log::alert($log_title . $response->msg, false);
    }
}
