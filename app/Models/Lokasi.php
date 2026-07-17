<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lokasi extends Model
{
    use HasFactory;
    protected $table = 'lokasi';

    public $timestamps = false; 
    protected $fillable = [
        'id_project',
        'nama_lokasi',
    ];

    public function kerusakan()
    {
        return $this->hasMany(Kerusakan::class, 'id_lokasi', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project', 'id');
    }
}
