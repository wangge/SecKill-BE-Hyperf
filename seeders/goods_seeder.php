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
        for($i=1;$i<10;$i++){
            Good::create([
                'goods_name' => 'iphone 11 pro max'.$i,
                'price'  => 199,
                'store'  => rand(200,500),
                'start_at' => date("Y-m-d"). " 18:00:00"
            ]);
        }

    }
}
