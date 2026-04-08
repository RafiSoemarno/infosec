<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public ?int $id = null;
    public ?string $username = null;
    public ?string $name = null;
    public ?string $email = null;
    public ?string $password = null;
    public ?string $employee_id = null;
    public ?string $company = null;
    public ?string $business_unit = null;
    public ?string $remember_token = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'employee_id',
        'company',
        'business_unit',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function videoProgress(): HasMany
    {
        return $this->hasMany(UserVideoProgress::class);
    }

    public function drillCompletions(): HasMany
    {
        return $this->hasMany(UserDrillCompletion::class);
    }

    public function deviceInfo(): HasOne
    {
        return $this->hasOne(UserDeviceInfo::class);
    }
}
