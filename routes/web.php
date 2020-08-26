<?php

use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});

// 定时器
Route::get('/timer/test', 'SwooleController@timer');

// 异步task任务
Route::get('/task/test', 'SwooleController@task');

// 事件监听
Route::get('/event/test', 'SwooleController@event');
Route::get('/event/test1', 'SwooleController@event1');

// 自定义组件-扫描
Route::get('/scanner/index', 'ScannerController@index');

// 异步mysql客户端
Route::get('/mysql/index', 'SwooleController@mysql');
// 异步redis客户daunt
Route::get('/redis/index', 'SwooleController@redis');

// mysql连接测试库
Route::get('/mysql/dxt', 'SwooleController@dxtMysql');
// 聊天室onOpen方法
Route::get('/chat/index', 'SwooleController@chat');
// mysql-协程
Route::get('/mysql/co', 'SwooleController@mysqlCo');
// 协程
Route::get('/co/run', 'SwooleController@coRun');
// 大市场小程序登录接口
Route::any('/user/login', 'LoginController@login');
// 协程-管道pop方法挂起等待
Route::any('/co/add', 'SwooleController@add');
Route::any('/co/add1', 'SwooleController@add1');

Route::any('/mysql/test', 'SwooleController@mysqlTest');
// 管道
Route::any('/channel/test', 'SwooleController@channel');
// 退出
Route::any('/exit/test', 'SwooleController@exit');


// webSock 接口推送
Route::any('/webSocket/push', 'WebSocketController@push');

// elasticsearch
Route::any('/es/get', 'ESController@get');
Route::any('/es/search', 'ESController@search');
Route::any('/es/searchPhrase', 'ESController@searchPhrase');
Route::any('/es/ContainerES', 'ESController@ContainerES');




