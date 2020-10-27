<?php


namespace App\Utils\OS;

use OpenSearch\Client\OpenSearchClient;
use OpenSearch\Client\SearchClient;
use OpenSearch\Util\SearchParamsBuilder;

class OpenSearch
{
    private $accessKeyId = '';

    private $secret = '';

    private $endPoint = '';

    private $appName = '';
    // 替换为下拉提示名称
    private $suggestName = '';

    private $options = ['debug' => true];

    private $instance = NULL;

    public function __construct()
    {
        $this->accessKeyId = config('aliyun.access_key_id');
        $this->secret      = config('aliyun.access_key_secret');
        $this->endPoint    = config('aliyun.end_point');

        try {
            $this->instance = new OpenSearchClient($this->accessKeyId, $this->secret, $this->endPoint, $this->options);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        if (empty($this->instance)) {
            throw new \Exception('实例为空');
        }

        return $this->instance;
    }

    // 简单搜索
    public function easySearch()
    {
//        header("Content-Type:text/html;charset=utf-8");

        // 实例化一个搜索类
        $searchClient = new SearchClient($this->instance);
        // 实例化一个搜索参数类
        $params = new SearchParamsBuilder();
        // 设置config子句的start值
        $params->setStart(0);
        // 设置config子句的hit值
        $params->setHits(20);
        // 指定一个应用用于搜索
        $params->setAppName('替换为应用名');
        // 指定搜索关键词
        $params->setQuery("name:'搜索'");
        // 指定返回的搜索结果的格式为json
        $params->setFormat("fulljson");
        //添加排序字段
        $params->addSort('RANK', SearchParamsBuilder::SORT_DECREASE);
        // 执行搜索，获取搜索结果
        $ret = $searchClient->execute($params->build());
        // 将json类型字符串解码
        print_r(json_decode($ret->result,true));
        // 打印调试信息
        dd($ret->traceInfo->tracer);
        return $ret->traceInfo->tracer;
    }

}
