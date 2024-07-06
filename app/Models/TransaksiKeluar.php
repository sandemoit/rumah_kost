<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiKeluar extends Model
{
    use HasFactory;

    protected $table = 'transaksi_keluar';

    protected $fillable = [
        'code_keluar',
        'deskripsi',
        'tanggal_transaksi',
    ];

    public function transaksiList(): BelongsTo
    {
        return $this->belongsTo(TransaksiList::class, 'id', 'id_tipe');
    }
}
