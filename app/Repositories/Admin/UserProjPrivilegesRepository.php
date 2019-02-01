<?php

namespace App\Repositories\Admin;

use App\Models\Admin\UserProjPrivileges;
use App\Repositories\BaseRepository;

class UserProjPrivilegesRepository extends BaseRepository
{
    public function getUserProjectPrivilegeByUid($uid) {
        $sql_where = [
            'u_id' => $uid,
            'status_' => 'use',
        ];
        $db_result = UserProjPrivileges::where($sql_where)->get();
        return $db_result;
    }
}