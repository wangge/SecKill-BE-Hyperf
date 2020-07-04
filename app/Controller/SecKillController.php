<?php


namespace App\Controller;

use App\Amqp\Producer\SecondKillProducer;
use App\Model\Good;
use App\Model\Order;
use App\Service\RedisSevice;
use Hyperf\Amqp\Producer;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Phper666\JwtAuth\Jwt;
use Phper666\JwtAuth\Middleware\JwtAuthMiddleware;

/**
 * @Controller()
 * Class SecKillController
 * @package App\Controller
 */
class SecKillController extends AbstractController
{

    /**
     * @Inject()
     * @var Jwt
     */
    private $jwt;

    /**
     * @Inject()
     * @var Producer
     */
    private $producer;
    /**
     * @PostMapping("buy")
     * @Middleware(JwtAuthMiddleware::class)
     * @return mixed
     */
    public function buy(){
        $id = $this->request->input('id');
        //todo 检查是否还有库存
        $store_num = $this->redis->get()->get("s_goods_store_{$id}");
        if(!$store_num) $this->response->json([
            'code' => 1,
            'msg' => '已经没有库存',
        ]);
        //todo 检查该用户是否已经秒杀过
        $user = $this->jwt->getParserData();
        if(!$this->storeUser($id, $user['uid'])){
            //添加不成功就是已经参加过秒杀
            return $this->response->json([
                'code' => 2,
                'msg' => '已经参见过',
            ]);
        }
        //todo 生成订单，通过消息中间件，并库存减一
        $this->redis->get()->incrBy("s_goods_store_{$id}",-1);
        $this->producer->produce(new SecondKillProducer(["user_id"=>$user['uid'],'goods_id'=>$id]));
        //返回秒杀成功的消息
        return $this->response->json([
            'code' => 0,
            'msg' => '秒杀成功',
        ]);
    }

    /**
     * @GetMapping("goodsInfo")
     * @param $id
     * @return Good|\Hyperf\Database\Model\Model|null
     */
    public function getGoods(){
        $id = $this->request->input("id");
        return Good::findFromCache($id);
    }

    /**
     * @Inject()
     * @var RedisSevice
     */
    private $redis;
    /**
     * @PostMapping("setStore")
     */
    public function setStore(){
        $id = $this->request->input("id");
        $goods = $this->getGoods();
        $this->redis->get()->set("s_goods_store_{$id}",$goods->store);
        return 'ok';
    }

    /**
     * @GetMapping("getStore")
     * @return bool|mixed|string
     */
    public function getStore(){
        $id = $this->request->input("id");
        return $this->redis->get()->get("s_goods_store_{$id}");
    }

    private function storeUser($id, $user_id){
        return $this->redis->get()->sAdd("s_goods_killed_{$id}", $user_id);
    }

    /**
     * @GetMapping("getSeckillInfo")
     * @Middleware(JwtAuthMiddleware::class)
     * @return array
     */
    public function getSecKillInfo(){
        $goods_id = $this->request->input("goods_id");
        $user = $this->jwt->getParserData();
        $order = Order::query()->where(['user_id'=>$user['uid'], 'goods_id'=>$goods_id])->count();
        if(!$order) return ['code'=>1,'msg'=>'还为保存成功'];
        return ['code'=>0,'msg'=>'秒杀成功'];
    }

}