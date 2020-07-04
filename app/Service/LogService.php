<?php


namespace App\Service;

use Psr\Container\ContainerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Logger\Logger;
use Hyperf\Utils\ApplicationContext;
class LogService
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        // 第一个参数对应日志的 name, 第二个参数对应 config/autoload/logger.php 内的 key
        $this->logger = $loggerFactory->get('log', 'default');
    }

    public static function get(string $name = 'app')
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name);
    }

}