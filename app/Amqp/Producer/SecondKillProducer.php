<?php

declare(strict_types=1);

namespace App\Amqp\Producer;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessage;

/**
 * @Producer(exchange="skill", routingKey="skill")
 */
class SecondKillProducer extends ProducerMessage
{
    public function __construct($data)
    {
        $this->payload = $data;
    }
}