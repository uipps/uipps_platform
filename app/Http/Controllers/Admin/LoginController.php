<?php

namespace App\Http\Controllers\Admin;

use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    protected $userService;


    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function showLoginForm(Request $request)
    {
        $params = $request->all();
        $l_data_arr = self::getDataArray($params);
        return view('admin.login_register.login', $l_data_arr);
    }

    // 自定义登录验证
    public function loginAdmin(Request $request)
    {
        $form = $request->all();

        $l_data_arr = self::getDataArray($form);

        $result = $this->userService->getUserexistByuser($form);
        //$result = $this->userService->ValidatePerm($form);
        if ($result->ret > 0) {
            $l_data_arr['action_error_notice'] = $result->msg;
            return view('admin.login_register.login', $l_data_arr);
        }
        // 登录成功，种cookie，session等
        $this->userService->SetSessionCookieByUserArr($result->data, $form);
        if ( !empty($request['back_url']) ){
            // 似乎应该用session中注册的，以后验证????
            return redirect($request['back_url']);
        }
        return redirect('/admin/mainpage');
    }

    public function logout(Request $request) {
        $this->userService->logout();
        return redirect(route('admin.login'));
    }


    private function getDataArray($params) {
        $l_data_arr = [
            "header"=>'',
            "footer"=>'',
            "system_name"=>env('SYSTEM_NAME_STR'),
            "RES_WEBPATH_PREF"=>env('RES_WEBPATH_PREF'),
            "l_yanzhengma"=>"",
            "l_yanzhengma_js"=>"",
            "l_web_domain"=>'webdomain', //$GLOBALS['cfg']['WEB_DOMAIN'],
            "back_url"=>isset($params['back_url']) ? urlencode($params['back_url']) : '',
            "action_error_notice"=>'',
            "action_error_username"=>'',
            "action_error_password"=>'',
            "action_error_googlecode"=>'',
        ];

        return $l_data_arr;
    }

    //
    /*protected function authenticated(Request $request, $user)
    {
        if ($user->active == 1) {
            // 可以登录
            return redirect()->intended($this->redirectPath());
        }
        return $this->logout($request);
    }
    protected function credentials(Request $request) {
        $credentials = $request->only($this->username(), 'password'); // or add another item here if it's from the request
        $credentials['status_'] = 'use';
        return $credentials;
    }
    public function username()
    {
        return 'username';
    }
    public function redirectTo()
    {
        return route('admin.mainpage');
    }*/

}
