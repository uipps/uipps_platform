<?php

namespace App\Repositories\Admin;

use Illuminate\Support\Facades\Cache;

class ProjectRepository extends ProjectRepositoryImpl
{
    const CACHE_TIME = 1; // 单位分
    const CACHE_TIME_DSN_PROJECT_LIST = 60;

    private function GetCacheKey($pid) {
        return 'platform-project-info-' . $pid;
    }

    private function GetProjectDsnCacheKey() {
        return 'platform-project-list-';
    }

    public function getProjectById($pid) {
        $cache_key = $this->GetCacheKey($pid);

        $cached_result = Cache::get($cache_key);
        if ($cached_result)
            return $cached_result;

        $db_result = parent::getProjectById($pid);
        if ($db_result)
            Cache::add($cache_key, $db_result, self::CACHE_TIME);
        return $db_result;
    }

    public function getProjectDsnList($with_dbname = true) {
        $cache_key = $this->GetProjectDsnCacheKey();

        $cached_result = Cache::get($cache_key);
        if ($cached_result)
            return $cached_result;

        $db_result = parent::getProjectDsnList($with_dbname);
        if (!$db_result)
            return $db_result;
        // 按照dsn作为可以重新组织数组
        $rlt = [];
        foreach ($db_result as $row) {
            $l_dsn = \DbHelper::getConnectName($row, true, false);
            $rlt[$l_dsn] = $row;
        }
        Cache::add($cache_key, $rlt, self::CACHE_TIME_DSN_PROJECT_LIST);
        return $rlt;
    }
}