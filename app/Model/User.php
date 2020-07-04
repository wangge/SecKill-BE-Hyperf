<?php

declare (strict_types=1);
namespace App\Model;

use App\Service\LogService;
use Hyperf\Database\Model\Events\Created;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Di\Annotation\Inject;

/**
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['username','passwd'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];
    //public $timestamps = false;

    /**
     * @Inject()
     * @var LogService
     */
    private $log;
    public function created(Created $event)
    {
        $this->log->get("model")->info("create user");
        $this->log->get("model")->debug(json_encode($event));
    }
}