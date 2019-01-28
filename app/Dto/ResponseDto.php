<?php

namespace App\Dto;

class ResponseDto extends BaseDto
{
    public $code = 1000;       // 1000表示成功，非1000表示失败
    public $info = "SUCCESS";  // 失败原因描述
    public $data = [];         // 存放数据
}
