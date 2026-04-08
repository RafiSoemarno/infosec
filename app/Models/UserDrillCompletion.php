<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDrillCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'drill_simulation_id',
        'completed_at',
        'response_time',
        'score',
        'status',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function drill(): BelongsTo
    {
        return $this->belongsTo(DrillSimulation::class, 'drill_simulation_id');
    }
}
