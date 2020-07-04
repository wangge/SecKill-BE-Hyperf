<?php
namespace App\Task;

use App\Service\LogService;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Coroutine;

/**
 * @Crontab(name="Foo", rule="*\/10 * * * *", callback="execute", memo="这是一个示例的定时任务2")
 */
class TestTask
{

    /**
     * @Inject()
     * @var LogService
     */
    private $logger;

    public function execute()
    {
        $this->logger->get('task')->info("annotation crontab task".date('Y-m-d H:i:s', time()));
    }
    public function configExecute(){
        $this->logger->get('task')->info("config crontab task".date('Y-m-d H:i:s', time()));
    }

    public function handle($cid)
    {
        return [
            'worker.cid' => $cid,
            // task_enable_coroutine 为 false 时返回 -1，反之 返回对应的协程 ID
            'task.cid' => Coroutine::id(),
        ];
    }
}
