<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penyewa extends Model
{
    use HasFactory;

    protected $table = 'penyewa';

    protected $fillable = [
        'nama_penyewa',
        'alamat_penyewa',
        'nomor_wa',
        'status',
        'tanggal_masuk',
        'id_kontrakan',
        'id_kamar'
    ];

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'id_kamar');
    }
}
