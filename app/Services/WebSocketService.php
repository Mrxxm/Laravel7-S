<?php


namespace App\Services;

use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Illuminate\Support\Facades\Log;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

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
        echo 'WebSocket 连接建立';
        $server->push($request->fd, '欢迎与LaravelS-WebSocket服务建立连接');
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
        // 调用 push 方法向客户端推送数据
        var_dump($frame);
        $server->push($frame->fd, '这是一条来自后台WebSocket服务器推送的消息 ' . date('Y-m-d H:i:s'));
    }

    // 关闭连接时触发
    public function onClose(Server $server, $fd, $reactorId)
    {
        echo 'WebSocket 连接关闭';
//        Log::info('WebSocket 连接关闭');
    }
}
