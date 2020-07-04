<?php
declare(strict_types=1);
namespace App\Controller;
use App\Amqp\Producer\DemoProducer;
use App\Service\QueueService;
use App\Task\AnnotationTask;
use App\Task\TestTask;
use Hyperf\Amqp\Producer;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use App\Middleware\Auth\JwtMiddleware;
use Hyperf\Task\Task;
use Hyperf\Task\TaskExecutor;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Coroutine;
use \Phper666\JwtAuth\Jwt;
use Hyperf\Di\Annotation\Inject;
use Phper666\JwtAuth\Middleware\JwtAuthMiddleware;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use App\Service\LogService;
use App\Model\User;
/**
 * @Controller("user")
 */
class UserController extends AbstractController
{
    /**
     * @Inject()
     * @var Jwt
     */
    protected $jwt;

    /**
     * @Inject()
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    /**
     * @Inject()
     * @var LogService
     */
    protected $logger;

    /**
     * @Inject()
     * @var QueueService
     */
    private $queue;

    /**
     * @Inject()
     * @var Producer
     */
    protected $producter;

    /**
     * @RequestMapping(path="index", methods="get")
     * @Middleware(JwtAuthMiddleware::class)
     */
    public function index() {
        $this->logger->get()->debug("hello world");
        return $this->jwt->getParserData();
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @PostMapping(path="create")
     */
    public function storeUser(RequestInterface $request, ResponseInterface $response) :Object{

        $username = $request->input('username');
        $password = $request->input('password');
        //验证
        $validator = $this->validationFactory->make(
            $request->all(),
            [
                'username' => 'required',
                'password' => 'required',
            ],
            [
                'username.required' => 'Username is required',
                'password.required' => 'password is required',
            ]
        );

        if ($validator->fails()) {
            return $this->response->json([
                'code' => 422,
                'msg' => 'validate failure',
                'data' => ''
            ]);
        }
        return User::create([
            'username' =>$username,
            'passwd' => hash("md5", $password)
        ]);
    }

    /**
     * @PostMapping(path="login")
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function login()
    {
        $username = $this->request->input('username');
        $password = $this->request->input('password');
        //验证
        $validator = $this->validationFactory->make(
            $this->request->all(),
            [
                'username' => 'required',
                'password' => 'required',
            ],
            [
                'username.required' => 'Username is required',
                'password.required' => 'password is required',
            ]
        );

        $validator->after(function ($validator) {
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field', 'Something is wrong with this field!');
            }
        });

        if ($validator->fails()) {
            return $this->response->json([
                'code' => 422,
                'msg' => 'validate failure',
                'data' => ''
            ]);
        }

        if ($username && $password) {

            $user = User::query()->where(['username'=>$username])->first();
            if($user and $user->passwd == hash('md5', $password)){
                $userData = [
                    'uid' => $user->id, // 如果使用单点登录，必须存在配置文件中的sso_key的值，一般设置为用户的id
                    'username' => $user->username,
                ];
                $token = $this->jwt->getToken($userData);
                $data = [
                    'code' => 0,
                    'msg' => 'success',
                    'data' => [
                        'token' => (string)$token,
                        'exp' => $this->jwt->getTTL(),
                        'username'=>$username
                    ]
                ];
                return $this->response->json($data);
            }

        }
        return $this->response->json(['code' => 404, 'msg' => '登录失败', 'data' => []]);
    }

    /**
     * @PostMapping(path="refreshToken")
     * @Middleware(JwtAuthMiddleware::class)
     * @return \Psr\Http\Message\ResponseInterface
     */
    # 刷新token，http头部必须携带token才能访问的路由
    public function refreshToken()
    {
        $token = $this->jwt->refreshToken();
        $data = [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'token' => (string)$token,
                'exp' => $this->jwt->getTTL(),
            ]
        ];
        return $this->response->json($data);
    }

    /**
     * @GetMapping(path="logout")
     * @Middleware(JwtAuthMiddleware::class)
     * @return bool
     */
    # 注销token，http头部必须携带token才能访问的路由
    public function logout()
    {
        $this->jwt->logout();
        return true;
    }
    private function somethingElseIsInvalid(){
        return false;
    }

    /**
     * 异步队列
     * @GetMapping("queuetest")
     */
    public function questTest(){
        $this->logger->get("queue")->info("调用队列".time());
        $this->queue->push(['time'=>time()],5);
        $this->logger->get("queue")->info("调用队列后".time());
        return 'success';
    }

    /**
     * RabbitMq 生产者
     * @GetMapping("productTest")
     * @return string
     */
    public function productTest(){
        $this->logger->get("rabbitmq")->info("调用rabbit".time());
        $message = new DemoProducer(4);
        $result = $this->producter->produce($message);
        $this->logger->get("rabbitmq")->info("调用rabbit2".time());
        return 'success';
    }

    /**
     * @GetMapping("taskTest")
     * @return mixed
     * @throws \Throwable
     */
    public function taskTest(){
        $this->logger->get("task")->info("调用task".time());
        $container = ApplicationContext::getContainer();
        $task = $container->get(TaskExecutor::class);
        $result = $task->execute(new Task([AnnotationTask::class, 'handle'], [Coroutine::id()]));
        $this->logger->get("task")->info("调用task2".time());

        $aTask = $container->get(AnnotationTask::class);
        $result2 = $aTask->aHandel(Coroutine::id());
        return [$result, $result2];
    }
}