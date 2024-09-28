<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kontrakan;
use App\Models\TransaksiList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanCustomController extends Controller
{
    public function custom()
    {
        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Custom',
        ];

        return view('admin.laporan.custom.umum', $data);
    }

    public function getAllBukuKas(Request $request)
    {
        // Jika tidak ada parameter 'date' yang diberikan, gunakan tanggal Custom ini
        if (!empty($request->date)) {
            $date = explode(' - ', $request->input('date'));
            $dateStart = Carbon::createFromFormat('m/d/Y', $date[0])->startOfDay();
            $dateEnd = Carbon::createFromFormat('m/d/Y', $date[1])->endOfDay();
        } else {
            $dateStart = Carbon::now()->startOfDay();
            $dateEnd = Carbon::now()->endOfDay();
        }

        // Mengambil parameter 'book' dari query string
        $code_kontrakan = $request->query('book', 'all');

        // Menghitung saldo awal Custom
        $saldoAwalCustomQuery = TransaksiList::select('code_kontrakan', DB::raw('SUM(IF(tipe="masuk", nominal, -nominal)) as saldo_awal'))
            ->where(function ($query) use ($dateStart) {
                $query->whereHas('transaksiMasuk', function ($query) use ($dateStart) {
                    // Mengubah penulisan whereDate yang benar
                    $query->whereDate('tanggal_transaksi', '<', $dateStart);
                })->orWhereHas('transaksiKeluar', function ($query) use ($dateStart) {
                    // Mengubah penulisan whereDate yang benar
                    $query->whereDate('tanggal_transaksi', '<', $dateStart);
                });
            })
            ->groupBy('code_kontrakan');

        if ($code_kontrakan !== 'all') {
            $saldoAwalCustomQuery->where('code_kontrakan', $code_kontrakan);
        }

        $saldoAwalCustom = $saldoAwalCustomQuery->get()->sum('saldo_awal');

        // Menghitung semua pemasukan dan pengeluaran pada tanggal tertentu
        $transaksiListQuery = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->where(function ($query) use ($dateStart, $dateEnd, $code_kontrakan) {
                $query->whereHas('transaksiMasuk', function ($query) use ($dateStart, $dateEnd) {
                    // Perbaikan whereDate untuk rentang tanggal
                    $query->whereBetween('tanggal_transaksi', [$dateStart, $dateEnd]);
                })
                    ->when($code_kontrakan !== 'all', function ($query) use ($code_kontrakan) {
                        $query->where('code_kontrakan', $code_kontrakan);
                    });
            })
            ->orWhere(function ($query) use ($dateStart, $dateEnd, $code_kontrakan) {
                $query->whereHas('transaksiKeluar', function ($query) use ($dateStart, $dateEnd) {
                    // Perbaikan whereDate untuk rentang tanggal
                    $query->whereBetween('tanggal_transaksi', [$dateStart, $dateEnd]);
                })
                    ->when($code_kontrakan !== 'all', function ($query) use ($code_kontrakan) {
                        $query->where('code_kontrakan', $code_kontrakan);
                    });
            });

        $transaksiList = $transaksiListQuery->get();

        $semuaPemasukan = $transaksiList->where('tipe', 'masuk')->sum('nominal');
        $semuaPengeluaran = $transaksiList->where('tipe', 'keluar')->sum('nominal');

        // Menghitung akumulasi dan saldo akhir Custom
        $akumulasi = $semuaPemasukan - $semuaPengeluaran;
        $saldoAkhirCustom = $saldoAwalCustom + $akumulasi;

        // Mengembalikan data dalam format JSON
        return response()->json([
            'date' => $date,
            'saldoAwalCustom' => rupiah($saldoAwalCustom),
            'semuaPemasukan' => rupiah($semuaPemasukan),
            'semuaPengeluaran' => rupiah($semuaPengeluaran),
            'akumulasi' => rupiah($akumulasi),
            'saldoAkhirCustom' => rupiah($saldoAkhirCustom),
        ]);
    }

    public function getAllExIn(Request $request)
    {
        // Mengambil parameter 'date' dan 'book' dari query string
        if (!empty($request->date)) {
            $date = explode(' - ', $request->input('date'));
            $startDate = Carbon::createFromFormat('m/d/Y', $date[0])->startOfDay();
            $endDate = Carbon::createFromFormat('m/d/Y', $date[1])->endOfDay();
        } else {
            $startDate = Carbon::now()->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        }

        $code_kontrakan = $request->query('book', 'all');

        // Query transaksi masuk dan keluar
        $transaksiMasukQuery = TransaksiList::where('tipe', 'masuk')
            ->whereHas('transaksiMasuk', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
            });

        $transaksiKeluarQuery = TransaksiList::where('tipe', 'keluar')
            ->whereHas('transaksiKeluar', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
            });

        // Jika 'book' bukan 'all', filter berdasarkan 'code_kontrakan'
        if ($code_kontrakan !== 'all') {
            $transaksiMasukQuery->where('code_kontrakan', $code_kontrakan);
            $transaksiKeluarQuery->where('code_kontrakan', $code_kontrakan);
        }

        // Ambil data dengan relasi dan optimasi query menggunakan 'with'
        $transaksiMasuk = $transaksiMasukQuery->with('transaksiMasuk')->get();
        $transaksiKeluar = $transaksiKeluarQuery->with('transaksiKeluar')->get();

        // Mengelompokkan dan menghitung total pemasukan per code_kontrakan
        $groupedMasuk = $transaksiMasuk->groupBy('code_kontrakan')->map(function ($items) {
            $nama_kontrakan = Kontrakan::where('code_kontrakan', $items->first()->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown';
            return [
                'nama_kontrakan' => $nama_kontrakan,
                'total_masuk' => $items->sum('nominal')
            ];
        });

        // Mengelompokkan dan menghitung total pengeluaran per code_kontrakan
        $groupedKeluar = $transaksiKeluar->groupBy('code_kontrakan')->map(function ($items) {
            $nama_kontrakan = Kontrakan::where('code_kontrakan', $items->first()->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown';
            return [
                'nama_kontrakan' => $nama_kontrakan,
                'total_keluar' => $items->sum('nominal')
            ];
        });

        // Gabungkan data pemasukan dan pengeluaran ke dalam satu result
        $result = [];

        foreach ($groupedMasuk as $code_kontrakan => $dataMasuk) {
            $result[$code_kontrakan] = [
                'nama_kontrakan' => $dataMasuk['nama_kontrakan'],
                'total_masuk' => $dataMasuk['total_masuk'],
                'total_keluar' => $groupedKeluar[$code_kontrakan]['total_keluar'] ?? 0 // Default pengeluaran 0 jika tidak ada
            ];
        }

        foreach ($groupedKeluar as $code_kontrakan => $dataKeluar) {
            if (!isset($result[$code_kontrakan])) {
                $result[$code_kontrakan] = [
                    'nama_kontrakan' => $dataKeluar['nama_kontrakan'],
                    'total_masuk' => 0, // Default pemasukan 0 jika tidak ada
                    'total_keluar' => $dataKeluar['total_keluar']
                ];
            }
        }

        // Mengembalikan data yang sudah dikelompokkan dalam bentuk JSON
        return response()->json([
            'data' => $result
        ]);
    }

    public function aktivitas()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Custom',
        ];

        return view('admin.laporan.custom.aktivitas', $data);
    }

    // nse
    public function get_aktivitas_custom(Request $request)
    {
        if (!empty($request->date)) {
            $date = explode(' - ', $request->input('date'));
            $dateStart = Carbon::createFromFormat('m/d/Y', $date[0])->startOfDay();
            $dateEnd = Carbon::createFromFormat('m/d/Y', $date[1])->endOfDay();
        } else {
            $dateStart = Carbon::now()->startOfDay();
            $dateEnd = Carbon::now()->endOfDay();
        }

        $code_kontrakan = $request->input('book');
        $transaksi = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->whereHas('transaksiMasuk', function ($query) use ($dateStart, $dateEnd) {
                $query->whereBetween('tanggal_transaksi', [$dateStart, $dateEnd]);
            })
            ->orWhereHas('transaksiKeluar', function ($query) use ($dateStart, $dateEnd) {
                $query->whereBetween('tanggal_transaksi', [$dateStart, $dateEnd]);
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
                    'transaksi' => $item->where('tipe', 'masuk')->pluck('transaksiMasuk'),

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
            'pageTitle' => 'Laporan Custom',
        ];

        return view('admin.laporan.custom.ringkasan', $data);
    }

    public function get_ringkasan_custom(Request $request)
    {
        if (!empty($request->date)) {
            $date = explode(' - ', $request->input('date'));
            $dateStart = Carbon::createFromFormat('m/d/Y', $date[0])->startOfDay();
            $dateEnd = Carbon::createFromFormat('m/d/Y', $date[1])->endOfDay();
        } else {
            $dateStart = Carbon::now()->startOfDay();
            $dateEnd = Carbon::now()->endOfDay();
        }

        $code_kontrakan = $request->input('book');

        $data['dates'] = [];
        $data['type'] = 'custom';
        $startDate = Carbon::createFromFormat('Y-m-d', $dateStart->format('Y-m-d'));
        $endDate = Carbon::createFromFormat('Y-m-d', $dateEnd->format('Y-m-d'));
        $dates = [];
        for ($dateI = $startDate; $dateI->lte($endDate); $dateI->addDay()) {
            $dates[] = $dateI->format('d M y');
        }
        $data['dates'] = $dates;

        $transaksi = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->whereHas('transaksiMasuk', function ($query) use ($dateStart, $dateEnd) {
                $query->whereBetween('tanggal_transaksi', [$dateStart, $dateEnd]);
            })
            ->orWhereHas('transaksiKeluar', function ($query) use ($dateStart, $dateEnd) {
                $query->whereBetween('tanggal_transaksi', [$dateStart, $dateEnd]);
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
                            $query->where('tanggal_transaksi', 'like', Carbon::parse($date)->format('Y-m-d') . '%');
                        })
                        ->where('tipe', 'keluar')
                        ->where('code_kontrakan', $item[0]->code_kontrakan);
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


        $data['grandTotalPengeluarans'] = [];
        foreach ($data['dates'] as $date) {
            $t = TransaksiList::with(['transaksiKeluar'])
                ->whereHas('transaksiKeluar', function ($query) use ($date) {
                    $query->where('tanggal_transaksi', 'like', Carbon::parse($date)->format('Y-m-d') . '%');
                })
                ->where('tipe', 'keluar');
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
                            $query->where('tanggal_transaksi', 'like', Carbon::parse($date)->format('Y-m-d') . "%");
                        })
                        ->where('tipe', 'masuk')
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
                    $query->where('tanggal_transaksi', 'like', Carbon::parse($date)->format('Y-m-d') . '%');
                })
                ->where('tipe', 'masuk');
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
                            $query->where('tanggal_transaksi', 'like', Carbon::parse($date)->format('Y-m-d') . '%');
                        })
                        ->where('tipe', 'masuk')
                        ->where('code_kontrakan', $item[0]->code_kontrakan);
                    $tKeluar = TransaksiList::with(['transaksiKeluar'])
                        ->whereHas('transaksiKeluar', function ($query) use ($date) {
                            $query->where('tanggal_transaksi', 'like', Carbon::parse($date)->format('Y-m-d') . '%');
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
                    $query->where('tanggal_transaksi', 'like', Carbon::parse($date)->format('Y-m-d') . '%');
                })
                ->where('tipe', 'masuk');
            $tKeluar = TransaksiList::with(['transaksiKeluar'])
                ->whereHas('transaksiKeluar', function ($query) use ($date) {
                    $query->where('tanggal_transaksi', 'like', Carbon::parse($date)->format('Y-m-d') . '%');
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
}
