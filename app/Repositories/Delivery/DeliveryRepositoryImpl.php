<?php

namespace App\Repositories\Delivery;

use App\Models\Delivery\Delivery;
use App\Repositories\BaseRepository;


class DeliveryRepositoryImpl extends BaseRepository
{
    public function getDeliveryInfoById($dispatch_id) {
        $sql_where = [
            'id' => $dispatch_id,
            //'status' => Delivery::STATUS_TO_DELIVERY // 待配送状态
        ];
        $db_result = Delivery::where($sql_where)->first();
        return $db_result;
    }
}