<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrillLogMain extends Model
{
    protected $connection = 'sqlsrv';

    protected $table = 'dbo.tb_logs_main';

    public $timestamps = false;

    protected $guarded = [];
}
