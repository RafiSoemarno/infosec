<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVideoProgress extends Model
{
    use HasFactory;

    protected $table = 'user_video_progress';

    protected $fillable = [
        'user_id',
        'video_id',
        'watched',
        'watched_at',
    ];

    protected $casts = [
        'watched' => 'boolean',
        'watched_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(EducationVideo::class, 'video_id');
    }
}
