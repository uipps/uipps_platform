<?php

namespace App\Repositories\Admin;

use App\Models\Admin\User;
use App\Repositories\BaseRepository;


class UserRepositoryImpl extends BaseRepository
{
    public function getUserexistByuser($params) {
        $sql_where = [
            'username' => $params['username'],
        ];
        $db_result = User::where($sql_where)->first();
        return $db_result;
    }
}