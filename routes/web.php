<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/admin/login');
});

// Blade::setContentTags('<!--{', '}-->'); // 不可用
Auth::routes(); // 暂时隐藏外网用户注册、找回密码等


Route::group(['namespace' => 'Admin', 'prefix'=>'admin'], function (){
    //管理员登录、注销 （暂无注册）
    Route::get('login','LoginController@showLoginForm')->name('admin.loginForm');
    Route::post('login','LoginController@loginAdmin')->name('admin.login');
    Route::get('logout','LoginController@logout')->name('admin.logout');
    Route::get('mainpage','AdminController@mainpage')->name('admin.mainpage');
    Route::get('frmMainMenu','AdminController@frmMainMenu')->name('admin.frmMainMenu');
    Route::get('GetProjectListJS/pt/{pt}/node/{node}','AdminController@GetProjectListJS')->name('admin.GetProjectListJS');
});


Route::group(['namespace' => 'Admin'], function ($router) {
    Route::get('/home', 'HomeController@index')->name('home');

});

Route::group(['namespace' => 'Hostbackend', 'prefix' => 'host'], function ($router) {
    $router->get('list','HostController@list')->name('hostlist');
    $router->post('edit','HostController@edit')->name('hostedit');
    $router->post('add', 'HostController@add')->name('hostadd');
});

Route::group(['namespace' => 'Project', 'prefix' => 'project'], function ($router) {
    $router->get('list','ProjectController@list')->name('projectlist');
    $router->post('edit','ProjectController@edit')->name('projectedit');
    $router->post('add', 'ProjectController@add')->name('projectadd');
});

Route::group(['namespace' => 'Template', 'prefix' => 'template'], function ($router) {
    $router->get('list','TemplateController@list')->name('templatelist');
    $router->post('edit','TemplateController@edit')->name('templateedit');
    $router->post('add', 'TemplateController@add')->name('templateadd');
});

Route::group(['namespace' => 'Tempdef', 'prefix' => 'tempdef'], function ($router) {
    $router->get('list','TempdefController@list')->name('tempdeflist');
    $router->post('edit','TempdefController@edit')->name('tempdefedit');
    $router->post('add', 'TempdefController@add')->name('tempdefadd');
});

Route::group(['namespace' => 'Document', 'prefix' => 'document'], function ($router) {
    $router->get('list','DocumentController@list')->name('documentlist');
    $router->post('edit','DocumentController@edit')->name('documentedit');
    $router->post('add', 'DocumentController@add')->name('documentadd');
});

Route::group(['namespace' => 'Schedule', 'prefix' => 'schedule'], function ($router) {
    $router->get('list','ScheduleController@list')->name('schedulelist');
    $router->post('edit','ScheduleController@edit')->name('scheduleedit');
    $router->post('add', 'ScheduleController@add')->name('scheduleadd');
});
