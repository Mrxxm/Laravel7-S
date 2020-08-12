<?php


namespace App\Http\Controllers;


class WebSocketController
{
    public function push()
    {
        $swoole = app('swoole');
        $ports = $swoole->ports;
       var_dump(json_decode(json_encode($swoole), true));
        foreach ($swoole->connections as $_fd) {
            if ($swoole->exist($_fd) && $swoole->isEstablished($_fd)) {
                $swoole->push($_fd, "this is server push");
            }
        }
    }
}
