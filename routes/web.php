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

Route::get('/chat/index', 'SwooleController@chat');

Route::get('/mysql/co', 'SwooleController@mysqlCo');

Route::get('/co/run', 'SwooleController@coRun');
