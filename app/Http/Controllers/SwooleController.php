<?php


namespace App\Http\Controllers;


use App\Services\WebSocketService;
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

    public function dxtMysql(Request $request)
    {
        $member = DB::table('destoon_member')
            ->select('userid', 'truename', 'company', 'mobile')
            ->where('userid', '=', '215363')
            ->first();
        echo "协程1" . PHP_EOL;
        print_r($member);
        return json(['data' => $member]);
    }

    public function chat(Request $request)
    {
        $data = $request->all();
        $msg = $data['msg'];
        $userId =  215363;
        $tUserId =  902;
        $msg = 'hello';
        if (empty($userId) || empty($tUserId)) {
            return json('用户不存在');
        }
        $chatId = $this->getChatId($userId, $tUserId);
        if (empty($chatId)) {
            return json('聊天室不存在');
        }

        $data = [
            'userId'  => $userId,
            'tUserId' => $tUserId,
            'msg'     => $msg,
            'chatId'  => $chatId
        ];

        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);//连接redis
        $server = $redis->get('Swoole:ws');
        $server->push(1, json_encode($data['msg']));
//        foreach ($server->connections as $fd) {
//            if (!$server->isEstablished($fd)) {
//                // 如果连接不可用则忽略
//                continue;
//            }
//             // 服务端通过 push 方法向所有连接的客户端发送数据
//        }

        $task = new \App\Jobs\Task\ChatTask($data);
        $success = \Hhxsv5\LaravelS\Swoole\Task\Task::deliver($task);
    }

    public function getChatId($userId, $tUserId)
    {
        $ins = [
            'userid' => $userId,
            't_userid' => $tUserId
        ];
        $result = DB::table('destoon_xcx_roomchat')
            ->where(function ($query) use($ins) {
                $query->where('userid',$ins['userid'])->where('t_userid',$ins['t_userid']);
            })
            ->orWhere(function($query) use($ins) {
                $query->where('userid',$ins['t_userid'])->where('t_userid',$ins['userid']);
            })
            ->orderby('create_time','desc')
            ->first();
        if (!$result){
            $chatId = DB::table('destoon_xcx_roomchat')->insertGetId($ins);
        }else{
            $chatId = $result->id;
        }

        return $chatId;
    }

}
