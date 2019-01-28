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

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['namespace' => 'admin'], function ($router) {
    $router->get('mainpage','AdminController@mainpage');
});

Route::group(['namespace' => 'Hostbackend', 'prefix' => 'host'], function ($router) {
    $router->get('list','HostController@list');
    $router->post('edit','HostController@edit');
    $router->post('add', 'HostController@add');
});

Route::group(['namespace' => 'Project', 'prefix' => 'project'], function ($router) {
    $router->get('list','ProjectController@list');
    $router->post('edit','ProjectController@edit');
    $router->post('add', 'ProjectController@add');
});

Route::group(['namespace' => 'Template', 'prefix' => 'template'], function ($router) {
    $router->get('list','TemplateController@list');
    $router->post('edit','TemplateController@edit');
    $router->post('add', 'TemplateController@add');
});

Route::group(['namespace' => 'Tempdef', 'prefix' => 'tempdef'], function ($router) {
    $router->get('list','TempdefController@list');
    $router->post('edit','TempdefController@edit');
    $router->post('add', 'TempdefController@add');
});

Route::group(['namespace' => 'Document', 'prefix' => 'document'], function ($router) {
    $router->get('list','DocumentController@list');
    $router->post('edit','DocumentController@edit');
    $router->post('add', 'DocumentController@add');
});

Route::group(['namespace' => 'Schedule', 'prefix' => 'schedule'], function ($router) {
    $router->get('list','ScheduleController@list');
    $router->post('edit','ScheduleController@edit');
    $router->post('add', 'ScheduleController@add');
});
