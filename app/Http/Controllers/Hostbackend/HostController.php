<?php

namespace App\Http\Controllers\Hostbackend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HostController extends Controller
{
    public function __construct() {
        $this->middleware('admin_auth');
    }

    public function list(Request $a_request)
    {
        return __NAMESPACE__ . "<br>\r\n" . __CLASS__ .  "<br>\r\n"  . __FUNCTION__;
    }
}
