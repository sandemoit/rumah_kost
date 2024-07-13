<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaksiList extends Model
{
    use HasFactory;

    protected $table = 'transaksi_list';

    protected $fillable = [
        'code_transaksi',
        'code_kontrakan',
        'id_kamar',
        'id_tipe',
        'tipe',
        'nominal',
        'saldo',
        'created_by',
    ];

    public function transaksiMasuk()
    {
        return $this->hasOne(TransaksiMasuk::class, 'id', 'id_tipe');
    }

    public function transaksiKeluar()
    {
        return $this->hasOne(TransaksiKeluar::class, 'id', 'id_tipe');
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'id_kamar');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
