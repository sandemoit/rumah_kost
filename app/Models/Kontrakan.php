<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kontrakan extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kontrakan',
        'alamat_kontrakan',
        'code_kontrakan',
    ];

    protected $table = 'kontrakan';
}
