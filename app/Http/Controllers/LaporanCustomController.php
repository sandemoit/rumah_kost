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
        // Mengambil parameter 'date' dan 'book' dari query string
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan tanggal Custom ini
        if (!$date) {
            $date = Carbon::now()->format('Y-m-d');
        } else {
            // Ubah format tanggal dari 'd-m-Y' ke 'Y-m-d'
            $date = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }

        // Menghitung saldo awal Custom
        $saldoAwalCustomQuery = TransaksiList::select('code_kontrakan', DB::raw('SUM(IF(tipe="masuk", nominal, -nominal)) as saldo_awal'))
            ->where(function ($query) use ($date) {
                $query->whereHas('transaksiMasuk', function ($query) use ($date) {
                    $query->whereDate('tanggal_transaksi', '<', $date);
                })->orWhereHas('transaksiKeluar', function ($query) use ($date) {
                    $query->whereDate('tanggal_transaksi', '<', $date);
                });
            })
            ->groupBy('code_kontrakan');

        if ($code_kontrakan !== 'all') {
            $saldoAwalCustomQuery->where('code_kontrakan', $code_kontrakan);
        }

        $saldoAwalCustom = $saldoAwalCustomQuery->get()->sum('saldo_awal');

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
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan tanggal hari ini
        if (!$date) {
            $date = Carbon::now()->format('Y-m-d');
        } else {
            // Ubah format tanggal dari 'd-m-Y' ke 'Y-m-d'
            $date = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
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

        // Mengelompokkan transaksi masuk berdasarkan code_kontrakan dan menghitung total pemasukan
        $groupedMasuk = $transaksiMasuk->groupBy('code_kontrakan')->map(function ($items) {
            return [
                'nama_kontrakan' => Kontrakan::where('code_kontrakan', $items->first()->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                'total_masuk' => $items->sum('nominal')
            ];
        });

        // Mengelompokkan transaksi keluar berdasarkan code_kontrakan dan menghitung total pengeluaran
        $groupedKeluar = $transaksiKeluar->groupBy('code_kontrakan')->map(function ($items) {
            return [
                'nama_kontrakan' => Kontrakan::where('code_kontrakan', $items->first()->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                'total_keluar' => $items->sum('nominal')
            ];
        });

        // Persiapan data yang akan diparsing dan dikirimkan dalam response JSON
        $result = [];

        // Loop pertama: Proses data pemasukan dan pengeluaran
        foreach ($groupedMasuk as $code_kontrakan => $dataMasuk) {
            $result[$code_kontrakan] = [
                'nama_kontrakan' => $dataMasuk['nama_kontrakan'],
                'total_masuk' => $dataMasuk['total_masuk'],
                'total_keluar' => $groupedKeluar[$code_kontrakan]['total_keluar'] ?? 0 // Pastikan pengeluaran default ke 0
            ];
        }

        // Loop kedua: Proses kontrakan yang hanya punya pengeluaran
        foreach ($groupedKeluar as $code_kontrakan => $dataKeluar) {
            if (!isset($result[$code_kontrakan])) {
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
                $query->whereBetween('periode_sewa', [$dateStart, $dateEnd]);
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
                $query->whereBetween('periode_sewa', [$dateStart, $dateEnd]);
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
                            $query->where('periode_sewa', 'like', Carbon::parse($date)->format('Y-m-d') . "%");
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
                    $query->where('periode_sewa', 'like', Carbon::parse($date)->format('Y-m-d') . '%');
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
                            $query->where('periode_sewa', 'like', Carbon::parse($date)->format('Y-m-d') . '%');
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
                    $query->where('periode_sewa', 'like', Carbon::parse($date)->format('Y-m-d') . '%');
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
