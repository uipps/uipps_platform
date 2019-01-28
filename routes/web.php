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
    return 'welcome here!';
});

Auth::routes();

Route::group(['namespace' => 'admin'], function ($router) {
    Route::get('/home', 'HomeController@index')->name('home');
    $router->get('mainpage','AdminController@mainpage')->name('mainpage');

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
