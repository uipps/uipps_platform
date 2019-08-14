<?php
define('TABLENAME_PREF', env('DB_PREFIX', 'dpps_'));
$GLOBALS['cfg']['WEB_DOMAIN'] = 'uipps.com';

$GLOBALS['cfg']['__LIMIT__'] = '__LIMIT__';   // 条数限制
$GLOBALS['cfg']['__OFFSET__'] = '__OFFSET__'; // 起始位置

$GLOBALS['cfg']['RES_WEBPATH_PREF'] = env('RES_WEBPATH_PREF');
$GLOBALS['cfg']['db_character'] = env('db_character', 'utf8');
$GLOBALS['cfg']['out_character'] = env('out_character', 'utf8');

$GLOBALS['cfg']['SOURCE_CSS_PATH'] = "WEB-INF/template/css";
$GLOBALS['cfg']['SOURCE_JS_PATH']  = "WEB-INF/template/js";
$GLOBALS['cfg']['SOURCE_IMG_PATH'] = "WEB-INF/template/images";
require_once 'chinese.utf8.lang.php';


// system.conf.php
define('SESSION_EXPIRE_TIME', 86400); // session的超时时间
define('APPSESSIONID', 'SESSION_ID_ADMIN');

$GLOBALS['cfg']['WEB_DOMAIN'] = 'ni9ni.com';
$GLOBALS['cfg']['SMS_CODE_KEY'] = '_SMS_CODE_KEY'; // 短语验证码key

$GLOBALS['cfg']['__LIMIT__'] = '__LIMIT__';   // 条数限制
$GLOBALS['cfg']['__OFFSET__'] = '__OFFSET__'; // 起始位置

$GLOBALS['cfg']['DEVELOPE_ENV'] = '';
if ('cli' == php_sapi_name() && isset($_o) && $_o["e"]) $GLOBALS['cfg']['DEVELOPE_ENV'] = $_o["e"];

// memcached 服务器地址及端口, 支持多组memcache
$GLOBALS['g_memcached_servers'] = array(
    'default' => array(array('127.0.0.1', 11211),
        array('127.0.0.1', 11211),
    ),
    'session' => array(array('127.0.0.1', 11211),
        array('127.0.0.1', 11211),
    ),
);
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    if(!isset($GLOBALS['cfg']['PATH_ROOT'])) $GLOBALS['cfg']['PATH_ROOT'] = str_replace("\\","/",dirname(dirname(__FILE__)));//"D:/www/dpa";
    $GLOBALS['cfg']['PATH_RUNTIME'] = str_replace("\\","/",dirname(dirname(__FILE__)));    // PATH_RUNTIME用于后台程序的，PATH_ROOT前台程序
    $GLOBALS['cfg']['PATH_PHP_LIBS'] = "D:/www";
    $GLOBALS['cfg']['PATH_PEAR'] = $GLOBALS['cfg']['PATH_PHP_LIBS'] . "/pear";
    $GLOBALS['cfg']['LOG_PATH']  = "D:/www";
    $GLOBALS['cfg']['RES_WEBPATH_PREF'] = "/";
    $GLOBALS['cfg']['INI_CONFIGS_PATH'] = "D:/www/config";
    $GLOBALS['cfg']['IFMDB2'] = true;
    $GLOBALS['cfg']['IFMYSQLI'] = false;
    $GLOBALS['cfg']['MEMCACHE_SESSION'] = true;
    $GLOBALS['cfg']['db_character'] = "utf8";
    $GLOBALS['cfg']['db_character_contype'] = "utf-8";
    $GLOBALS['cfg']['out_character'] = "utf8";
    $GLOBALS['cfg']['out_character_contype'] = "utf-8";
    $GLOBALS['cfg']['SYSTEM_DB_DSN_NAME_W'] = "dpa";
    $GLOBALS['cfg']['SYSTEM_DB_DSN_NAME_R'] = "dpa";
    $GLOBALS['cfg']['WEB_PROXY_FILE'] = "D:/www/config/proxy.ini";
    $GLOBALS['cfg']['IMG_UPLOAD_PATH'] = "D:/www/dpa/img3/upload";
    $GLOBALS['cfg']['IMG_URL_PRE'] = $GLOBALS['cfg']['RES_WEBPATH_PREF'] . "dpa/img3/upload";

} else if ('CYGWIN' === strtoupper(PHP_OS)) {
    if(!isset($GLOBALS['cfg']['PATH_ROOT'])) $GLOBALS['cfg']['PATH_ROOT'] = "/cygdrive/d/www/dpa";
    $GLOBALS['cfg']['PATH_RUNTIME'] = "/cygdrive/d/www/dpa";
    $GLOBALS['cfg']['PATH_PHP_LIBS'] = "/cygdrive/d/www";
    $GLOBALS['cfg']['PATH_PEAR'] = $GLOBALS['cfg']['PATH_PHP_LIBS'] . "/pear";
    $GLOBALS['cfg']['LOG_PATH']  = "/data1/logs";
    $GLOBALS['cfg']['RES_WEBPATH_PREF'] = "/";
    $GLOBALS['cfg']['INI_CONFIGS_PATH'] = "/cygdrive/d/www/config";
    $GLOBALS['cfg']['IFMDB2'] = true;
    $GLOBALS['cfg']['IFMYSQLI'] = false;
    $GLOBALS['cfg']['MEMCACHE_SESSION'] = true;
    $GLOBALS['cfg']['db_character'] = "utf8";
    $GLOBALS['cfg']['db_character_contype'] = "utf-8";
    $GLOBALS['cfg']['out_character'] = "utf8";
    $GLOBALS['cfg']['out_character_contype'] = "utf-8";

} else if ('DARWIN' === strtoupper(PHP_OS)) {
    // 苹果 Mac 得到的是Darwin

    if(!isset($GLOBALS['cfg']['PATH_ROOT'])) $GLOBALS['cfg']['PATH_ROOT'] = "/Users/cf/svn_dev/dpa";
    $GLOBALS['cfg']['PATH_RUNTIME'] = str_replace("\\","/",dirname(dirname(__FILE__)));//"/data0/deve/runtime";
    $GLOBALS['cfg']['PATH_PHP_LIBS'] = "/usr/local/opt/php56/lib/php/libs";
    $GLOBALS['cfg']['PATH_PEAR'] = "/usr/local/opt/php56/lib/php/";
    $GLOBALS['cfg']['INI_CONFIGS_PATH'] = file_exists('/Users/cf/Documents/deve/config_ini_files') ? '/Users/cf/Documents/deve/config_ini_files' : __DIR__;
    // 如果是 https会有“已阻止载入混合活动内容”的风险
    //if (isset($_SERVER['SERVER_PORT']) && 443 == $_SERVER['SERVER_PORT']) $GLOBALS['cfg']['RES_WEBPATH_PREF'] = 'https://img3.' .$GLOBALS['cfg']['WEB_DOMAIN']. '/'; else
    //$GLOBALS['cfg']['RES_WEBPATH_PREF'] = '//img3.' .$GLOBALS['cfg']['WEB_DOMAIN']. '/';
    $GLOBALS['cfg']['RES_WEBPATH_PREF'] = '/';
    $GLOBALS['cfg']['LOG_PATH']  = "/Users/cf/logs_all/logs_dpa";
    $GLOBALS['cfg']['IFMDB2'] = true;
    $GLOBALS['cfg']['IFMYSQLI'] = false;
    $GLOBALS['cfg']['MEMCACHE_SESSION'] = true;
    $GLOBALS['cfg']['db_character'] = "utf8";
    $GLOBALS['cfg']['db_character_contype'] = "utf-8";
    $GLOBALS['cfg']['out_character'] = "utf8";
    $GLOBALS['cfg']['out_character_contype'] = "utf-8";
    $GLOBALS['cfg']['SYSTEM_DB_DSN_NAME_W'] = "dpa";
    $GLOBALS['cfg']['SYSTEM_DB_DSN_NAME_R'] = "dpa3307_r";
    $GLOBALS['cfg']['IMG_UPLOAD_PATH'] = "/data0/htdocs/img3/upload"; // 以后放到
    $GLOBALS['cfg']['IMG_URL_PRE'] = $GLOBALS['cfg']['RES_WEBPATH_PREF'] . "upload";

} else {
    if(!isset($GLOBALS['cfg']['PATH_ROOT'])) $GLOBALS['cfg']['PATH_ROOT'] = "/data0/htdocs/admin/dpa";
    $GLOBALS['cfg']['PATH_RUNTIME'] = str_replace("\\","/",dirname(dirname(__FILE__)));//"/data0/deve/runtime";
    $GLOBALS['cfg']['PATH_PHP_LIBS'] = "/usr/local/webserver/php/lib/php/libs";
    $GLOBALS['cfg']['PATH_PEAR'] = "/usr/local/webserver/php/lib/php";
    $GLOBALS['cfg']['INI_CONFIGS_PATH'] =  dirname(dirname(dirname(__DIR__))) . '/config_ini_files';
    // 如果是 https会有“已阻止载入混合活动内容”的风险
    //if (isset($_SERVER['SERVER_PORT']) && 443 == $_SERVER['SERVER_PORT']) $GLOBALS['cfg']['RES_WEBPATH_PREF'] = 'https://img3.' .$GLOBALS['cfg']['WEB_DOMAIN']. '/'; else
    $GLOBALS['cfg']['RES_WEBPATH_PREF'] = '//img3.' .$GLOBALS['cfg']['WEB_DOMAIN']. '/';
    $GLOBALS['cfg']['LOG_PATH']  = "/data1/logs";
    $GLOBALS['cfg']['IFMDB2'] = true;
    $GLOBALS['cfg']['IFMYSQLI'] = false;
    $GLOBALS['cfg']['MEMCACHE_SESSION'] = true;
    $GLOBALS['cfg']['db_character'] = "utf8";
    $GLOBALS['cfg']['db_character_contype'] = "utf-8";
    $GLOBALS['cfg']['out_character'] = "utf8";
    $GLOBALS['cfg']['out_character_contype'] = "utf-8";
    $GLOBALS['cfg']['SYSTEM_DB_DSN_NAME_W'] = "dpa";
    $GLOBALS['cfg']['SYSTEM_DB_DSN_NAME_R'] = "dpa3307_r";
    $GLOBALS['cfg']['IMG_UPLOAD_PATH'] = "/data0/htdocs/img3/upload"; // 以后放到
    $GLOBALS['cfg']['IMG_URL_PRE'] = $GLOBALS['cfg']['RES_WEBPATH_PREF'] . "upload";

    // 按照ip进行细分
    $exec = "/sbin/ifconfig | grep 'inet addr' | awk '{ print $2 }' | awk -F ':' '{ print $2}' | head -1";
    $local_ip = exec($exec);
    // WEB_ROOT用于项目内部
    if ('10.77.135.24' == trim($local_ip)) {
        // cms.wanhui.cn上的内网后台管理
        $GLOBALS['cfg']['PATH_PEAR'] = "/var/wd/cms/pear";
        $GLOBALS['cfg']['INI_CONFIGS_PATH'] = "/var/wd/cms/config_ini_files";
        $GLOBALS['cfg']['RES_WEBPATH_PREF'] = "http://cms.wanhui.cn/";
        $GLOBALS['cfg']['IMG_UPLOAD_PATH'] = "/var/wd/cms/upload/userfiles/cms_upload";
        $GLOBALS['cfg']['IMG_URL_PRE'] = $GLOBALS['cfg']['RES_WEBPATH_PREF'] . "upload/userfiles/cms_upload";
        $GLOBALS['cfg']['LOG_PATH']  = "/var/wd/cms/logs";

        // memcached 服务器地址及端口, 支持多组memcache
        $GLOBALS['g_memcached_servers'] = array(
            'default' => array(array('127.0.0.1', 11220),
                array('127.0.0.1', 11221),
            ),
            'session' => array(array('127.0.0.1', 11220),
                array('127.0.0.1', 11221),
            ),
        );
    }
}
// 以下常量将全部使用全局变量的方式，便于灵活修改全部放在变量cfg中
$GLOBALS['cfg']['IMG_TTF_FILE'] = $GLOBALS['cfg']['PATH_PEAR'] . '/jpgraph/fonts/DejaVuSans.ttf';
$GLOBALS['cfg']['Business_Path'] = "WEB-INF/Business";
$GLOBALS['cfg']['Validate_Path'] = "WEB-INF/validate";
$GLOBALS['cfg']['Template_Path'] = "WEB-INF/template";
$GLOBALS['cfg']['Tpl_Path']     = "WEB-INF/tpl";
$GLOBALS['cfg']['INI_DB_DSN_CONFIGS_FILE'] = "mysql_config.ini";
$GLOBALS['cfg']['INI_REDIS_DSN_CONFIGS_FILE'] = "redis_config.ini";
$GLOBALS['cfg']['INI_MEMCACHE_DSN_CONFIGS_FILE'] = "memcache_config.ini";
$GLOBALS['cfg']['LANG_DEFINE_FILE'] = "chinese.utf8.lang.php";
$GLOBALS['cfg']['DEBUG'] = false;
$GLOBALS['cfg']['DEFAULT_ACTION'] = "mainpage";
$GLOBALS['cfg']['DEFAULT_LOGIN_ACTION'] = "login";
$GLOBALS['cfg']['UPLOADIMG_PRE'] = "uploadimg_";
$GLOBALS['cfg']['RADIO_UPLOADIMG_CHANGE'] = "radio_change_";
$GLOBALS['cfg']['MAX_UPLOAD_IMG_SIZE'] = 8*1024*1024; // 最大文件大小 8M
// 数据库相关
$GLOBALS['cfg']['DB_DEFALUT_TYPE']     = 'aups_p';
$GLOBALS['cfg']['DB_TB_DEFALUT_TYPE']     = 'aups_t';
$GLOBALS['cfg']['DB_FIELD_DEFALUT_TYPE']   = 'aups_f';
// log4php配置文件路径
$GLOBALS['cfg']['LOG_CONF_FILE'] = $GLOBALS['cfg']['PATH_RUNTIME'] . '/configs/log4php.properties';

// 表相关
$GLOBALS['cfg']['TABLENAME_USER']      = "user";
$GLOBALS['cfg']['TABLENAME_LOGINLOG'] = "loginlog";
define('NEW_LINE_CHAR',"\r\n");


return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'PRC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'contentOldTags' => ['<!--{', '}-->'],
    'contentTags' => ['{{', '}}'],

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

    ],

];
