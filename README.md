# Introduction
秒杀系统api,基于Hyperf研发，使用了Jwt,RabbitMq,redis
# Requirements
 - PHP >= 7.2
 - Swoole PHP extension >= 4.4，and Disabled `Short Name`
 - OpenSSL PHP extension
 - JSON PHP extension
 - PDO PHP extension （If you need to use MySQL Client）
 - Redis PHP extension （If you need to use Redis Client）
 - Protobuf PHP extension （If you need to use gRPC Server of Client）
# Installation using Composer
$ cd path/to/install
$ php bin/hyperf.php start
$ php bin/hyperf.php migrate
$ php bin/hyperf.php db:seed
This will start the cli-server on port `9501`, and bind it to all network interfaces. You can then visit the site at `http://localhost:9501/`



