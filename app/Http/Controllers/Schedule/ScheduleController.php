<?php

namespace App\Http\Controllers\Schedule;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ScheduleController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function list(Request $request)
    {
        return __NAMESPACE__ . "<br>\r\n" . __CLASS__ .  "<br>\r\n"  . __FUNCTION__;
    }

    public function add(Request $request)
    {
        return __NAMESPACE__ .  "<br>\r\n"  . __CLASS__ .  "<br>\r\n"  . __FUNCTION__;
    }

    public function edit(Request $request)
    {
        return __NAMESPACE__ .  "<br>\r\n"  . __CLASS__ .  "<br>\r\n"  . __FUNCTION__;
    }
}
