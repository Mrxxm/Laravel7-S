<?php


namespace App\Http\Controllers;


use Co\MySQL;
use Illuminate\Database\MySqlConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SwooleController
{
    public function timer(Request $request)
    {
        $count = 0;
        \Swoole\Timer::tick(1000, function ($timerId, $count) {
            global $count;
            echo "Swoole 很棒\n";
            $count++;
            if ($count == 3) {
                \Swoole\Timer::clear($timerId);
            }
        }, $count);
    }

    public function task(Request $request)
    {
        $task = new \App\Jobs\Task\TestTask('异步任务1');
        $success = \Hhxsv5\LaravelS\Swoole\Task\Task::deliver($task);  // 异步投递任务，触发调用任务类的 handle 方法

        $data = ['task' => 'success'];
        return json($data);
    }

    public function event(Request $request)
    {
        $data = $request->all();

        return json($data);
    }

    public function event1(Request $request)
    {
        $event = new \App\Events\TestEvent('测试异步事件监听及处理');

        $listener = $event->getListeners();

        $success = \Hhxsv5\LaravelS\Swoole\Task\Event::fire($event);

        $data = ['event' => $success];
        return json($data);
    }

    public function mysql(Request $request)
    {
        \Swoole\Runtime::enableCoroutine(true);
        echo 1;
        go(function () {

            $ret = DB::table('banner')
                ->where('id', 1)
                ->get();

            echo print_r($ret);
//            return response()->json(print_r($ret));
        });
        echo 2;
    }

}
