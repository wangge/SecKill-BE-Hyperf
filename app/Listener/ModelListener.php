<?php

declare(strict_types=1);

namespace App\Listener;

use App\Service\LogService;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Database\Model\Events\Saved;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\Annotation\Listener;
use Psr\Container\ContainerInterface;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener
 */
class ModelListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @Inject()
     * @var LogService
     */
    private $log;


    /**
     * ModelListener constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 监听事件
     * @return array|string[]
     */
    public function listen(): array
    {
        return [
            Saved::class
        ];
    }

    public function process(object $event)
    {
        if ($event instanceof Event) {
            $model = $event->getModel();
            $this->log->get()->debug(json_encode($model));
        }
    }
}
