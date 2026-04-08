<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDeviceInfo extends Model
{
    use HasFactory;

    protected $table = 'user_device_info';

    protected $fillable = [
        'user_id',
        'computer_name',
        'ip_address',
        'plant',
        'location',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
