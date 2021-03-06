<?php

declare(strict_types=1);

namespace App\Job;

use App\Service\LogService;
use Hyperf\AsyncQueue\Job;
use Hyperf\Di\Annotation\Inject;

class TestJob extends Job
{
    public $params;

    /**
     * @Inject()
     * @var LogService
     */
    private $log;
    public function __construct($params)
    {
        // 这里最好是普通数据，不要使用携带 IO 的对象，比如 PDO 对象
        $this->params = $params;
    }

    public function handle()
    {
        // 根据参数处理具体逻辑
        // 通过具体参数获取模型等
        var_dump($this->params);
        $this->log->get("queue")->info(json_encode($this->params));
    }
}
