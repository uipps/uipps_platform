<?php

namespace App\Repositories\Delivery;

use Illuminate\Support\Facades\Cache;

class DeliveryRepository extends DeliveryRepositoryImpl
{
    const CACHE_TIME = 0.05; // 缓存时间，单位分

    private function GetCacheKey($dispatch_id) {
        return 'delivery-dispatch-info-dao-' . $dispatch_id;
    }

    public function getDeliveryInfoById($dispatch_id) {
        $cache_key = $this->GetCacheKey($dispatch_id);

        $cached_result = Cache::get($cache_key);
        if ($cached_result)
            return $cached_result;

        $db_result = parent::getDeliveryInfoById($dispatch_id);
        if ($db_result) {
            Cache::add($cache_key, $db_result, self::CACHE_TIME);
        }
        return $db_result;
    }
}