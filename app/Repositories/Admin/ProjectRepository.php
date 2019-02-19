<?php

namespace App\Repositories\Admin;

use App\Models\Admin\Project;
use App\Repositories\BaseRepository;

class ProjectRepository extends BaseRepository
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
}