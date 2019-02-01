<?php

namespace App\Http\Controllers\Admin;

use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function mainpage(Request $request)
    {
        // 检查是否登录
        $l_auth = $this->userService->ValidatePerm($request);
        if (!$l_auth) {
            redirect('admin.login');
        }

        return __NAMESPACE__ . "<br>\r\n" . __CLASS__ .  "<br>\r\n"  . __FUNCTION__;
    }
}
