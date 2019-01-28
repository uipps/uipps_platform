<?php

namespace App\Dto;

class DataListDto extends BaseDto
{
    public $count = 0; // 总条数
    //public $next = 0;  // 是否有下一页，当总数很多很难统计的时候，此字段表示是否有下一页
    public $list = []; // 具体数据
}
