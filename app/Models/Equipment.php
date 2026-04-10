<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $connection = 'mysql';

    protected $table = 'ehelpdesk_m_equipment';

    public $timestamps = false;

    protected $guarded = [];
}
