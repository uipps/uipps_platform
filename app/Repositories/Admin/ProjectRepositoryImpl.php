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
        if (!$db_result)
            return $db_result;

        $db_result_list = $db_result->toArray(); // 节省内存

        // 按照dsn作为可以重新组织数组
        $rlt = [];
        foreach ($db_result_list as $row) {
            $l_dsn = \DbHelper::getConnectName($row, true, false);
            $rlt[$l_dsn] = $row;
        }
        return $rlt;
    }

    // 插入单条记录
    public function insertOneProject($data_arr) {
        return Project::insertGetId($data_arr);
        //return $project->insertGetId($data_arr);
    }
}
