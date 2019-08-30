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
        return $db_result;
    }

    public function getProjectById($pid) {
        if (!is_numeric($pid) || $pid <= 0)
            return [];
        $db_result = Project::find($pid);
        return $db_result;
    }
}