<?php

namespace App\Logics\Delivery;

use App\Logics\BaseLogic;
use App\Services\Delivery\DeliveryService;


class DeliveryLogic extends BaseLogic
{
    protected $deliveryService;

    public function __construct(
        DeliveryService $deliveryService
    ) {
        $this->deliveryService = $deliveryService;
    }

    public function getDeliveryInfoById($dispatch_id) {
        return $this->deliveryService->getDeliveryInfoById($dispatch_id);
    }
}
