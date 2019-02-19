<?php

namespace App\Http\Controllers\Project;

use App\Services\Admin\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }

    public function list(Request $request)
    {
        // 检查是否登录
        $l_auth = $this->userService->ValidatePerm($request);
        $_SESSION = session()->all();
        if (!$l_auth || !isset($_SESSION['user']) || !$_SESSION['user']) {
            return redirect('/admin/login');
        }
        $GLOBALS['cfg']['RES_WEBPATH_PREF'] = env('RES_WEBPATH_PREF');
        $GLOBALS['cfg']['db_character'] = env('db_character', 'utf8');
        $GLOBALS['cfg']['out_character'] = env('out_character', 'utf8');


        if (isset($_SESSION['user']['nickname'])) {
            $nickname = convCharacter($_SESSION['user']['nickname']);
        }else {
            $nickname = convCharacter($_SESSION['user']['username']);
        }

        // 先获取模板
        $l_file = __FUNCTION__ . '.blade.php';
        $l_path = resource_path() . '/views/admin/';
        $content = file_get_contents($l_path . $l_file);
        //unlink($l_path . $l_file);usleep(2000);
        if (false !== strpos($content, '<!--{'))
            file_put_contents($l_path . $l_file, str_replace(['<!--{', '}-->'], ['{{', '}}'], $content));
        // 加入头尾
        $header = file_get_contents(resource_path() . '/views/admin/'.'header.html');  // 标准头
        $footer = file_get_contents(resource_path() . '/views/admin/'.'footer.html');  // 标准尾
        $data_arr = array(
            'nickname'=>$nickname,
            'ip'=>getip(),
            'RES_WEBPATH_PREF'=>$GLOBALS['cfg']['RES_WEBPATH_PREF'],
            'header'=>$header,
            'footer'=>$footer
        );
        return view('admin/list', $data_arr);
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
