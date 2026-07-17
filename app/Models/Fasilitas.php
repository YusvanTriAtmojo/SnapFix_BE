<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fasilitas extends Model
{
    use HasFactory;
    protected $table = 'fasilitas';

    public $timestamps = false; 
    protected $fillable = [
        'id_project',
        'nama_fasilitas',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project', 'id');
    }

    
    public function kerusakan()
    {
        return $this->hasMany(Kerusakan::class, 'id_fasilitas', 'id');
    }
}
