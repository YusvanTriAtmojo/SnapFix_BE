<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;
    protected $table = 'project';
    public $timestamps = false; 

    protected $fillable = [
        'nama_project'
    ]; 

    public function lokasi()
    {
        return $this->hasMany(Lokasi::class, 'id_project', 'id');
    }

    public function fasilitas()
    {
        return $this->hasMany(Fasilitas::class, 'id_project', 'id');
    }

    public function kerusakan()
    {
        return $this->hasMany(Kerusakan::class, 'id_project', 'id');
    }
}
