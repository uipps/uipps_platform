<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'project';
    public $timestamps = false;
    //protected $fillable = ['name_cn', 'type', 'parent_id', 'table_field_belong_project_id', 'db_host', 'db_port', 'db_name', 'db_user', 'db_pwd', 'db_timeout'];
}
