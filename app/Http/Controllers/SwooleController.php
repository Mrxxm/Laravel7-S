<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;

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

}
