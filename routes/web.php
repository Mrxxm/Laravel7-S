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
