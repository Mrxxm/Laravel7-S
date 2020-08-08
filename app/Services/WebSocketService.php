<?php


namespace App\Services;

use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

//\Swoole\Runtime::enableCoroutine(true);

class WebSocketService implements WebSocketHandlerInterface
{
    public function __construct()
    {

    }

    // 连接建立时触发
    public function onOpen(Server $server, Request $request)
    {
        // 在触发 WebSocket 连接建立事件之前，Laravel 应用初始化的生命周期已经结束，你可以在这里获取 Laravel 请求和会话数据
        // 调用 push 方法向客户端推送数据，fd 是客户端连接标识字段
//        Log::info('WebSocket 连接建立');
        echo "WebSocket 连接建立\n";
        $server->push($request->fd, '欢迎与LaravelS-WebSocket服务建立连接');

        $chatId = $request->get['chatId'];

        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);//连接redis

        $chats = $redis->get("Swoole:chat");
        $chats = json_decode($chats,true);
        if (empty($chats)) {
            echo 1;
            $chats = [];
            $chats[] = [
                'fds'     => [$request->fd],
                'chatId' => $chatId
            ];
        } else {
            echo 2;
            foreach ($chats as &$chat) {
                if ($chat['chatId'] == $chatId) {
                    $chat['fds'] = array_merge($chat['fds'], [$request->fd]);
                }
            }
        }
        $redis->set('Swoole:chat',json_encode($chats));
    }

    // 收到消息时触发
    /*
     * $frame
     * ["fd"]=>int(1)
     * ["data"]=>string(8) "xxm: 666"
     * ["opcode"]=>int(1)
     * ["flags"]=>int(33)
     * ["finish"]=>bool(true)
     */
    public function onMessage(Server $server, Frame $frame)
    {
        $data = explode(',', $frame->data);
        if (count($data) != 3) {
            return ;
        }
        $userId =  $data[0];
        $tUserId =  $data[1];
        $msg = 'hello';
        if (empty($userId) || empty($tUserId)) {
            return ;
        }
        $chatId = $this->getChatId($userId, $tUserId);
        if (empty($chatId)) {
            return ;
        }

        $data = [
            'userId'  => $userId,
            'tUserId' => $tUserId,
            'msg'     => $data[2],
            'chatId'  => $chatId
        ];

        $res = [
            'userid' => $userId,
            'msg' => $data['msg'],
        ];

        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);//连接redis
        $chats = $redis->get("Swoole:chat");
        $chats = json_decode($chats,true);

        $fds = [];
        foreach ($chats as $chat) {
            if ($chat['chatId'] == $chatId) {
                $fds = $chat['fds'];
            }
        }

        if (!empty($fds)) {
            foreach ($fds as $fd) {
                if ($server->isEstablished($fd)) {
                    $server->push($fd, json_encode($res)); // 服务端通过 push 方法向所有连接的客户端发送数据
                }
            }
        }

        $task = new \App\Jobs\Task\ChatTask($data);
        $success = \Hhxsv5\LaravelS\Swoole\Task\Task::deliver($task);
    }

    // 关闭连接时触发
    public function onClose(Server $server, $fd, $reactorId)
    {
        echo "WebSocket 连接关闭\n";
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);//连接redis
        $chats = $redis->get("Swoole:chat");
        $chats = json_decode($chats,true);

        foreach ($chats as $num => &$chat) {
            if (in_array($fd, $chat['fds'])) {
                foreach ($chat['fds'] as $key => $value) {
                    if ($fd == $value) {
                        unset($chat['fds'][$key]);
                    }
                }
            }
            if (empty($chat['fds'])) {
                unset($chats[$num]);
            }
        }
        $redis->set("Swoole:chat", json_encode($chats));
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
