<?php
namespace App\Task;
use App\Service\LogService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Task\Annotation\Task;
use Hyperf\Utils\Coroutine;

class AnnotationTask
{
    /**
     * @Inject()
     * @var LogService
     */
    private $log;
    public function handle($cid)
    {
        $this->log->get("task1")->info(date('YmdHis'));
        return [
            'worker.cid' => $cid,
            // task_enable_coroutine=false 时返回 -1，反之 返回对应的协程 ID
            'task.cid' => Coroutine::id(),
        ];
    }

    /**
     * @Task
     * @return string
     */
    public function aHandel(){
        $this->log->get("task2")->info(date('YmdHis'));
        return "注解task";
    }
}


