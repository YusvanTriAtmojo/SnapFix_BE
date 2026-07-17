<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kerusakan extends Model
{
    use HasFactory;
    protected $table = 'kerusakan';
    public $timestamps = false; 

    protected $fillable = [
        'user_id',
        'id_project',
        'id_lokasi',
        'id_fasilitas',
        'tanggal',
        'tanggal_perbaikan',
        'lat_posisi',
        'lng_posisi',
        'deskripsi',
        'deskripsi_perbaikan',
        'foto_kerusakan',
        'foto_perbaikan',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fasilitas()
    {
        return $this->belongsTo(Fasilitas::class, 'id_fasilitas', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project', 'id');
    }

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class, 'id_lokasi', "id");
    }

}
