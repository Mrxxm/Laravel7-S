<?php


namespace App\Jobs\Task;


use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\DB;

class ChatTask extends Task
{
    // 待处理任务数据
    private $data;

    // 任务处理结果
    private $result;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        echo __CLASS__ . ': 开始处理任务' . "\n";

        $ins['userid'] = intval($this->data['userId']);
        $ins['t_userid'] = intval($this->data['tUserId']);
        $ins['chatid'] = intval($this->data['chatId']);
        $ins['msg'] = $this->data['msg'];
        $ins['state'] = 0;
        $ins['create_time'] = strtotime('now');
        $ins['type'] = 0;

        $chatMsgId = DB::table('destoon_xcx_chatmsg')
            ->insertGetId($ins);


        $this->result = 'The result of ' . $chatMsgId . "\n";
    }

    public function finish()
    {
        echo __CLASS__ . ': 任务处理完成' . $this->result . "\n";
    }
}
