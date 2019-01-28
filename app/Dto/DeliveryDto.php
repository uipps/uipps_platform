<?php

namespace App\Dto;

class DeliveryDto extends BaseDto
{
    public function Assign($item) {
        parent::Assign($item);
        if (isset($item['updated_at']) && !is_numeric($item['updated_at']))
            $this->updated_at = strtotime($item['updated_at']);
    }
    public $id = 0;
    public $created_at = 0; // 创建时间
    public $updated_at = 0; // 更新时间
}
