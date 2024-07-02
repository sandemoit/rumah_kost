<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiKeluar extends Model
{
    use HasFactory;

    protected $table = 'transaksi_keluar';

    protected $fillable = [
        'code_keluar',
        'deskripsi',
        'tanggal_transaksi',
    ];

    public function transaksiList()
    {
        return $this->belongsTo(TransaksiList::class, 'id', 'id_tipe');
    }
}
