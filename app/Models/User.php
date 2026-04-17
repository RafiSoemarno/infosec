<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'sqlsrv_app';
    protected $table = 'tb_user';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'MailAddress',
        'AuthLevel',
        'PasswordHash',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'PasswordHash',
        'PasswordSalt',
        'HashAlgorithm',
        'HashIterations',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ID' => 'int',
    ];

    /**
     * Get the password hash for auth verification.
     */
    public function getAuthPassword(): string
    {
        return (string) $this->PasswordHash;
    }

    /**
     * Accessors to map tb_user columns to fields expected by downstream code.
     */
    public function getRoleAttribute(): string
    {
        return (string) ($this->attributes['AuthLevel'] ?? 'regular');
    }

    public function getUsernameAttribute(): string
    {
        return (string) ($this->attributes['MailAddress'] ?? '');
    }

    public function getEmailAttribute(): string
    {
        return (string) ($this->attributes['MailAddress'] ?? '');
    }

    /**
     * Build an auth user array for DrillDataService methods.
     */
    public function toAuthUserArray(): array
    {
        $username = strtolower((string) $this->MailAddress);
        return [
            'id' => (int) $this->ID,
            'username' => $username,
            'name' => (string) ($this->name ?? $username),
            'employeeId' => '-',
            'company' => '-',
            'businessUnit' => '-',
            'email' => $username,
            'role' => (string) ($this->AuthLevel ?? 'regular'),
            'isSpecial' => $username === 'selamet.nuryanto',
        ];
    }

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
