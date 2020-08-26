<?php


namespace App\Utils\ES;


use Elasticsearch\ClientBuilder;

class ElasticSearch
{
    protected static $instance;

    public function __construct()
    {
        try {
            static::$instance = ClientBuilder::create()
                ->setHosts(['127.0.0.1:8301'])
                ->build();
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        if (empty(static::$instance)) {
            throw new \Exception('实例为空');
        }
    }

    public function search(array $params)
    {
        return static::$instance->search($params);
    }
}
