<?php


namespace App\Http\Controllers;


use App\Utils\ES\ES;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;

class ESController
{
    public function get(Request $request)
    {
        $data = $request->all();

        $params = [
            "index"  => 'buy_index',
            "type"   => 'buy',
            'id'     => '1aTPKHQBB5LHhX_I5dSa'
        ];

        $client = ClientBuilder::create()
            ->setHosts(['127.0.0.1:8301'])
            ->build();
        $result = $client->get($params);

        return response()->json($result);
    }

    public function search(Request $request)
    {
        $data = $request->all();

        $params = [
            "index"  => 'buy_index',
            "type"   => 'buy',
            "body"   => [
                "query" => [
                    "match" => [
                        "introduce" => $data['introduce']
                    ]
                ]
            ]
        ];

        $client = ClientBuilder::create()
            ->setHosts(['127.0.0.1:8301'])
            ->build();
        $result = $client->search($params);

        return response()->json($result);
    }

    public function searchPhrase(Request $request)
    {
        $data = $request->all();

        $params = [
            "index"  => 'buy_index',
            "type"   => 'buy',
            "body"   => [
                "query" => [
                    "match_phrase" => [
                        "introduce" => $data['introduce']
                    ]
                ]
            ]
        ];

        $client = ClientBuilder::create()
            ->setHosts(['127.0.0.1:8301'])
            ->build();
        $result = $client->search($params);

        return response()->json($result);
    }

    public function ContainerES(Request $request)
    {
        $data = $request->all();

        $params = [
            "index"  => 'buy_index',
            "type"   => 'buy',
            "body"   => [
                "query" => [
                    "match_phrase" => [
                        "introduce" => $data['introduce']
                    ]
                ]
            ]
        ];

        $result =  ES::search($params);

        return response()->json($result);
    }
}
