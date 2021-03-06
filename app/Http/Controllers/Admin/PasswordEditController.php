<?php

namespace App\Http\Controllers\Admin;


use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DBR;
use DBW;

class PasswordEditController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function execute(Request $a_request)
    {
        // 检查是否登录
        $l_auth = $this->userService->ValidatePerm($a_request);
        $_SESSION = session()->all();
        if (!$l_auth || !isset($_SESSION['user']) || !$_SESSION['user']) {
            return redirect('/admin/login');
        }

        $actionMap = [];
        $actionError = [];
        $response = [];
        $form = $a_request->post();
        $get = [];
        $cookie = [];
        $files = [];

        $request = $a_request->all();
        $request['do'] = 'password_edit';
        //$_SESSION = session()->all();


        $dbR = DBR::getDBR();
        $dbR->table_name = "user";
        if (!$a_request->isMethod('get')) {
            // 获取用户信息
            $_arr = $dbR->getOne(" where id = ". $_SESSION['user']['id']);
            if ($_arr) {
                $o_passwd = $_arr['password'];
                if($form['password']!=$form['password_c']){
                    $response['html_content'] = '输入密码不一致';
                    $response['ret'] = array('ret'=>0);
                    return $response['html_content'];  // 总是返回此结果
                }
                if (md5($form['password_o']) != $o_passwd) {
                    $response['html_content'] = '输入密码不正确';
                    $response['ret'] = array('ret'=>0);
                    return $response['html_content'];  // 总是返回此结果
                } else {
                    // 则进行重置密码
                    $dbW = DBW::getDBW();
                    $dbW->table_name = "user";
                    $dbW->updateOne(array('password'=>md5($form['password'])), 'id=' . $_arr['id']);

                    $response['html_content'] = '修改成功';
                    $response['ret'] = array('ret'=>0);
                    return $response['html_content'];  // 总是返回此结果
                }
            } else {
                //echo('用户不存在！');
                return '用户不存在！';
            }
        }

        $data_arr = array(
            "nickname"=>$_SESSION['user']['nickname'],
            "ip"=>getip(),
            "get_csrf_token"=>csrf_token(),
            "RES_WEBPATH_PREF"=>$GLOBALS['cfg']['RES_WEBPATH_PREF'],
        );
        // 获取模板
        $content = file_get_contents(resource_path() . '/views/admin/' . $request['do'] . '.html');

        $response['html_content'] = replace_template_para($data_arr, $content);
        $response['ret'] = array('ret'=>0);
        return $response['html_content'];  // 总是返回此结果
    }
}
