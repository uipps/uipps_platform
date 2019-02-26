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
    Route::get('GetTemplateListJS','AdminController@GetTemplateListJS')->name('admin.GetTemplateListJS');
});


Route::group(['namespace' => 'Admin'], function ($router) {
    Route::get('/home', 'HomeController@index')->name('home');
    Route::any('/password/edit', 'PasswordEditController@execute')->name('home');

});

Route::group(['namespace' => 'Hostbackend', 'prefix' => 'host'], function ($router) {
    $router->get('list','HostController@list')->name('hostlist');
    $router->post('edit','HostEditController@execute')->name('hostedit');
    $router->post('add', 'HostAddController@execute')->name('hostadd');
});

Route::group(['namespace' => 'Project', 'prefix' => 'project'], function ($router) {
    $router->get('list','ProjectController@list')->name('projectlist');
    $router->any('edit','ProjectEditController@execute')->name('projectedit');
    $router->any('add', 'ProjectAddController@execute')->name('projectadd');
});

Route::group(['namespace' => 'Template', 'prefix' => 'template'], function ($router) {
    $router->get('list','TemplateController@list')->name('templatelist');
    $router->any('edit','TemplateEditController@execute')->name('templateedit');
    $router->any('add', 'TemplateAddController@execute')->name('templateadd');
});

Route::group(['namespace' => 'Tempdef', 'prefix' => 'tempdef'], function ($router) {
    $router->get('list','TempdefController@list')->name('tempdeflist');
    $router->any('edit','TempdefEditController@execute')->name('tempdefedit');
    $router->any('add', 'TempdefAddController@execute')->name('tempdefadd');
});

Route::group(['namespace' => 'Document', 'prefix' => 'document'], function ($router) {
    $router->get('list','DocumentController@list')->name('documentlist');
    $router->any('edit','DocumentEditController@execute')->name('documentedit');
    $router->any('add', 'DocumentAddController@execute')->name('documentadd');
});

Route::group(['namespace' => 'Schedule', 'prefix' => 'schedule'], function ($router) {
    $router->get('list','ScheduleController@list')->name('schedulelist');
    $router->any('edit','ScheduleEditController@execute')->name('scheduleedit');
    $router->any('add', 'ScheduleAddController@execute')->name('scheduleadd');
});


