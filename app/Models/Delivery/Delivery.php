<?php

namespace App\Models\Delivery;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    const STATUS_TO_DELIVERY  = 1; // 待配送
    const STATUS_DELIVERING   = 2; // 配送中

    protected $connection = 'mysql57';
    protected $table = 'jobs';
    public $timestamps = false;
}