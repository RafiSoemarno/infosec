<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardStatDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'dashboard_period_stat_id',
        'label',
        'actual',
        'target',
    ];

    public function periodStat(): BelongsTo
    {
        return $this->belongsTo(DashboardPeriodStat::class, 'dashboard_period_stat_id');
    }
}
