<?php

namespace App\Services\Delivery;

use App\Dto\DeliveryDto;
use App\Repositories\Delivery\DeliveryRepository;
use App\Services\BaseService;

class DeliveryServiceImpl extends BaseService
{
    protected $deliveryRepository;

    public function __construct(
        DeliveryRepository $deliveryRepository
    ) {
        $this->deliveryRepository = $deliveryRepository;
    }

    public function getDeliveryInfoById($params) {
        // 获取配送单数据
        $data_arr = $this->deliveryRepository->getDeliveryInfoById($params['id']);
        if (!$data_arr)
            return $data_arr;

        $rlt = new DeliveryDto();
        $rlt->Assign($data_arr);
        return $rlt;
    }
}
