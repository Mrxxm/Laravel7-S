<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController
{

    public function login(Request $request)
    {
        $data = $request->all();

        try {
            // ip检测
            $this->ipCheck($_SERVER["REMOTE_ADDR"]);

        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        go(function () use ($data) {
            $redisCo = $this->redisCoClient();
            // 版本记录
            $this->saveXcxVersion($redisCo, $data);
        });

        // 获取openid
        $userInfo = $this->getOpenid($data['code']);
        $openId = $userInfo['openid'];

        // 登录记录
        $value = [
            'server' => $_SERVER,
            'userInfo' => $userInfo,
            'data' => $data,
            'time' => time()
        ];
        $task = new \App\Jobs\Task\LoginTask($value);
        $success = \Hhxsv5\LaravelS\Swoole\Task\Task::deliver($task);

        $redis = $this->redisClient();
        $redisUserInfo = $redis->get('User:user_info_' . $openId);

        if (!empty($redisUserInfo)) {
            $redisUserInfo = unserialize($redisUserInfo);
            if (empty($user) || empty($user->unionid) || empty($user->avatarUrl) || empty($user->gender) || empty($user->passport) || $redisUserInfo->groupid <> $user->groupid ) {
                $redis->del('User:user_info_' . $openId);
                try {
                    $redisUserInfo = $this->execLogin($data, $userInfo);
                } catch (\Exception $exception) {
                    throw new \Exception($exception->getMessage());
                }
            }
            return json($redisUserInfo);
        } else {
            try {
                $redisUserInfo = $this->execLogin($data, $userInfo);
            } catch (\Exception $exception) {
                throw new \Exception($exception->getMessage());
            }
            return json($redisUserInfo);
        }
    }

    public function ipCheck($ip)
    {
        $is_ban = DB::table('destoon_banip')
            ->where('ip', $ip)
            ->count();
        if ($is_ban) {
            throw new \Exception('ip禁止访问');
        }
    }

    public function redisClient()
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);//连接redis

        return $redis;
    }

    public function redisCoClient()
    {
        $redis = new \Swoole\Coroutine\Redis();
        $redis->connect('127.0.0.1',6379);//连接redis

        return $redis;
    }

    public function saveXcxVersion($redis, $data)
    {
        if (!empty($data['currentVersion']) && !empty($data['mobile']) && !empty($data['codeVersion'])) {
            $mobile = $data['mobile'];
            $version = $data['currentVersion'];
            $codeVersion = $data['codeVersion'];
            $xcxVersion = [
                $mobile,
                $version,
                $codeVersion
            ];
            $redis->set("Version:$mobile", $xcxVersion);
        }
    }

    public function getOpenid($code)
    {
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . self::$AppId . '&secret=' . self::$AppSecret . '&js_code=' . $code . '&grant_type=authorization_code';

        $apiData = file_get_contents($url);
        $apiData = json_decode($apiData,true);//对json数据解码

        return $apiData;
    }

    public function execLogin($data, $userInfo)
    {
        $openId = $userInfo['openid'];
        $unionId = $userInfo['unionid'] ?? '';

        $member = DB::table('destoon_member')
            ->where('wx_openId', '=', $openId)
            ->first();
        if (!$member) {
            $insert = [];
            $insert['wx_openId'] = $openId;
            $userId = DB::table('destoon_member')->insertGetId($insert);
            $upd = [];
            $upd['username']  = 'xcx' . $userId;
            $upd['unionid']   = $unionId;
            $upd['passport']  = $data['nickName'];
            $upd['gender']    = $data['gender'];
            $upd['avatarUrl'] = $data['avatarUrl'];
            $upd['c_from']    = 2;
        } else {
            $upd = [];
            $upd['unionid'] = $unionId;
            $upd['passport'] = $data['nickName'];
            $upd['gender']  = $data['gender'];
            if (!$member->avatarUrl){
                $upd['avatarUrl']  = $data['avatarUrl'];
            }
        }
        DB::table('destoon_member')
            ->where('wx_openId', '=', $openId)
            ->update($upd);

        $user = DB::table('destoon_member')
            ->where('wx_openId', '=', $openId)
            ->first();

        if ($user) {
            try {
                $company = $this->checkCompany($user);
            } catch (\Exception $exception) {
                throw new \Exception($exception->getMessage());
            }
            $user->companyInfo = $company;
        }

        go(function () use ($user) {
            $this->updMessage($user);
            $this->saveMisc($user);
        });

        $_token = $this->createToken($user);
        $user->_token = $_token;
        if ($user->groupid > '6') {
            $user->groupid = '6';
        }

        $redis = $this->redisClient();
        $redis->set('User:user_info_' . $openId, serialize($user), config('redis.user_info_expire'));

        return $user;
    }

    public function updMessage($user)
    {
        $message = DB::table('destoon_message')
            ->where('touser', '=', $user->username)
            ->where("isread",'=',"0") //未读
            ->count();
        $upd = [];
        $upd['message'] = $message;

        DB::table('destoon_member')
            ->where('userid', '=', $user->userid)
            ->update($upd);
    }

    public function saveMisc($user)
    {
        $misc = DB::table('destoon_member_misc')
            ->where('userid', '=', $user->userid)
            ->first();
        if (!$misc) {
            $insert = [];
            $insert['userid'] = $user->userid;
            $insert['username'] = $user->username;
            DB::table('destoon_member_misc')
                ->insert($insert);
        }
    }

    public function getTokenLifetimeTimeStamp()
    {
        $now = time();
        $nextDay = $now + 864000;

        return $nextDay;
    }

    public function createToken($user)
    {
        $userId = $user->userid;
        $jsonstr = json_encode(['userid' => $userId, 'lifetime' => $this->getTokenLifetimeTimeStamp()]);
        $_token = base64_encode($jsonstr);

        return $_token;
    }

    public function saveCompany($user)
    {
        $insert = [];
        $insert['userid'] = $user->userid;
        $insert['username'] = $user->username;
        $userId = DB::table('destoon_company')
            ->insertGetId($insert);
        return DB::table('destoon_company')
            ->where('userid', '=', $userId)
            ->first();
    }

    public function checkCompany($user)
    {
        $company = DB::table('destoon_company')
            ->where('userid', '=', $user->userid)
            ->first();

        if (!$company) {
            $company = $this->saveCompany($user);
        }

        $black_company = DB::table('ims_mofei_qunliao_black_list')->where('type', 'company')->pluck('keywords')->toArray();
        $black_company = array_column($black_company, 'keywords');

        if (in_array($company->company, $black_company)) {
            throw new \Exception('系统错误2,请联系客服');
        }
        if (mb_strlen($company->company) < 2 && mb_strlen($company->company) > 0) {
            throw new \Exception('系统错误3,请联系客服');
        }
        if (preg_match('/^[a-zA-Z0-9]+$/u', $company->company)) {
            throw new \Exception('系统错误4,请联系客服');
        }

        return $company;
    }
}
