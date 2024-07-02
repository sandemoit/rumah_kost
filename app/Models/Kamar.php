<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kamar extends Model
{
    use HasFactory;

    protected $table = 'kamar';

    protected $fillable = [
        'nama_kamar',
        'id_kontrakan',
        'harga_kamar',
        'keterangan'
    ];

    public function penyewa()
    {
        return $this->hasMany(Penyewa::class, 'id_kamar');
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiList::class, 'id_kamar');
    }
}
