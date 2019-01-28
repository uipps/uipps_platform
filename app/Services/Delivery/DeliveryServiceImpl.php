<?php

namespace App\Services\Delivery;

use App\Dto\DeliveryDto;
use App\Dto\ResponseDto;
use App\Repositories\Delivery\DeliveryRepository;
use App\Services\BaseService;
use App\Libs\Utils\ErrorMsg;

class DeliveryServiceImpl extends BaseService
{
    protected $deliveryRepository;

    public function __construct(
        DeliveryRepository $deliveryRepository
    ) {
        $this->deliveryRepository = $deliveryRepository;
    }

    public function getDeliveryInfoById($params) {
        $response = new ResponseDto();
        if (!isset($params['id']) || $params['id'] <= 0) {
            ErrorMsg::FillResponseAndLog($response, ErrorMsg::PARAM_ERROR);
            return $response;
        }

        // 获取配送单数据
        $data_arr = $this->deliveryRepository->getDeliveryInfoById($params['id']);
        if (!$data_arr)
            return $response;

        $rlt = new DeliveryDto();
        $rlt->Assign($data_arr);
        $response->data = $rlt;

        return $response;
    }
}
