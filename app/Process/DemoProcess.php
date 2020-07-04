<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Hyperf\Contract\StdoutLoggerInterface;

/**
 * 还可以通过配置文件注册，此处是通过注解注册
 * @Process(name="demo_process")
 */
class DemoProcess extends AbstractProcess
{

    //用于监控失败队列数量的子进程，当失败队列有数据时，报出警告。
    public function handle(): void
    {
        $logger = $this->container->get(StdoutLoggerInterface::class);

        while (true) {
            $redis = $this->container->get(\Redis::class);
            $count = $redis->llen('queue:failed');
            $logger->info('listen queue failure. ');
            if ($count > 0) {
                $logger->warning('The num of failed queue is ' . $count);
            }

            sleep(10000);
        }
    }
}
