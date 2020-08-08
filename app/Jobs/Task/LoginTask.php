<?php


namespace App\Jobs\Task;


use App\Utils\ClientUtil;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\DB;

class LoginTask extends Task
{
    // 待处理任务数据
    private $server;
    private $userInfo;
    private $time;

    // 任务处理结果
    private $result;

    public function __construct($value)
    {
        $this->server   = $value['server'];
        $this->userInfo = $value['userInfo'];
        $this->time     = $value['time'];
    }

    public function handle()
    {
        echo __CLASS__ . ': 开始处理任务' . "\n";

        $member = DB::table('destoon_member')
            ->where('wx_openId', '=', $this->userInfo['openid'])
            ->select('userid', 'username', 'passsalt', 'password')
            ->first();
        $userId = $member->userid;
        $clientIp = $this->server["REMOTE_ADDR"];
        $agent = $this->server['HTTP_USER_AGENT'];

        $ipAddress = ClientUtil::getAddress($clientIp);

        $upd = [];
        $upd['loginip']   = $clientIp;
        $upd['logintime'] = $this->time;
        $upd['ip_address'] = $ipAddress;

        DB::table('destoon_member')
            ->where('userid', '=', $userId)
            ->update($upd);

        DB::table('destoon_member')
            ->where('userid', '=', $userId)
            ->increment("logintimes");

        $insert['admin']     = 0;
        $insert['agent']     = $agent;
        $insert['userid']    = $userId;
        $insert['loginip']   = $clientIp;
        $insert['logintime'] = $this->time;
        $insert['username']  = $member->username;
        $insert['passsalt']  = $member->passsalt;
        $insert['password']  = $member->password ?? '';
        $insert['message']   = "小程序登录成功";
        $loginRecordId = DB::table("destoon_login")
            ->insertGetId($insert);

        $this->result = 'The result of ' . $loginRecordId . "\n";
    }

    public function finish()
    {
        echo __CLASS__ . ': 任务处理完成' . $this->result . "\n";
    }
}
