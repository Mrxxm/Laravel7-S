<?php
namespace App\Utils;
use Yii;
class ClientUtil {


    /**
     **	解析referer
     **/
    public static function parseReferer(){
        $referer = '/site/index/index';
        $refer = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if($refer){
            $parse = parse_url($refer);
            if(strpos($parse['host'], '.chinayarn.com')){
                $referer = $parse['path'];
            }
        }
        return $referer;
    }

    /*
    *获取IP信息
    */
    public static function getIp($toLong=0) {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } else {
            $ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
        }
        return $toLong==1?ip2long($ip):$ip;
    }

    /*
    *获取浏览器信息
    */
    public static function getBrowserInfo() {
        $browser_names = array('Firefox', 'MSIE', 'Opera', 'Netscape', 'Chrome');
        $replace_char = array('(', ')', ';', '/');
        $agent_info = str_replace($replace_char, ' ',
            $_SERVER['HTTP_USER_AGENT']);
        $agent_info = explode(' ', $agent_info);
        //init;
        $browser_info = array('name' => 'Unknown', 'version' => 'Unknown');
        foreach ($browser_names as $value_0) {
            foreach ($agent_info as $key => $value_1) {
                if (stristr($value_1, $value_0)) {
                    $browser_info['name'] = $value_1;
                    if ($value_0 == $value_1) {
                        $browser_info['version'] = ' ' . $agent_info[$key + 1];
                    }
                    if ($browser_info['name'] == 'MSIE')
                        $browser_info['name'] = 'Internet Explorer';
                    return $browser_info;
                }
            }
        }
        return $browser_info;
    }

    /**
     * 判断客户端是否为IE核心浏览器
     * @return boolean
     * @author zhangzhicheng
     */
    public static function isIE(){
        if(self::getBrowser()=='Internet Explorer')
            return true;
        return false;
    }


    /**
     * 获取浏览器名字
     * @return string
     * @author zhangzhicheng
     */
    public static function getBrowser() {
        $b = self::getBrowserInfo();
        return $b['name'];
    }
    /*
     * 功能:检测普通URL是否合法;
     * 返回值:匹配次数 or false
     */
    public static function checkUrl($url) {
        $result = preg_match(
            '/([\w-]+\.)+[\w-]+.([^a-z])(/[\w- ./?%&=]*)?|[a-zA-Z0-9\-\.][\w-]+.([^a-z])(/[\w- ./?%&=]*)?/i',
            $url);
        return $result;
    }
    /*
     * 功能:获取用户操作系统类型(简化版);
     * 返回:操作系统类型;
     */
    public static function getOS() {
        $default_os = 'Unknown';
        $os_types = array('Windows', 'Linux', 'Unix', 'Sun', 'FreeBSD',
            'NetBSD', 'BSD', 'Mac', 'IBM', 'IRIX', 'OSF1', 'HPUX', 'AIX',
            'PowerPC','Safari','MicroMessenger');
        $agent_info = $_SERVER['HTTP_USER_AGENT'];
        foreach ($os_types as $value) {
            if (stristr($agent_info, $value) != false) {
                return $value;
            }
        }
        return $default_os;
    }
    /*
     * 功能:获取客户端IP;
     * 返回:IP;
     */
    public static function getClientIP() {
        $ClinetIP = '*.*.*.*';
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $ClinetIP = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $ClinetIP = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $ClinetIP = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $ClinetIP = getenv("HTTP_X_FORWARDED_FOR");
            } elseif (getenv("HTTP_CLIENT_IP")) {
                $ClinetIP = getenv("HTTP_CLIENT_IP");
            } else {
                $ClinetIP = getenv("REMOTE_ADDR");
            }
        }
        return $ClinetIP;
    }

    /*
     * 获取客户端综合信息
     */
    public static function getClientInfos(){

    }

    //获取当前ip所在城市
    public static function GetIpLookup($ip = ''){
        if(empty($ip)){
            return '请输入IP地址';
        }
        $test= 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;
        $res = @file_get_contents($test);
        return $res;
    }


    /**
     * 根据IP获取地埋位置
     */
    public static function getIpAdress($ip='') {
        return Yii::$app->ip->getAdress($ip);
    }

    public static function getAddress($ip, $qqwryData='QQWry.Dat') {
//	    echo $ip;
        if(!$fd = fopen(app_path('Utils/QQWry.Dat'),'rb')) {
            return '- Invalid IP data file';
        }

        $ip = explode('.', $ip);
        $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

        if(!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4)) ) return;
        @$ipbegin = implode('', unpack('L', $DataBegin));
        if($ipbegin < 0) $ipbegin += pow(2, 32);
        @$ipend = implode('', unpack('L', $DataEnd));
        if($ipend < 0) $ipend += pow(2, 32);
        $ipAllNum = ($ipend - $ipbegin) / 7 + 1;

        $BeginNum = $ip2num = $ip1num = 0;
        $ipAddr1 = $ipAddr2 = '';
        $EndNum = $ipAllNum;

        while($ip1num > $ipNum || $ip2num < $ipNum) {
            $Middle= intval(($EndNum + $BeginNum) / 2);

            fseek($fd, $ipbegin + 7 * $Middle);
            $ipData1 = fread($fd, 4);
            if(strlen($ipData1) < 4) {
                fclose($fd);
                return '- System Error';
            }
            $ip1num = implode('', unpack('L', $ipData1));
            if($ip1num < 0) $ip1num += pow(2, 32);

            if($ip1num > $ipNum) {
                $EndNum = $Middle;
                continue;
            }

            $DataSeek = fread($fd, 3);
            if(strlen($DataSeek) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
            fseek($fd, $DataSeek);
            $ipData2 = fread($fd, 4);
            if(strlen($ipData2) < 4) {
                fclose($fd);
                return '- System Error';
            }
            $ip2num = implode('', unpack('L', $ipData2));
            if($ip2num < 0) $ip2num += pow(2, 32);

            if($ip2num < $ipNum) {
                if($Middle == $BeginNum) {
                    fclose($fd);
                    return '- Unknown';
                }
                $BeginNum = $Middle;
            }
        }

        $ipFlag = fread($fd, 1);
        if($ipFlag == chr(1)) {
            $ipSeek = fread($fd, 3);
            if(strlen($ipSeek) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
            fseek($fd, $ipSeek);
            $ipFlag = fread($fd, 1);
        }

        if($ipFlag == chr(2)) {
            $AddrSeek = fread($fd, 3);
            if(strlen($AddrSeek) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $ipFlag = fread($fd, 1);
            if($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if(strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return '- System Error';
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }

            while(($char = fread($fd, 1)) != chr(0))
                $ipAddr2 .= $char;

            $AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
            fseek($fd, $AddrSeek);

            while(($char = fread($fd, 1)) != chr(0))
                $ipAddr1 .= $char;
        } else {
            fseek($fd, -1, SEEK_CUR);
            while(($char = fread($fd, 1)) != chr(0))
                $ipAddr1 .= $char;

            $ipFlag = fread($fd, 1);
            if($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if(strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return '- System Error';
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }
            while(($char = fread($fd, 1)) != chr(0))
                $ipAddr2 .= $char;
        }
        fclose($fd);

        if(preg_match('/http/i', $ipAddr2)) {
            $ipAddr2 = '';
        }
        $ipaddr = "$ipAddr1";
        $ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
        $ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
        $ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
        if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
            $ipaddr = '- Unknown';
        }
        return mb_convert_encoding($ipaddr,'utf-8','gb2312');
    }

}
