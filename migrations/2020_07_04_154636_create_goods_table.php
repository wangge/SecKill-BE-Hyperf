<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateGoodsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('goods', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("goods_name",100)->unique();
            $table->bigInteger('price')->comment("价格（分）");
            $table->string("images")->nullable();
            $table->bigInteger("store")->default(100);
            $table->timestamp("start_at");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods');
    }
}
