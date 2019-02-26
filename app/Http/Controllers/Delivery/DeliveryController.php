<?php

namespace App\Http\Controllers\Delivery;

use App\Dto\ResponseDto;
use App\Http\Controllers\Controller;
use App\Logics\Delivery\DeliveryLogic;
use Illuminate\Http\Request;
use App\Services\Delivery\DeliveryService;

class DeliveryController extends Controller
{
    protected $deliveryLogic;
    protected $deliveryService;

    public function __construct(
        DeliveryLogic $deliveryLogic,
        DeliveryService $deliveryService
    ) {
        $this->deliveryLogic = $deliveryLogic;
        $this->deliveryService = $deliveryService;
    }

    // 获取配送单信息
    public function getOrderDeliveryInfo(Request $a_request)
    {
        $response = new ResponseDto();
        $params = $a_request->all();
        $data = $this->deliveryLogic->getDeliveryInfoById($params);  // 没有特别复杂的逻辑，可以不用Logic层
        #$data = $this->deliveryService->getDeliveryInfoById($params);
        if (is_object($data) && property_exists($data, 'code')) {
            return response()->json($data);
        }
        $response->data = $data;
        return response()->json($response);
    }
}