<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'embed_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function progress(): HasMany
    {
        return $this->hasMany(UserVideoProgress::class, 'video_id');
    }
}
