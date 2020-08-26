<?php


namespace App\Utils\ES;


use Elasticsearch\ClientBuilder;

class ElasticSearch
{
    protected $instance;

    public function __construct()
    {
        try {
            $this->instance = ClientBuilder::create()
                ->setHosts(['127.0.0.1:8301'])
                ->build();
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }

        if (empty($this->instance)) {
            throw new \Exception('实例为空');
        }
    }

    public function search(array $params)
    {
        return $this->instance->search($params);
    }
}
