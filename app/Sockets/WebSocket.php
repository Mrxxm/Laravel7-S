<?php


namespace App\Sockets;

use Hhxsv5\LaravelS\Swoole\Socket\WebSocket as WebSocketF;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Server\Port;
use Swoole\WebSocket\Frame;

class WebSocket extends WebSocketF
{
    public function __construct(Port $port)
    {
        parent::__construct($port);
    }

    public function onOpen(\Swoole\WebSocket\Server $server, Request $request)
    {
        echo $request->fd . "WebSocket-custom 连接建立\n";
        $server->push($request->fd, '欢迎与LaravelS-WebSocket-custom服务建立连接');
    }

    public function onMessage(\Swoole\WebSocket\Server $server, Frame $frame)
    {
        $server->push($frame->fd, 'WebSocket-custom' . $frame->data); // 服务端通过 push 方法向所有连接的客户端发送
    }

    public function onClose(\Swoole\WebSocket\Server $server, $fd, $reactorId)
    {
        echo $fd . "WebSocket-custom 连接关闭\n";
    }
}
