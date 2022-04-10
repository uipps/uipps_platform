<?php

namespace App\Models\Uipps;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $connection='';
    //protected $primaryKey='id';
    protected $table = 'project';
}
