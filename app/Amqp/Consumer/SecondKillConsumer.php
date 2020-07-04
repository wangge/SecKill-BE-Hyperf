<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use App\Model\Good;
use App\Model\Order;
use Hyperf\Amqp\Result;
use Hyperf\Amqp\Annotation\Consumer;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\DbConnection\Db;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @Consumer(exchange="skill", routingKey="skill", queue="skill", name ="SecondKillConsumer", nums=1)
 */
class SecondKillConsumer extends ConsumerMessage
{
    public function consumeMessage($data, AMQPMessage $message): string
    {
        //Order::create($data);
        //事务出入订单，修改库存
        Db::transaction(function () use($data){
            Order::create([
                'user_id'=>$data['user_id'],
                "goods_id" =>$data['goods_id']
            ]);
            Db::update('UPDATE goods set store = store-1 WHERE id = ?', [$data['goods_id']]);
        });

        return Result::ACK;
    }
}
