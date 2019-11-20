<?php

namespace App\Repositories\Admin;

use Illuminate\Support\Facades\Cache;

class ProjectRepository extends ProjectRepositoryImpl
{
    const CACHE_TIME = 0.02; // 单位分
    const CACHE_TIME_DSN_PROJECT_LIST = 0.05;

    private function GetCacheKey($pid) {
        return 'platform-project-info-' . $pid;
    }

    private function GetProjectDsnCacheKey() {
        return 'platform-project-list-';
    }

    /*public function getProjectById($pid) {
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
        if ($db_result)
            Cache::add($cache_key, $db_result, self::CACHE_TIME_DSN_PROJECT_LIST);
        return $db_result;
    }*/

    // 插入一条项目记录，同时要删除cache（这里就简单删除cache即可）
    public function insertOneProject($data_arr) {
        $cache_key = $this->GetProjectDsnCacheKey();
        $rlt = parent::insertOneProject($data_arr);
        Cache::forget($cache_key); // 删除缓存
        return $rlt;
    }
}
