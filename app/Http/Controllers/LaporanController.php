<?php

namespace App\Http\Controllers;


use App\Models\Kontrakan;
use App\Models\TransaksiList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function harian()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Harian',
        ];

        return view('admin.laporan.harian.umum', $data);
    }

    public function getAllBukuKas(Request $request)
    {
        // Mengambil parameter 'date' dan 'book' dari query string
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan tanggal hari ini
        if (!$date) {
            $date = Carbon::now()->format('Y-m-d');
        } else {
            // Ubah format tanggal dari 'd-m-Y' ke 'Y-m-d'
            $date = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }

        // Menghitung saldo awal hari
        $saldoAwalHariQuery = TransaksiList::select('code_kontrakan', DB::raw('SUM(IF(tipe="masuk", nominal, -nominal)) as saldo_awal'))
            ->where(function ($query) use ($date) {
                $query->whereHas('transaksiMasuk', function ($query) use ($date) {
                    $query->whereDate('periode_sewa', '<', $date);
                })->orWhereHas('transaksiKeluar', function ($query) use ($date) {
                    $query->whereDate('tanggal_transaksi', '<', $date);
                });
            })
            ->groupBy('code_kontrakan');

        if ($code_kontrakan !== 'all') {
            $saldoAwalHariQuery->where('code_kontrakan', $code_kontrakan);
        }

        $saldoAwalHari = $saldoAwalHariQuery->get()->sum('saldo_awal');

        // Menghitung semua pemasukan dan pengeluaran pada tanggal tertentu
        $transaksiListQuery = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->where(function ($query) use ($date, $code_kontrakan) {
                $query->whereHas('transaksiMasuk', function ($query) use ($date) {
                    $query->whereDate('periode_sewa', '=', $date);
                })
                    ->when($code_kontrakan !== 'all', function ($query) use ($code_kontrakan) {
                        $query->where('code_kontrakan', $code_kontrakan);
                    });
            })
            ->orWhere(function ($query) use ($date, $code_kontrakan) {
                $query->whereHas('transaksiKeluar', function ($query) use ($date) {
                    $query->whereDate('tanggal_transaksi', '=', $date);
                })
                    ->when($code_kontrakan !== 'all', function ($query) use ($code_kontrakan) {
                        $query->where('code_kontrakan', $code_kontrakan);
                    });
            });

        $transaksiList = $transaksiListQuery->get();

        $semuaPemasukan = $transaksiList->where('tipe', 'masuk')->sum('nominal');
        $semuaPengeluaran = $transaksiList->where('tipe', 'keluar')->sum('nominal');

        // Menghitung akumulasi dan saldo akhir hari
        $akumulasi = $semuaPemasukan - $semuaPengeluaran;
        $saldoAkhirHari = $saldoAwalHari + $akumulasi;

        // Mengembalikan data dalam format JSON
        return response()->json([
            'date' => $date,
            'saldoAwalHari' => rupiah($saldoAwalHari),
            'semuaPemasukan' => rupiah($semuaPemasukan),
            'semuaPengeluaran' => rupiah($semuaPengeluaran),
            'akumulasi' => rupiah($akumulasi),
            'saldoAkhirHari' => rupiah($saldoAkhirHari),
        ]);
    }

    public function getAllExIn(Request $request)
    {
        // Mengambil parameter 'date' dan 'book' dari query string
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan tanggal hari ini
        if (!$date) {
            $date = Carbon::now()->format('Y-m-d');
        } else {
            // Ubah format tanggal dari 'd-m-Y' ke 'Y-m-d'
            $date = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }

        $code_kontrakan = $request->input('book', 'all');

        $transaksiMasukQuery = TransaksiList::where('tipe', 'masuk')
            ->whereHas('transaksiMasuk', function ($query) use ($date) {
                $query->whereDate('periode_sewa', $date);
            });

        if ($code_kontrakan !== 'all') {
            $transaksiMasukQuery->where('code_kontrakan', $code_kontrakan);
        }

        $transaksiMasuk = $transaksiMasukQuery->with('transaksiMasuk')->get();

        $transaksiKeluarQuery = TransaksiList::where('tipe', 'keluar')
            ->whereHas('transaksiKeluar', function ($query) use ($date) {
                $query->whereDate('tanggal_transaksi', $date);
            });

        if ($code_kontrakan !== 'all') {
            $transaksiKeluarQuery->where('code_kontrakan', $code_kontrakan);
        }

        $transaksiKeluar = $transaksiKeluarQuery->with('transaksiKeluar')->get();

        foreach ($transaksiMasuk as $transaksi) {
            $kontrakan = Kontrakan::where('code_kontrakan', $transaksi->code_kontrakan)->first();
            $transaksi->nama_kontrakan = $kontrakan->nama_kontrakan ?? 'Unknown';
        }

        foreach ($transaksiKeluar as $transaksi) {
            $kontrakan = Kontrakan::where('code_kontrakan', $transaksi->code_kontrakan)->first();
            $transaksi->nama_kontrakan = $kontrakan->nama_kontrakan ?? 'Unknown';
        }

        return response()->json([
            'transaksiMasuk' => $transaksiMasuk,
            'transaksiKeluar' => $transaksiKeluar,
        ]);
    }

    public function umum()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Harian',
        ];

        return view('admin.laporan.harian.umum', $data);
    }

    public function aktivitas()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Harian',
        ];

        return view('admin.laporan.harian.aktivitas', $data);
    }
    // =================NSE===================================
    public function get_aktivitas_harian(Request $request)
    {
        $date = $request->input('date');
        $code_kontrakan = $request->input('book');

        $transaksi = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->whereHas('transaksiMasuk', function ($query) use ($date) {
                $query->whereDate('periode_sewa', Carbon::parse($date));
            })
            ->orWhereHas('transaksiKeluar', function ($query) use ($date) {
                $query->whereDate('tanggal_transaksi', Carbon::parse($date));
            })
            ->get();
        if ($code_kontrakan !== 'all' && $code_kontrakan !== null) {
            $transaksi = $transaksi->where('code_kontrakan', $code_kontrakan);
        }
        $data['pengeluarans'] = $transaksi
            ->where('tipe', 'keluar')
            ->groupBy('code_kontrakan')
            ->map(function ($item) {
                return [
                    'id' => $item[0]->id,
                    'nama_kontrakan' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                    'total' => $item->sum('nominal'),
                    'transaksi' => $item->pluck('transaksiKeluar'),

                ];
            })->values();

        $data['pemasukans'] = $transaksi
            ->where('tipe', 'masuk')
            ->groupBy('code_kontrakan')
            ->map(function ($item) {
                return [
                    'id' => $item[0]->id,
                    'nama_kontrakan' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                    'total' => $item->sum('nominal'),
                    'transaksi' => $item->pluck('transaksiMasuk'),

                ];
            })->values();

        $data['total_pemasukan'] = $data['pemasukans']->sum('total');
        $data['total_pengeluaran'] = $data['pengeluarans']->sum('total');
        $html = view('components.aktivitas', $data)->render();
        return response()->json(['html' => $html]);
    }

    public function ringkasan()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Harian',
        ];

        return view('admin.laporan.harian.ringkasan', $data);
    }

    public function get_ringkasan_harian(Request $request)
    {
        $date = $request->input('date');
        $code_kontrakan = $request->input('book');

        $data['dates'] = [];
        $data['type'] = 'harian';
        for ($i = 0; $i < Carbon::parse($date)->daysInMonth; $i++) {
            $data['dates'][] = Carbon::parse($date)->addDays($i)->format('Y-m-d');
        }

        $transaksi = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->whereHas('transaksiMasuk', function ($query) use ($date) {
                $query->where('periode_sewa', 'like', Carbon::parse($date)->format('Y-m') . "%");
            })
            ->orWhereHas('transaksiKeluar', function ($query) use ($date) {
                $query->where('tanggal_transaksi', 'like', Carbon::parse($date)->format('Y-m') . "%");
            })
            ->get();
        if ($code_kontrakan !== 'all' && $code_kontrakan !== null) {
            $transaksi = $transaksi->where('code_kontrakan', $code_kontrakan);
        }


        $data['pengeluarans'] = $transaksi
            ->where('tipe', 'keluar')
            ->groupBy('code_kontrakan')
            ->map(function ($item) use ($data) {
                $trx = [];
                foreach ($data['dates'] as $date) {
                    $t = TransaksiList::with(['transaksiKeluar'])
                        ->whereHas('transaksiKeluar', function ($query) use ($date) {
                            $query->where('tanggal_transaksi', $date);
                        })
                        ->where('code_kontrakan', $item[0]->code_kontrakan);
                    if (request('book') !== 'all' && request('book') !== null) {
                        $t = $t->where('code_kontrakan', $item[0]->code_kontrakan);
                    }
                    $trx[$date] = $t->where('tipe', 'keluar')->sum('nominal') ?? 0;
                }
                return [
                    'nama_kontrakan' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                    'qty' => count(array_filter($trx, function ($value) {
                        return $value != 0;
                    })),
                    'total' => $item->where('tipe', 'keluar')->sum('nominal'),
                    'transaksi' => $trx,
                ];
            })
            ->values();


        $data['grandTotalPengeluarans'] = [];
        foreach ($data['dates'] as $date) {
            $t = TransaksiList::with(['transaksiKeluar'])
                ->whereHas('transaksiKeluar', function ($query) use ($date) {
                    $query->where('tanggal_transaksi', $date);
                })->where('tipe', 'keluar');
            if (request('book') !== 'all' && request('book') !== null) {
                $t = $t->where('code_kontrakan', $request->book);
            }
            $data['grandTotalPengeluarans'][$date] = $t->sum('nominal') ?? 0;
        }


        $data['pemasukans'] = $transaksi
            ->where('tipe', 'masuk')
            ->groupBy('code_kontrakan')
            ->map(function ($item) use ($data) {
                $trx = [];
                foreach ($data['dates'] as $date) {
                    $t = TransaksiList::with(['transaksiMasuk'])
                        ->whereHas('transaksiMasuk', function ($query) use ($date) {
                            $query->where('periode_sewa', 'like', $date . "%");
                        })
                        ->where('code_kontrakan', $item[0]->code_kontrakan);
                    if (request('book') !== 'all' && request('book') !== null) {
                        $t = $t->where('code_kontrakan', $item[0]->code_kontrakan);
                    }
                    $trx[$date] = $t->sum('nominal') ?? 0;
                }
                return [
                    'nama_kontrakan' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                    'qty' => count(array_filter($trx, function ($value) {
                        return $value != 0;
                    })),
                    'total' => $item->sum('nominal'),
                    'transaksi' => $trx,
                ];
            })
            ->values();


        $data['grandTotalPemasukans'] = [];
        foreach ($data['dates'] as $date) {
            $t = TransaksiList::with(['transaksiMasuk'])
                ->whereHas('transaksiMasuk', function ($query) use ($date) {
                    $query->where('periode_sewa', $date);
                });
            if (request('book') !== 'all' && request('book') !== null) {
                $t = $t->where('code_kontrakan', $request->book);
            }
            $data['grandTotalPemasukans'][$date] = $t->sum('nominal') ?? 0;
        }

        $data['profits'] = $transaksi
            ->groupBy('code_kontrakan')
            ->map(function ($item) use ($data) {
                $trx = [];
                foreach ($data['dates'] as $date) {
                    $tMasuk = TransaksiList::with(['transaksiMasuk'])
                        ->whereHas('transaksiMasuk', function ($query) use ($date) {
                            $query->where('periode_sewa', $date);
                        })
                        ->where('tipe', 'masuk')
                        ->where('code_kontrakan', $item[0]->code_kontrakan);
                    $tKeluar = TransaksiList::with(['transaksiKeluar'])
                        ->whereHas('transaksiKeluar', function ($query) use ($date) {
                            $query->where('tanggal_transaksi', $date);
                        })
                        ->where('tipe', 'keluar')
                        ->where('code_kontrakan', $item[0]->code_kontrakan);
                    $trx[$date] = $tMasuk->sum('nominal') - $tKeluar->sum('nominal') ?? 0;
                }
                return [
                    'nama_kontrakan' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                    'qty' => count(array_filter($trx, function ($value) {
                        return $value != 0;
                    })),
                    'transaksi' => $trx,
                ];
            })
            ->values();


        $data['grandTotalProfits'] = [];
        foreach ($data['dates'] as $date) {
            $tMasuk = TransaksiList::with(['transaksiMasuk'])
                ->whereHas('transaksiMasuk', function ($query) use ($date) {
                    $query->where('periode_sewa', $date);
                })
                ->where('tipe', 'masuk');
            $tKeluar = TransaksiList::with(['transaksiKeluar'])
                ->whereHas('transaksiKeluar', function ($query) use ($date) {
                    $query->where('tanggal_transaksi', $date);
                })
                ->where('tipe', 'keluar');
            if (request('book') !== 'all' && request('book') !== null) {
                $tMasuk = $tMasuk->where('code_kontrakan', $request->book);
                $tKeluar = $tKeluar->where('code_kontrakan', $request->book);
            }
            $data['grandTotalProfits'][$date] = $tMasuk->sum('nominal') - $tKeluar->sum('nominal') ?? 0;
        }

        $html = view('components.ringkasan', $data)->render();
        return response()->json(['html' => $html]);
    }
    // ==================NSE===================================


}
