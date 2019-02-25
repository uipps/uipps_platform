<?php

namespace App\Services\Admin;

use App\Dto\ResponseDto;
use App\Libs\Utils\ErrorMsg;
use App\Repositories\Admin\UserProjPrivilegesRepository;
use App\Repositories\Admin\UserRepository;
use App\Repositories\Admin\UserTempdefPrivilegesRepository;
use App\Services\BaseService;

class UserService extends BaseService
{
    protected $_sid = null;
    protected $_fields = ['id','username','password','nickname','email','g_id','is_admin','if_super','expired']; // 'proj_priv',
    protected $_cookie_sid_domain = '';
    protected $_cookie_sid_path   = '/';
    protected $_cookie_sid_expire = 315360000;  // 十年时间 3650*24*3600
    protected $_sid_str = 'sid';    // cookie中sid的名称

    protected $userRepository;
    protected $userProjPrivilegesRepository;
    protected $userTempdefPrivilegesRepository;

    public function __construct(
        UserProjPrivilegesRepository $userProjPrivilegesRepository,
        UserTempdefPrivilegesRepository $userTempdefPrivilegesRepository,
        UserRepository $userRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userProjPrivilegesRepository = $userProjPrivilegesRepository;
        $this->userTempdefPrivilegesRepository = $userTempdefPrivilegesRepository;

        // 当前域名
        $url = url()->current();
        $url_arr = parse_url($url);
        $this->_cookie_sid_domain = self::GetCookieDomain($url_arr['host']);
    }

    public function getLocUserexistByuser($a_uinfo)
    {
        return self::getUserexistByuser($a_uinfo);
    }

    public function getUserexistByuser($params) {
        $responseDto = new ResponseDto();
        if (!isset($params['username']) || !isset($params['password'])) {
            ErrorMsg::FillResponseAndLog($responseDto, ErrorMsg::PARAM_ERROR);
            return $responseDto;
        }

        // 先检查用户是否存在，用于前端显示不同信息
        $l_user = collect($this->userRepository->getUserexistByuser($params))->toArray();
        // 如果为空表示用户不存在, 将返回null空; 存在则返回数组
        if (!$l_user) {
            ErrorMsg::FillResponseAndLog($responseDto, ErrorMsg::USER_NOT_EXISTS);
            return $responseDto;
        }

        // google Authenticator
        if (isset($l_user['google_authenticator']) && $l_user['google_authenticator']) {
            $ga = new \PHPGangsta_GoogleAuthenticator();
            $secret = $l_user['google_authenticator']; // 每个用户只需要产生一次即可，然后将secret保存到数据库，用户自己也需要保存下来。
            $checkResult = $ga->verifyCode($secret, $params['googlecode'], 2);    // 2 = 2*30sec clock tolerance
            if (!$checkResult) {
                ErrorMsg::FillResponseAndLog($responseDto, ErrorMsg::GOOGLE_AUTHENTICATOR_ERROR);
                return $responseDto;
            }
        }

        // 密码验证
        $password = $params['password'];
        $md5_pass = self::getMd5Password($password);
        if (isset($params['md5pass']) && $params['md5pass']) { // 前端md5加密过
            $md5_pass = self::getMd5Password($password, 1);
        }
        if ($md5_pass != $l_user['password']) {
            ErrorMsg::FillResponseAndLog($responseDto, ErrorMsg::PASSWORD_ERROR);
            return $responseDto;
        }

        // 用户状态
        if ('use' != $l_user['status_']) {
            // 用户被删除的情况
            ErrorMsg::FillResponseAndLog($responseDto, ErrorMsg::USER_STATUS_ERROR);
            return $responseDto;
        }

        // 同时需要将该用户的权限数据获取到，获取用户的项目和相应的表权限。并直接赋值到结果数组中
        //$proj_priv = self::getProjTBLPriv($l_user);
        //$responseDto->user_info = $l_user
        $responseDto->data = $l_user;
        return $responseDto;
    }

    // is_md5_pass表示是否已经加密过一次
    public function getMd5Password($pwd, $is_md5_pass=false) {
        if (!$is_md5_pass)
            $pwd = md5($pwd);
        return $pwd; // 沿用旧的 TODO 以后有时间则改成多次md5提高安全性
        //return md5($pwd . env('PWD_SECRET_KEY', 'my_secret_key'));
    }

    // 从两张表中获取项目和表的权限
    public function getProjTBLPriv(&$a_uinfo ){
        $u_id = $a_uinfo['id'];

        if ($u_id<=0) {
            return [];
        }
        // 通过u_id获取到其项目id数组和表权限
        $proj_priv = collect($this->userProjPrivilegesRepository->getUserProjectPrivilegeByUid($u_id))->toArray();
        if (!$proj_priv || !array_key_exists('suoshuxiangmu_id', $proj_priv[0]))
            return [];
        // 变成按照字段排列的
        $proj_priv = \cArray::Index2KeyArr($proj_priv, $a_val=array('key'=>'suoshuxiangmu_id', 'value'=>array()));

        $tbl_priv = collect($this->userTempdefPrivilegesRepository->getUserTempdefPrivilegesByUid($u_id))->toArray();
        if (!$tbl_priv)
            return [];

        foreach ($tbl_priv as $l_tbl) {
            // 按照t_id重新组织一下, 但必须保证已经有项目权限
            if (array_key_exists($l_tbl['suoshuxiangmu_id'], $proj_priv)) {
                $proj_priv[$l_tbl['suoshuxiangmu_id']]['tbl_priv'][$l_tbl['suoshubiao_id']] = $l_tbl;
            }
        }

        $a_uinfo['proj_priv'] = $proj_priv;
        return $proj_priv;
    }


    public function SetSessionCookieByUserArr($l_uinfo, $a_arr=array()){
        \Log::info(var_export($l_uinfo, true));
        $this->InitUserRowByInfo($l_uinfo);
        $this->_sid = \UIBI::getSidFromUIBIByUserPass($l_uinfo);

        // 可以在此种植需要用到的cookie，甚至是记住登录处sid
        $this->setUserSession();
        $this->setUserCookie();
        // 种cookie
        if (isset($a_arr['remember']) && $a_arr['remember']) {
            // 用户设置记录帐号, 记住登录处sid的cookie, 用于唯一识别
            if (!empty($this->_sid)) setcookie($this->_sid_str, $this->_sid, time()+$this->_cookie_sid_expire, $this->_cookie_sid_path, $this->_cookie_sid_domain);
        }
    }

    public function setUserCookie(){
        if ($this->id) {
            // 常用cookie种下
            setcookie('uid', $this->id, time()+$this->_cookie_sid_expire, $this->_cookie_sid_path, $this->_cookie_sid_domain);
            setcookie('user', $this->username, time()+$this->_cookie_sid_expire, $this->_cookie_sid_path, $this->_cookie_sid_domain);
            \Cookie::make('uid', $this->id, time()+$this->_cookie_sid_expire, $this->_cookie_sid_path, $this->_cookie_sid_domain);
            \Cookie::make('user', $this->username, time()+$this->_cookie_sid_expire, $this->_cookie_sid_path, $this->_cookie_sid_domain);
            if (''!=$this->email) {
                setcookie('email', $this->email, time()+$this->_cookie_sid_expire, $this->_cookie_sid_path, $this->_cookie_sid_domain);
                \Cookie::make('email', $this->email, time()+$this->_cookie_sid_expire, $this->_cookie_sid_path, $this->_cookie_sid_domain);
            }
        }
    }

    public function setUserSession(){

        if ($this->id) {
            // 设置 session
            $this->InitUserSession();
            return true;
        } else {
            $this->logout();
            return false;
        }
    }

    public function logout() {
        // 销毁session
        session()->flush();

        // 销毁cookie
        $this->destroy_cookie();
        return true;
    }

    public function destroy_cookie($name=null){
        if (empty($name)) $name = $this->_sid_str;
        setcookie($name, '', time()-3600, $this->_cookie_sid_path, $this->_cookie_sid_domain);
    }

    public function InitUserSession(){
        if (!\Session::has('user')) {
            \Session::put('user', []);
        }
        if ( !isset($_SESSION['user']) ){
            $_SESSION['user'] = array();
        }
        // session()->put('user', collect($l_user)->toArray());
        // $_SESSION['user']['username'] = $this->username;
        $_session = [];
        foreach ($this->_fields as $l_field){
            //$_SESSION['user'][$l_field] = $this->$l_field;
            //\Session::push("user.$l_field", $this->$l_field); // push变成多维数组，得用put
            $_session[$l_field] = $this->$l_field;
        }
        \Session::put('user', $_session);
    }

    public function InitUserRow_(){
        // $this->id = $_SESSION['user']['id'];
        if (isset($_SESSION['user'])) {
            foreach ($this->_fields as $l_field){
                $this->$l_field = $_SESSION['user'][$l_field];
            }
        }
        if (\Session::has('user')) {
            foreach ($this->_fields as $l_field){
                $this->$l_field = session('user')[$l_field];
            }
        }
    }

    public function InitUserRowByInfo($a_uinfo)
    {//echo __LINE__ . "\r\n";print_r($a_uinfo);
        // $this->id = $a_uinfo['id'];
        foreach ($this->_fields as $l_field){
            $this->$l_field = $a_uinfo[$l_field];
            \Log::info(var_export($this->$l_field, true));
        }
    }

    // TODO 临时使用
    public function GetCookieDomain($host) {
        // 如果是合法IP，直接返回
        if (filter_var($host, FILTER_VALIDATE_IP))
            return $host;
        // 如果只有一个点或没有点，这整个就是域名直接返回
        $host = trim($host);
        if (!$host || false === strpos($host, '.'))
            return $host;
        $dot_count = substr_count($host, '.');
        if ($dot_count <= 1)
            return $host;
        // 2个点以上，则只需截取最后两项作为顶级域
        $last_str = substr($host, strpos($host, '.'));
        return  $last_str; // .uipps.com
    }

    /**
     * 通过给定的sid字符串，判断是否正确的sid，成功返回用户信息，失败返回错误信息
     *
     * @param string $sid
     * @return array, ret=0|1成功还是失败
     *
     */
    public function IsSid($sid){
        $uid   = substr($sid, 32);
        $u_arr  = array("username"=>$uid);
        $l_rlt   = $this->getLocUserexistByuser($u_arr);

        // 通过id获取到用户名和密码等信息以后进行加密验证, 如果跟提供的sid吻合则表示正确, 否则错误
        if (0==$l_rlt['ret']) {
            $l_sid = \UIBI::getSidFromUIBIByUserPass($l_rlt['data']);
            if ($l_sid != $sid) {
                // 如果两次的sid不一致，认为是伪造的，因此设置为错误
                $l_rlt['ret'] = 9;
                $l_rlt['msg'] = "_sid_ is wrong!";
            }
        }

        return $l_rlt;
    }

    public function ValidatePerm ($a_request){
        $l_auth = $this->Authorize($a_request);
        if ( is_string($l_auth) ){
            return $l_auth;  // 返回错误信息
        }
        if ($l_auth) {
            return $this->Authorize($a_request); // 再一次请求的目的是上一次请求已经做了一些处理
        }
        // 还有其他一些权限，都在此处理
        return false;
    }

    public function Authorize($a_arr) {
        $_SESSION = session()->all();
        $_COOKIE = [];
        if (property_exists($a_arr, 'cookies'))
            $_COOKIE = $a_arr->cookies->all();

        // 返回地址
        if ( !isset($_SESSION['back_url']) && isset($a_arr["back_url"]) ){
            $_SESSION["back_url"] = $a_arr["back_url"];
        }

        // 如果使用 session 和 cookie 验证
        if ( isset($_SESSION['user']) && $_SESSION['user'] ){
            // session存在可以认为已经登录成功，可不用种cookie。
            return true;
        }

        if (\Session::has('user') && session('user', '')) {
            return true;
        }

        if (isset($_COOKIE[$this->_sid_str]) && $_COOKIE[$this->_sid_str]) {
            // kaixin001 _kx    42ebe5b40da8b6ce25e1ae1a47c4dea5_105421
            // 发送 cookie 的sid C33E51F0C725BA9B9BC368B9B7B15A25ifeng_test002 到指定服务器进行验证
            $l_rlt = $this->IsSid($_COOKIE[$this->_sid_str]);  //
            if (0==$l_rlt['ret']) {
                $this->_sid = $_COOKIE[$this->_sid_str];
                $this->InitUserRowByInfo($l_rlt['user_info']);
                $this->InitUserSession();
                return true;
            } else if (1==$l_rlt['ret']) {
                return $l_rlt['msg'];  // 1表示用户不存在, 返回字符串
            } else {
                // 注销错误的cookie, 此处ret为9表示sid是伪造的或者过期的
                $this->destroy_cookie();
                // return false; 先不返回，还要进行session判断
            }
        }

        // 用户提交的数据 // 没有session和cookie，则试图种
        if (isset($a_arr["username"]) && $a_arr["username"] && $a_arr["password"]) {
            $username = $a_arr['username'];
            $password = $a_arr['password'];

            if (isset($_SESSION["ERROR_LOGIN"]["num"]) && $_SESSION["ERROR_LOGIN"]["num"]>0) {
                if(strtolower($_SESSION["AI-code"]) != strtolower($a_arr["aicode"])){
                    // 验证码错误
                    $_SESSION["AI_code_error"] = 1;
                    return false;
                }
            }
            // username 也可以是用户id
            $l_rlt = $this->getLocUserexistByuser($a_arr);

            if (0==$l_rlt['ret']){
                $this->SetSessionCookieByUserArr($l_rlt['user_info'], $a_arr);

                // 记录下登录日志 begin
                $data_arr = array(
                    "username"=>$this->username,
                    "nickname"=>$this->nickname,
                    "succ_or_not"=>"y"
                );
                $rlt = $this->LogerLoginlog($data_arr);
                // 记录日志完成 end

                return true;
            } else {
                if ( !isset($_SESSION['ERROR_LOGIN']) ){
                    $_SESSION['ERROR_LOGIN'] = array();
                }
                if (!isset($_SESSION["ERROR_LOGIN"]["num"])) {
                    $_SESSION["ERROR_LOGIN"]["num"] = 0;
                }
                $_SESSION["ERROR_LOGIN"]["num"] += 1;

                // 用户名或密码不正确, 记录登录日志
                $data_arr = array(
                    "username"=>$username,
                    "description"=>$password,
                    "succ_or_not"=>"n"
                );
                $rlt = $this->LogerLoginlog($data_arr);

                return false;
            }
        } else {
            return false;
        }

        return true;
    }

    /*private function __get($property_name) {
        if (isset($this->$property_name)) {
            return($this->$property_name);
        } else {
            return null;
        }
    }
    private function __set($property_name, $value) {
        $this->$property_name = $value;
    }*/

    public function __destruct(){}
}
