<?php


namespace App\Http\Controllers;


use Illuminate\Http\Response;
use Mrxxm\Scanner\Scanner;

class ScannerController
{
    public function index(Response $response)
    {
        $urls = [
            'www.baidu.com',
            'www.baidddddd'.com,
            'www.qq.com'
        ];

        $obj = new Scanner($urls);
        $result = $obj->getInvalidUrld();

        return json($result);
    }
}
