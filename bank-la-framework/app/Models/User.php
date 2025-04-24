<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\AccessRole;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * JWT identifier
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Custom JWT claims
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Role relationship
     */
    public function role()
    {
        return $this->belongsTo(AccessRole::class, 'role_id');
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'fullname',
        'email',
        'phone_number',
        'password',
        'role_id',
        'account_balance',
        'pin',
        'status',
        'loan_amount',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'loan_amount' => 'decimal:2',
        'email_verified_at' => 'datetime',
    ];
}
