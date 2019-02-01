<?php

namespace App\Repositories\Admin;

use App\Models\Admin\UserTempdefPrivileges;
use App\Repositories\BaseRepository;

class UserTempdefPrivilegesRepository extends BaseRepository
{
    public function getUserTempdefPrivilegesByUid($uid) {
        $sql_where = [
            'u_id' => $uid,
            'status_' => 'use',
        ];
        $db_result = UserTempdefPrivileges::where($sql_where)->get();
        return $db_result;
    }
}