企业云盘是一个集协同办公、便倢分享、安全存储、在线预览、权限管理等功能为一体，专门用心服务百度同学的文档管理平台。
企业云盘php sdk库是为了方便百度产品快速接入文档管理，高效开发上线而开发的一个文档管理开放库。
目录结构说明：
   conf: 配置文件。目录结构自定义。ODP环境建议放在根目录的conf文件夹下。
   data: 单测/demo文档测试数据。
   demo: demo代码
   log: 日志文件路径。可以在conf/ecloud.conf中自定义
   src: 企业云盘php sdk源代码
   tests: sdk单测代码
   vendor: composer依赖库
   composer.json: composer依赖库版本
运行环境：
   php版本：php5.4.33~php7.1
   guzzlehttp版本: 6.3
   monolog版本：1.23
   phpunit: 7.4
demo运行：
	php demo/Example.php
单测运行：
    ./vendor/bin/phpunit tests/EcloudClientTest.php
企业云盘配置参数申请接口人：
	heying05@baidu.com,gongyu@baidu.com,chenhongwei01@baidu.com
   
   
   