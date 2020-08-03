<?php

namespace App\Listeners;

use App\Events\TestEvent;
use Hhxsv5\LaravelS\Swoole\Task\Event;
use Hhxsv5\LaravelS\Swoole\Task\Listener;
use Illuminate\Support\Facades\Log;

class TestEventListener extends Listener
{
    protected $event = null;

    public function __construct(Event $event)
    {
        parent::__construct($event);
        $this->event = $event;
    }

    public function handle()
    {
        echo __CLASS__ . ': 开始处理' . $this->event->getData() . "\n";
        Log::info(__CLASS__ . ': 开始处理', [$this->event->getData()]);
        sleep(3);// 模拟耗时代码的执行
        echo __CLASS__ . ': 处理完毕' . "\n";
        Log::info(__CLASS__ . ': 处理完毕');
    }
}
