<?php

declare (strict_types=1);
namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 */
class Good extends Model implements CacheableInterface
{
    use Cacheable;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'goods';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ["id"];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    public function getPriceAttribute($value)
    {
        return number_format($value/100,2);
    }

    public function setPriceAttribute($value){
        $this->attributes['price'] = $value * 100;
    }
}