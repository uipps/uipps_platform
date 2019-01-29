<?php

namespace App\Models\User;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    const STATUS_DELETE  = 0; // 删除
    const STATUS_NORMAL  = 1; // 正常

    protected $connection = 'user_db'; // 用于注册用户, 由于是后台管理，暂时可以不用这些功能,仅用于学习测试
    protected $table = 'users'; // 默认就是model名后加s
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
