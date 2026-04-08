<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrillSimulation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'notify_note',
        'duration_label',
        'period_start',
        'period_end',
        'coming_soon',
        'sort_order',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'coming_soon' => 'boolean',
    ];

    public function completions(): HasMany
    {
        return $this->hasMany(UserDrillCompletion::class);
    }
}
