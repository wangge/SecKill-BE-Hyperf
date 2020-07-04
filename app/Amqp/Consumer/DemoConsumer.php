<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use App\Service\LogService;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Amqp\Result;
use Hyperf\Di\Annotation\Inject;

/**
 * @Consumer(exchange="hyperf", routingKey="hyperf", queue="hyperf", nums=1)
 */
class DemoConsumer extends ConsumerMessage
{

    /**
     * @Inject()
     * @var LogService
     */
    protected $log;
    public function consume($data): string
    {
        print_r($data);
        $this->log->get("rabbitmq")->info(json_encode($data));
        return Result::ACK;
    }
}
