<?php


namespace App\Service;


use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;

class RedisSevice
{
    public function get(){
        return ApplicationContext::getContainer()->get(Redis::class);
    }
}