<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;
    protected $table = 'roles';

    public $timestamps = false; 

    protected $fillable = [
        'nama_role',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_role');
    }
    
    public function akses()
    {
        return $this->belongsToMany(
            Akses::class,    
            'role_akses',   
            'id_role',       
            'id_akses'    
        );
    }

    public function roleAkses()
    {
        return $this->hasMany(RoleAkses::class, 'id_role');
    }


}
