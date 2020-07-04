<?php

declare(strict_types=1);

use Hyperf\Database\Seeders\Seeder;
use App\Model\User;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i=1;$i<10;$i++){
            User::create([
                'username' =>"master{$i}",
                'passwd' => hash("md5", "123456")
            ]);
        }

    }
}
