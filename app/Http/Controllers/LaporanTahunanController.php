<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kontrakan;
use App\Models\TransaksiList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanTahunanController extends Controller
{
    public function tahunan()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Tahunan',
        ];

        return view('admin.laporan.tahunan.umum', $data);
    }

    public function umum()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Tahunan',
        ];

        return view('admin.laporan.tahunan.umum', $data);
    }

    public function getAllBukuKas(Request $request)
    {
        // Mengambil parameter 'date' dan 'book' dari query string
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan Tahun saat ini
        if (!$date) {
            $date = Carbon::now()->format('Y');
        } else {
            try {
                // Ubah format tanggal dari 'Y' ke 'Y'
                $date = Carbon::createFromFormat('Y', $date)->format('Y');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format. Please use Y format.'], 400);
            }
        }

        // Pisahkan tahun dari parameter 'date'
        $year = Carbon::createFromFormat('Y', $date)->year;

        // Menghitung saldo awal Tahun
        $saldoAwalTahunQuery = TransaksiList::select('code_kontrakan', DB::raw('SUM(IF(tipe="masuk", nominal, -nominal)) as saldo_awal'))
            ->where(function ($query) use ($year) {
                $query->whereHas('transaksiMasuk', function ($query) use ($year) {
                    $query->whereYear('tanggal_transaksi', '<', $year);
                })->orWhereHas('transaksiKeluar', function ($query) use ($year) {
                    $query->whereYear('tanggal_transaksi', '<', $year);
                });
            })
            ->groupBy('code_kontrakan');

        if ($code_kontrakan !== 'all') {
            $saldoAwalTahunQuery->where('code_kontrakan', $code_kontrakan);
        }

        $saldoAwalTahun = $saldoAwalTahunQuery->get()->sum('saldo_awal');

        // Menghitung semua pemasukan dan pengeluaran pada Tahun tertentu
        $transaksiListQuery = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->where(function ($query) use ($year) {
                $query->whereHas('transaksiMasuk', function ($query) use ($year) {
                    $query->whereYear('tanggal_transaksi', '=', $year);
                })->orWhereHas('transaksiKeluar', function ($query) use ($year) {
                    $query->whereYear('tanggal_transaksi', '=', $year);
                });
            });

        if ($code_kontrakan !== 'all') {
            $transaksiListQuery->where('code_kontrakan', $code_kontrakan);
        }

        $transaksiList = $transaksiListQuery->get();

        $semuaPemasukan = $transaksiList->where('tipe', 'masuk')->sum('nominal');
        $semuaPengeluaran = $transaksiList->where('tipe', 'keluar')->sum('nominal');

        // Menghitung akumulasi dan saldo akhir Tahun
        $akumulasi = $semuaPemasukan - $semuaPengeluaran;
        $saldoAkhirTahun = $saldoAwalTahun + $akumulasi;

        // Mengembalikan data dalam format JSON
        return response()->json([
            'date' => $date,
            'saldoAwalTahun' => rupiah($saldoAwalTahun),
            'semuaPemasukan' => rupiah($semuaPemasukan),
            'semuaPengeluaran' => rupiah($semuaPengeluaran),
            'akumulasi' => rupiah($akumulasi),
            'saldoAkhirTahun' => rupiah($saldoAkhirTahun),
        ]);
    }


    public function getAllExIn(Request $request)
    {
        // Mengambil parameter 'date' dan 'book' dari query string
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan Tahun dan tahun saat ini
        if (!$date) {
            $date = Carbon::now()->format('Y');
        } else {
            try {
                // Ubah format tanggal dari 'Y' ke 'Y'
                $date = Carbon::createFromFormat('Y', $date)->format('Y');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format. Please use Y format.'], 400);
            }
        }

        // Pisahkan tahun dan Tahun dari parameter 'date'
        $year = Carbon::createFromFormat('Y', $date)->year;

        $transaksiMasukQuery = TransaksiList::where('tipe', 'masuk')
            ->whereHas('transaksiMasuk', function ($query) use ($year) {
                $query->whereYear('tanggal_transaksi', $year);
            });


        $transaksiKeluarQuery = TransaksiList::where('tipe', 'keluar')
            ->whereHas('transaksiKeluar', function ($query) use ($year) {
                $query->whereYear('tanggal_transaksi', $year);
            });

        if ($code_kontrakan !== 'all') {
            $transaksiKeluarQuery->where('code_kontrakan', $code_kontrakan);
            $transaksiMasukQuery->where('code_kontrakan', $code_kontrakan);
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
            'pageTitle' => 'Laporan Tahunan',
        ];

        return view('admin.laporan.tahunan.aktivitas', $data);
    }

    // nse
    public function get_aktivitas_tahunan(Request $request)
    {
        $date = $request->input('date');
        $code_kontrakan = $request->input('book');
        $transaksi = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->whereHas('transaksiMasuk', function ($query) use ($date) {
                $query->where('tanggal_transaksi', 'like', $date . "%");
            })
            ->orWhereHas('transaksiKeluar', function ($query) use ($date) {
                $query->where('tanggal_transaksi', 'like', $date . "%");
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
            'pageTitle' => 'Laporan Tahunan',
        ];

        return view('admin.laporan.tahunan.ringkasan', $data);
    }

    public function get_ringkasan_tahunan(Request $request)
    {

        $code_kontrakan = $request->input('book');

        $data['dates'] = [];
        $data['type'] = 'Tahunan';
        for ($i = 3; $i >= 0; $i--) {
            $data['dates'][] = Carbon::now()->subYears($i)->format('Y');
        }

        $transaksi = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
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
                            $query->where('tanggal_transaksi', 'like', $date . '%');
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
                    $query->where('tanggal_transaksi', 'like', $date . '%');
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
                            $query->where('tanggal_transaksi', 'like', $date . "%");
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
                    $query->where('tanggal_transaksi', 'like', $date . '%');
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
                            $query->where('tanggal_transaksi', 'like', $date . '%');
                        })
                        ->where('tipe', 'masuk')
                        ->where('code_kontrakan', $item[0]->code_kontrakan);
                    $tKeluar = TransaksiList::with(['transaksiKeluar'])
                        ->whereHas('transaksiKeluar', function ($query) use ($date) {
                            $query->where('tanggal_transaksi', 'like', $date . '%');
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
                    $query->where('tanggal_transaksi', 'like', $date . '%');
                })
                ->where('tipe', 'masuk');
            $tKeluar = TransaksiList::with(['transaksiKeluar'])
                ->whereHas('transaksiKeluar', function ($query) use ($date) {
                    $query->where('tanggal_transaksi', 'like', $date . '%');
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
