<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TransaksiList extends Model
{
    use HasFactory;

    protected $table = 'transaksi_list';

    protected $fillable = [
        'code_transaksi',
        'code_kontrakan',
        'id_kamar',
        'id_penyewa',
        'id_keluar',
        'id_masuk',
        'tipe',
        'nominal',
        'saldo',
        'created_by',
    ];

    public function transaksiMasuk()
    {
        return $this->hasOne(TransaksiMasuk::class, 'id', 'id_masuk');
    }

    public function transaksiKeluar()
    {
        return $this->hasOne(TransaksiKeluar::class, 'id', 'id_keluar');
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'id_kamar');
    }

    public function penyewa(): BelongsTo
    {
        return $this->belongsTo(Penyewa::class, 'id_penyewa');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeWithTransactions(Builder $query, $code_kontrakan, $month = false, $year = false, $keyword = false): void
    {
        $query->with(['transaksiMasuk', 'transaksiKeluar', 'kamar'])
            ->when($keyword, function (Builder $query, $keyword) {
                $query->whereHas('kamar', function (Builder $query) use ($keyword) {
                    $query->where('nama_kamar', 'LIKE', "%$keyword%");
                });
            })
            ->where(function (Builder $query) use ($month, $year) {
                if ($month == 'all') {
                    $query->whereHas('transaksiMasuk', function (Builder $query) use ($year) {
                        $query->whereYear('tanggal_transaksi', $year);
                    })->orWhereHas('transaksiKeluar', function (Builder $query) use ($year) {
                        $query->whereYear('tanggal_transaksi', $year);
                    });
                } else {
                    $query->whereHas('transaksiMasuk', function (Builder $query) use ($month, $year) {
                        $query->whereMonth('tanggal_transaksi', $month)
                            ->whereYear('tanggal_transaksi', $year);
                    })->orWhereHas('transaksiKeluar', function (Builder $query) use ($month, $year) {
                        $query->whereMonth('tanggal_transaksi', $month)
                            ->whereYear('tanggal_transaksi', $year);
                    });
                }
            })
            ->where('code_kontrakan', $code_kontrakan);
    }
}
