<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoleAkses extends Model
{
    use HasFactory;
    protected $table = 'role_akses';

    public $timestamps = false; 
    protected $fillable = [
        'id_role',
        'id_akses',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class,'id_role');
    }

    public function akses()
    {
        return $this->belongsTo(Akses::class,'id_akses');
    }

}
