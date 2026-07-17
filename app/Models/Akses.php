<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Akses extends Model
{
    use HasFactory;
    protected $table = 'akses';

    public $timestamps = false; 
    protected $fillable = [
        'nama_akses',
    ];

}
