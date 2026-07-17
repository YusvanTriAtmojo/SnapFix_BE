<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Role;
use App\Models\Kerusakan;
use App\Models\UserFcmToken;  



class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    public $timestamps = false; 
    protected $fillable = [
        'id_project',
        'id_role',
        'nip',
        'name',
        'notlp',
        'alamat',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class,'id_role');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project');
    }

    public function akses()
    {
        return $this->belongsTo(Akses::class,'id_akses');
    }

    public function fcmTokens()
    {
        return $this->hasMany(UserFcmToken::class, 'user_id');
    }

    public function kerusakan()
    {
        return $this->hasMany(Kerusakan::class, 'user_id');
    }

    // Untuk JWT (login dengan token)
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role->nama_role
        ];
    }
}
