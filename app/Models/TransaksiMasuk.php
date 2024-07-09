<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiMasuk extends Model
{
    use HasFactory;

    protected $table = 'transaksi_masuk';

    protected $fillable = [
        'tanggal_transaksi',
        'code_masuk',
        'deskripsi',
        'tanggal_transaksi',
        'periode_sewa',
    ];

    public function transaksiList(): BelongsTo
    {
        return $this->belongsTo(TransaksiList::class, 'id', 'id_tipe');
    }
}
