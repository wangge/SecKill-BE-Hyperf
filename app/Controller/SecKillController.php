<?php


namespace App\Controller;

use App\Amqp\Producer\SecondKillProducer;
use App\Model\Good;
use App\Model\Order;
use App\Service\LogService;
use App\Service\RedisSevice;
use Hyperf\Amqp\Producer;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
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
     * @var LogService
     */
    private $log;
    /**
     * @Inject()
     * @var Producer
     */
    private $producer;
    /**
     * 通过redis 锁及队列解决超卖
     * @RequestMapping("buy", methods="get,post")
     * @return mixed
     */
    public function buy(){
        $id = $this->request->input('id');
        try{
            //$lock = $this->redis->get()->setnx("skill_lock", true);
            //加锁和过期指令的原子性 5秒过期,在并发高于200时也会出现超卖的现象
            $lock = $this->redis->get()->set("skill_lock",true,['nx', 'ex' => 5]);
            if($lock){
                //todo 检查是否还有库存
                //$store_num = $this->redis->get()->get("s_goods_store_{$id}");
                $store_num = $this->redis->get()->lLen("s_goods_store_l_{$id}");
                if($store_num<=0) {
                    $this->log->get("skill")->info("秒杀结束");
                    return $this->response->json([
                        'code' => 1,
                        'msg' => '已经没有库存',
                    ]);
                }
                //todo 检查该用户是否已经秒杀过
                $user = $this->jwt->getParserData();
                //$user = ['uid'=>rand(1,100000)];
                if(!$this->storeUser($id, $user['uid'])){
                    //添加不成功就是已经参加过秒杀
                    return $this->response->json([
                        'code' => 2,
                        'msg' => '已经参见过',
                    ]);
                }
                //todo 生成订单，通过消息中间件，并库存减一
                //todo 减库存时需要加锁
                if($this->redis->get()->lPop("s_goods_store_l_{$id}")){
                    $this->redis->get()->incrBy("s_goods_store_{$id}",-1);
                    $this->log->get("skill")->info("left store:".$this->getStore());
                    $this->producer->produce(new SecondKillProducer(["user_id"=>$user['uid'],'goods_id'=>$id]));
                    //返回秒杀成功的消息
                    $this->log->get("skill")->info("秒杀成功");
                    return $this->response->json([
                        'code' => 0,
                        'msg' => '秒杀成功',
                    ]);
                }
                return $this->response->json([
                    'code' => 1,
                    'msg' => '已经没有库存',
                ]);

            }
        }catch (\Exception $exception){
            return $this->response->json([
                'code' => 2,
                'msg' => 'Exception',
            ]);
        } finally {
            $this->redis->get()->del("skill_lock");
        }





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
        $this->redis->get()->del("s_goods_killed_{$id}");


        //通过将商品库存压人队里，每次取出一个
        $this->redis->get()->del("s_goods_store_l_{$id}");
        for($i=1;$i<=$goods->store;$i++){
            $this->redis->get()->lPush("s_goods_store_l_{$id}",$i);
        }
        return $this->redis->get()->lLen("s_goods_store_l_{$id}");
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
        $order = Order::query()->where(['user_id'=>$user['uid'], 'goods_id'=>$goods_id])->first();
        if(!$order) return ['code'=>1,'msg'=>'还未保存成功'];
        return ['code'=>0,'msg'=>'秒杀成功','data'=>$order];
    }

}