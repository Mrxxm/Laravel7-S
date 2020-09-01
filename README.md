
### Laravel7 + S学习和使用

`swoole`学习

![](https://img9.doubanio.com/view/photo/l/public/p2617826535.jpg)

[laravelS-github](https://github.com/hhxsv5/laravel-s)

[学院君:Swoole从入门到实战](https://xueyuanjun.com/books/swoole-tutorial)

[Swoole官方文档](https://wiki.swoole.com/#/)


## ElasticSearch

1.安装
修改配置：

config/elasticsearch.yml

#集群配置
cluster.name: xxm-appliation
#节点设置
node.name: node-1
cluster.initial_master_nodes: ["node-1"] 

xpack.ml.enabled: false
network.host: 0.0.0.0
http.port: 8301

#memory
bootstrap.memory_lock: false
bootstrap.system_call_filter: false

#跨域
http.cors.enabled: true
http.cors.allow-origin: "*"

启动命令：

./bin/elasticsearch
./bin/elasticsearch -d 不输出信息
































2.安装elasticsearch-head

github地址：https://github.com/mobz/elasticsearch-head

Running with built in server

* git clone git://github.com/mobz/elasticsearch-head.git
* cd elasticsearch-head
* npm install
* npm run start
* open http://localhost:9100/

This will start a local webserver running on port 9100 serving elasticsearch-head




*    修改端口号(8401head端口号，8301监听es端口号)

Gruntfile.js


_site/app.js



* 启动

































3.分布式部署

配置：https://www.cnblogs.com/sanduzxcvbnm/p/11433741.html

踩坑：https://www.cnblogs.com/wxw16/p/6160186.html
























4.索引

* 新建索引

分片数一般是节点数的一到三倍。









5.创建

* 安装ik

./bin/elasticsearch-plugin install https://github.com/medcl/elasticsearch-analysis-ik/releases/download/v7.9.0/elasticsearch-analysis-ik-7.9.0.zip
# 卸载
./bin/elasticsearch-plugin remove analysis-ik

* 分词模式

k_max_word: 会将文本做最细粒度的拆分，比如会将“中华人民共和国国歌”拆分为“中华人民共和国,中华人民,中华,华人,人民共和国,人民,人,民,共和国,共和,和,国国,国歌”，会穷尽各种可能的组合，适合 Term Query；

ik_smart: 会做最粗粒度的拆分，比如会将“中华人民共和国国歌”拆分为“中华人民共和国,国歌”，适合 Phrase 查询。

* mapping

PUT my_index
{
  "settings": {
    "number_of_shards": 5,
    "number_of_replicas": 0
  },
  "mappings": {
    "properties": {
      "itemid": {
        "type": "long"
      },
      "catid": {
        "type": "long"
      },
      "title": {
        "type": "text"
      },
      "introduce": {
        "type": "text"
      },
      "price": {
        "type": "text"
      },
      "keyword": {
        "type": "text"
      },
      "content": {
        "type": "text",
        "analyzer": "ik_max_word",
        "search_analyzer": "ik_max_word"
      }
    }
  }
}
* 文档

{
  "itemid": 1,
  "catid": 1,
  "title": "求购1",
  "introduce": "拜耳腈纶/粘/棉50/30/20  32Ｓ/1 32/2，有现货最好 若无 可以定纺工厂也可以",
  "price": "999",
  "keyword": "求购信息,求购,面料"
}

{
  "settings": {
    "number_of_shards": 5,
    "number_of_replicas": 0
  },
  "mappings": {
    "properties": {
      "itemid": {
        "type": "long"
      },
      "catid": {
        "type": "long"
      },
      "title": {
        "type": "text"
      },
      "introduce": {
        "type": "text"
      },
      "hits": {
        "type": "long"
      },
       "thumb": {
        "type": "text"
      },
       "thumb1": {
        "type": "text"
      },
       "thumb2": {
        "type": "text"
      },
       "username": {
        "type": "text"
      },
       "groupid": {
        "type": "long"
      },
       "company": {
        "type": "text"
      },
       "vip": {
        "type": "long"
      },
       "truename": {
        "type": "text"
      },
       "mobile": {
        "type": "text"
      },
       "edittime": {
        "type": "long"
      },
       "editdate": {
        "type": "date"
      },
       "status": {
        "type": "long"
      },
       "isdaifa": {
        "type": "long"
      },
       "is_top": {
        "type": "long"
      },
      "agree": {
        "type": "long"
      },
      "content": {
        "type": "text",
        "analyzer": "ik_max_word",
        "search_analyzer": "ik_max_word"
      }
    }
  }
}






