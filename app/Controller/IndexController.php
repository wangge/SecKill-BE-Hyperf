<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;
use Hyperf\Elasticsearch\ClientBuilderFactory;
class IndexController extends AbstractController
{
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        $builder = $this->container->get(ClientBuilderFactory::class)->create();

        $client = $builder->setHosts(['http://127.0.0.1:9200'])->build();

        $info = $client->info();
        return [
            'method' => $method,
            'message' => "Hello {$user}.",
            'es_info' => $info,
        ];
    }
}
