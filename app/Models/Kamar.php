<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function transaksiList()
    {
        return $this->hasMany(TransaksiList::class, 'id_kamar');
    }

    public function kontrakan(): BelongsTo
    {
        return $this->belongsTo(Kontrakan::class, 'id_kontrakan');
    }
}
