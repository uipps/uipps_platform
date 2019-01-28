<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function mainpage(Request $request)
    {
        return __NAMESPACE__ . "<br>\r\n" . __CLASS__ .  "<br>\r\n"  . __FUNCTION__;
    }
}
