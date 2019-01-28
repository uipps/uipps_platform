<?php

namespace App\Services;


abstract class  BaseService
{
    protected $_lastError;

    /**
     * @param  int   $code
     * @param string $message
     * @param array  $data
     *
     * @return array
     */
    protected function returnFormat($code, $message = '', $data = [])
    {
        return ['code' => intval($code), 'info' => $message, 'data' => $data];
    }

    /*
     * 设置错误信息
     * @return array
     */
    public function setLastError($code, $message = '')
    {
        $this->_lastError = ['code' => $code, 'info' => $message];
    }

    /*
     * 获取错误信息
     * @return array
     */
    public function getLastError()
    {
        return $this->_lastError ? $this->_lastError : ['code' => 0, 'info' => ''];
    }
}
