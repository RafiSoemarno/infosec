<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DashboardPeriodStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'fiscal_year',
        'period_label',
        'target',
        'actual',
        'percentage',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(DashboardStatDetail::class, 'dashboard_period_stat_id');
    }
}
