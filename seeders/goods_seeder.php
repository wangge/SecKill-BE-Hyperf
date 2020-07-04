<?php

declare(strict_types=1);

use Hyperf\Database\Seeders\Seeder;
use App\Model\Good;
class GoodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Good::create([
            'goods_name' => 'iphone 11 pro',
            'price'  => 19900,
            'store'  => 100,
            'start_at' => date("Y-m-d"). " 18:00:00"
        ]);
    }
}
