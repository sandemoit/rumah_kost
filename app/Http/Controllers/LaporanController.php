<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
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
                    $query->whereDate('tanggal_transaksi', '<', $date);
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
                    $query->whereDate('tanggal_transaksi', '=', $date);
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
            // 'saldoAwalHari' => rupiah($saldoAwalHari),
            'semuaPemasukan' => rupiah($semuaPemasukan),
            'semuaPengeluaran' => rupiah($semuaPengeluaran),
            'akumulasi' => rupiah($akumulasi),
            // 'saldoAkhirHari' => rupiah($saldoAkhirHari),
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
            try {
                // Ubah format tanggal dari 'Y-m' ke 'Y-m'
                $date = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format. Please use Y-m-d format.'], 400);
            }
        }

        // Validasi jika tidak ada book yang ditentukan
        if ($code_kontrakan !== 'all' && !Kontrakan::where('code_kontrakan', $code_kontrakan)->exists()) {
            return response()->json([
                'data' => []
            ]);
        }

        $transaksiMasukQuery = TransaksiList::where('tipe', 'masuk')
            ->whereHas('transaksiMasuk', function ($query) use ($date) {
                $query->whereDate('tanggal_transaksi', $date);
            });

        $transaksiKeluarQuery = TransaksiList::where('tipe', 'keluar')
            ->whereHas('transaksiKeluar', function ($query) use ($date) {
                $query->whereDate('tanggal_transaksi', $date);
            });

        if ($code_kontrakan !== 'all') {
            $transaksiMasukQuery->where('code_kontrakan', $code_kontrakan);
            $transaksiKeluarQuery->where('code_kontrakan', $code_kontrakan);
        }

        $transaksiKeluar = $transaksiKeluarQuery->with('transaksiKeluar')->get();
        $transaksiMasuk = $transaksiMasukQuery->with('transaksiMasuk')->get();

        // Ambil semua kontrakan
        $allKontrakan = Kontrakan::all();

        // Group pemasukan dan pengeluaran berdasarkan 'code_kontrakan'
        $groupedMasuk = $transaksiMasuk->groupBy('code_kontrakan')->map(function ($items) {
            return [
                'nama_kontrakan' => Kontrakan::where('code_kontrakan', $items->first()->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                'total_masuk' => $items->sum('nominal')
            ];
        });

        $groupedKeluar = $transaksiKeluar->groupBy('code_kontrakan')->map(function ($items) {
            return [
                'nama_kontrakan' => Kontrakan::where('code_kontrakan', $items->first()->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                'total_keluar' => $items->sum('nominal')
            ];
        });

        // Persiapkan result default untuk semua kontrakan
        $result = [];
        foreach ($allKontrakan as $kontrakan) {
            $code_kontrakan = $kontrakan->code_kontrakan;
            $result[$code_kontrakan] = [
                'nama_kontrakan' => $kontrakan->nama_kontrakan ?? 'Unknown',
                'total_masuk' => $groupedMasuk[$code_kontrakan]['total_masuk'] ?? 0,
                'total_keluar' => $groupedKeluar[$code_kontrakan]['total_keluar'] ?? 0,
            ];
        }

        // Persiapan data yang akan diparsing dan dikirimkan dalam response JSON
        $result = [];

        // Loop pertama: Proses data pemasukan dan pengeluaran
        foreach ($groupedMasuk as $code_kontrakan => $dataMasuk) {
            if ($dataMasuk['total_masuk'] > 0 || (isset($groupedKeluar[$code_kontrakan]) && $groupedKeluar[$code_kontrakan]['total_keluar'] > 0)) {
                $result[$code_kontrakan] = [
                    'nama_kontrakan' => $dataMasuk['nama_kontrakan'],
                    'total_masuk' => $dataMasuk['total_masuk'],
                    'total_keluar' => $groupedKeluar[$code_kontrakan]['total_keluar'] ?? 0 // Pastikan pengeluaran default ke 0
                ];
            }
        }

        // Loop kedua: Proses kontrakan yang hanya punya pengeluaran
        foreach ($groupedKeluar as $code_kontrakan => $dataKeluar) {
            if (!isset($result[$code_kontrakan]) && $dataKeluar['total_keluar'] > 0) {
                $result[$code_kontrakan] = [
                    'nama_kontrakan' => $dataKeluar['nama_kontrakan'],
                    'total_masuk' => $groupedMasuk[$code_kontrakan]['total_masuk'] ?? 0, // Pemasukan default ke 0
                    'total_keluar' => $dataKeluar['total_keluar']
                ];
            }
        }

        // Mengembalikan data yang sudah dikelompokkan dalam bentuk JSON
        return response()->json([
            'data' => $result
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
                $query->where('tanggal_transaksi', Carbon::parse($date)->format('Y-m-d'));
            })
            ->orWhereHas('transaksiKeluar', function ($query) use ($date) {
                $query->where('tanggal_transaksi', Carbon::parse($date));
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
            ->map(function ($item) use ($date) {
                return [
                    'id' => $item[0]->id,
                    'nama_kontrakan' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                    'total' => $item->sum('nominal'),
                    'transaksi' => $item->where('tanggal_transaksi', Carbon::parse($date)->format('Y-m-d'))->pluck('transaksiMasuk'),

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
                $query->where('tanggal_transaksi', 'like', Carbon::parse($date)->format('Y-m') . "%");
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
                    'qty' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->kamar->count(),
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
                            $query->where('tanggal_transaksi', 'like', $date . "%");
                        })
                        ->where('code_kontrakan', $item[0]->code_kontrakan);
                    if (request('book') !== 'all' && request('book') !== null) {
                        $t = $t->where('code_kontrakan', $item[0]->code_kontrakan);
                    }
                    $trx[$date] = $t->sum('nominal') ?? 0;
                }
                return [
                    'nama_kontrakan' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                    'qty' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->kamar->count(),
                    'total' => $item->sum('nominal'),
                    'transaksi' => $trx,
                ];
            })
            ->values();


        $data['grandTotalPemasukans'] = [];
        foreach ($data['dates'] as $date) {
            $t = TransaksiList::with(['transaksiMasuk'])
                ->whereHas('transaksiMasuk', function ($query) use ($date) {
                    $query->where('tanggal_transaksi', $date);
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
                            $query->where('tanggal_transaksi', $date);
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
                    'qty' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->kamar->count(),
                    'transaksi' => $trx,
                ];
            })
            ->values();


        $data['grandTotalProfits'] = [];
        foreach ($data['dates'] as $date) {
            $tMasuk = TransaksiList::with(['transaksiMasuk'])
                ->whereHas('transaksiMasuk', function ($query) use ($date) {
                    $query->where('tanggal_transaksi', $date);
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
