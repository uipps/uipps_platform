<?php

namespace App\Repositories\Admin;

use Illuminate\Support\Facades\Cache;

class ProjectRepository extends ProjectRepositoryImpl
{
    const CACHE_TIME = 1; // 单位分

    private function GetCacheKey($pid) {
        return 'platform-project-info-' . $pid;
    }

    public function getProjectById($pid) {
        $cache_key = $this->GetCacheKey($pid);

        $cached_result = Cache::get($cache_key);
        if ($cached_result)
            return $cached_result;

        $db_result = parent::getProjectById($pid);
        if ($db_result) {
            Cache::add($cache_key, $db_result, self::CACHE_TIME);
        }
        return $db_result;
    }
}