<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiMasuk extends Model
{
    use HasFactory;

    protected $table = 'transaksi_masuk';

    protected $fillable = [
        'tanggal_transaksi',
        'code_masuk',
        'deskripsi',
        'tanggal_transaksi',
        'bulan',
        'tahun'
    ];

    public function transaksiList()
    {
        return $this->belongsTo(TransaksiList::class, 'id', 'id_tipe');
    }
}
