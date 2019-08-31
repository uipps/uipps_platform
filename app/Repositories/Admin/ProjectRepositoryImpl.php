<?php

namespace App\Repositories\Admin;

use App\Models\Admin\Project;
use App\Repositories\BaseRepository;

class ProjectRepositoryImpl extends BaseRepository
{
    public function getProjectList($uids=[]) {
        $sql_where = [
            'status_' => 'use',
        ];
        if ($uids)
            $db_result = Project::where($sql_where)->whereIn('uid', $uids)->get();
        else $db_result = Project::where($sql_where)->get();
        if ($db_result)
            return $db_result->toArray();
        return $db_result;
    }

    public function getProjectById($pid) {
        if (!is_numeric($pid) || $pid <= 0)
            return [];
        $db_result = Project::find($pid);
        if ($db_result)
            return $db_result->toArray(); // 节省内存
        return $db_result;
    }

    public function getProjectDsnList($with_dbname = true) {
        $db_result = Project::get();
        if ($db_result)
            return $db_result->toArray(); // 节省内存
        return $db_result;
    }
}