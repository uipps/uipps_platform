<?php

namespace App\Services\Delivery;

use App\Dto\ResponseDto;
use App\Libs\Utils\ErrorMsg;
use Illuminate\Support\Facades\Cache;

// 数据库加缓存即可，这里可以不用加缓存
class DeliveryService extends DeliveryServiceImpl
{
    const CACHE_TIME = 0.05; // 缓存时间，单位分

    private function GetCacheKey($dispatch_id) {
        return 'delivery-dispatch-info-service-' . $dispatch_id;
    }

    // 增加cache层
    public function getDeliveryInfoById($params) {
        $response = new ResponseDto();
        if (!isset($params['id']) || $params['id'] <= 0) {
            ErrorMsg::FillResponseAndLog($response, ErrorMsg::PARAM_ERROR);
            return $response;
        }

        $cache_key = $this->GetCacheKey($params['id']);

        $cached_result = Cache::get($cache_key);
        if ($cached_result)
            return $cached_result;

        $db_result = parent::getDeliveryInfoById($params);
        if ($db_result) {
            Cache::add($cache_key, $db_result, self::CACHE_TIME);
        }
        return $db_result;
    }
}
