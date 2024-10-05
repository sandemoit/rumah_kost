<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kontrakan;
use App\Models\TransaksiList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanBulananController extends Controller
{
    public function bulanan()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Bulanan',
        ];

        return view('admin.laporan.bulanan.umum', $data);
    }

    public function umum()
    {

        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Bulanan',
        ];

        return view('admin.laporan.bulanan.umum', $data);
    }

    public function getAllBukuKas(Request $request)
    {
        // Mengambil parameter 'date' dan 'book' dari query string
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan bulan dan tahun saat ini
        if (!$date) {
            $date = Carbon::now()->format('Y-m');
        }

        // Pisahkan tahun dan bulan dari parameter 'date'
        $year = Carbon::createFromFormat('Y-m', $date)->year;
        $month = Carbon::createFromFormat('Y-m', $date)->month;

        // Menghitung saldo awal Bulan
        $saldoAwalBulanQuery = TransaksiList::select('code_kontrakan', DB::raw('SUM(IF(tipe="masuk", nominal, -nominal)) as saldo_awal'))
            ->where(function ($query) use ($year, $month) {
                $query->whereHas('transaksiMasuk', function ($query) use ($year, $month) {
                    $query->whereYear('tanggal_transaksi', '<', $year)
                        ->orWhere(function ($query) use ($year, $month) {
                            $query->whereYear('tanggal_transaksi', '=', $year)
                                ->whereMonth('tanggal_transaksi', '<', $month);
                        });
                })->orWhereHas('transaksiKeluar', function ($query) use ($year, $month) {
                    $query->whereYear('tanggal_transaksi', '<', $year)
                        ->orWhere(function ($query) use ($year, $month) {
                            $query->whereYear('tanggal_transaksi', '=', $year)
                                ->whereMonth('tanggal_transaksi', '<', $month);
                        });
                });
            })
            ->groupBy('code_kontrakan');

        if ($code_kontrakan !== 'all') {
            $saldoAwalBulanQuery->where('code_kontrakan', $code_kontrakan);
        }

        $saldoAwalBulan = $saldoAwalBulanQuery->get()->sum('saldo_awal');

        // Menghitung semua pemasukan dan pengeluaran pada bulan tertentu
        $transaksiListQuery = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->where(function ($query) use ($month, $year) {
                $query->whereHas('transaksiMasuk', function ($query) use ($month, $year) {
                    $query->whereYear('tanggal_transaksi', '=', $year)
                        ->whereMonth('tanggal_transaksi', '=', $month);
                })->orWhereHas('transaksiKeluar', function ($query) use ($month, $year) {
                    $query->whereYear('tanggal_transaksi', '=', $year)
                        ->whereMonth('tanggal_transaksi', '=', $month);
                });
            });

        if ($code_kontrakan !== 'all') {
            $transaksiListQuery->where('code_kontrakan', $code_kontrakan);
        }

        $transaksiList = $transaksiListQuery->get();

        $semuaPemasukan = $transaksiList->where('tipe', 'masuk')->sum('nominal');
        $semuaPengeluaran = $transaksiList->where('tipe', 'keluar')->sum('nominal');

        // Menghitung akumulasi dan saldo akhir Bulan
        $akumulasi = $semuaPemasukan - $semuaPengeluaran;
        $saldoAkhirBulan = $saldoAwalBulan + $akumulasi;

        // Mengembalikan data dalam format JSON
        return response()->json([
            'date' => $date,
            // 'saldoAwalBulan' => rupiah($saldoAwalBulan),
            'semuaPemasukan' => rupiah($semuaPemasukan),
            'semuaPengeluaran' => rupiah($semuaPengeluaran),
            'akumulasi' => rupiah($akumulasi),
            // 'saldoAkhirBulan' => rupiah($saldoAkhirBulan),
        ]);
    }

    public function getAllExIn(Request $request)
    {
        // Mengambil parameter 'date' dan 'book' dari query string
        $date = $request->query('date');
        $code_kontrakan = $request->query('book', 'all');

        // Jika tidak ada parameter 'date' yang diberikan, gunakan bulan dan tahun saat ini
        if (!$date) {
            $date = Carbon::now()->format('Y-m');
        } else {
            try {
                // Ubah format tanggal dari 'Y-m' ke 'Y-m'
                $date = Carbon::createFromFormat('Y-m', $date)->format('Y-m');
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid date format. Please use Y-m format.'], 400);
            }
        }

        // Pisahkan tahun dan bulan dari parameter 'date'
        $year = Carbon::createFromFormat('Y-m', $date)->year;
        $month = Carbon::createFromFormat('Y-m', $date)->month;

        // Query transaksi masuk
        $transaksiMasukQuery = TransaksiList::select('code_kontrakan', 'nominal', 'tipe', 'id_kamar', 'id_masuk')
            ->where('tipe', 'masuk')
            ->whereHas('transaksiMasuk', function ($query) use ($year, $month) {
                $query->whereYear('tanggal_transaksi', $year)
                    ->whereMonth('tanggal_transaksi', $month);
            });

        // Query transaksi keluar
        $transaksiKeluarQuery = TransaksiList::select('code_kontrakan', 'nominal', 'tipe', 'id_kamar', 'id_keluar')
            ->where('tipe', 'keluar')
            ->whereHas('transaksiKeluar', function ($query) use ($year, $month) {
                $query->whereYear('tanggal_transaksi', $year)
                    ->whereMonth('tanggal_transaksi', $month);
            });

        // Filter berdasarkan code_kontrakan jika ada
        if ($code_kontrakan !== 'all') {
            $transaksiMasukQuery->where('code_kontrakan', $code_kontrakan);
            $transaksiKeluarQuery->where('code_kontrakan', $code_kontrakan);
        }

        // Ambil hasil query transaksi
        $transaksiMasuk = $transaksiMasukQuery->with('kamar', 'transaksiMasuk')->get();
        $transaksiKeluar = $transaksiKeluarQuery->with('kamar', 'transaksiKeluar')->get();

        // Ambil semua kamar yang terkait dengan setiap kontrakan
        $allKamarByKontrakan = Kamar::with('kontrakan')->get()->groupBy('id_kontrakan')->map(function ($kamar) {
            return $kamar->pluck('id')->toArray();
        });

        // Looping untuk transaksi masuk
        foreach ($transaksiMasuk as $transaksi) {
            // Ambil nama kamar berdasarkan id_kamar
            $id_kamar = json_decode($transaksi->id_kamar, true);
            if ($id_kamar) {
                $kamar = Kamar::whereIn('id', $id_kamar)->get();
                $transaksi->nama_kamar = $kamar->pluck('nama_kamar')->implode(', ') ?? 'Unknown';
            }
        }

        // Looping untuk transaksi keluar
        foreach ($transaksiKeluar as $transaksi) {
            $id_kamar = json_decode($transaksi->id_kamar, true);

            if ($id_kamar) {
                // Ambil data kontrakan dari relasi kamar
                $kontrakan = Kamar::whereIn('id', $id_kamar)->with('kontrakan')->get()->first()->kontrakan;
                $nama_kontrakan = $kontrakan->nama_kontrakan;

                // Jika semua kamar dari kontrakan tersebut dipilih
                if ($allKamarByKontrakan->has($kontrakan->id) && $allKamarByKontrakan->get($kontrakan->id) == $id_kamar) {
                    $transaksi->nama_kamar = "$nama_kontrakan (All)";
                } else {
                    // Jika hanya sebagian kamar dipilih, ambil nama kamar dan bagi nominal
                    $kamar = Kamar::whereIn('id', $id_kamar)->get();
                    $transaksi->nama_kamar = $kamar->pluck('nama_kamar')->toArray();
                }
            } else {
                $transaksi->nama_kamar = ['Unknown'];
            }
        }

        return response()->json([
            'transaksiMasuk' => $transaksiMasuk,
            'transaksiKeluar' => $transaksiKeluar,
        ]);
    }

    public function aktivitas()
    {
        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'kontrakan' => $kontrakan,
            'pageTitle' => 'Laporan Bulanan',
        ];

        return view('admin.laporan.bulanan.aktivitas', $data);
    }

    // nse
    public function get_aktivitas_bulanan(Request $request)
    {
        $date = $request->input('date');
        $code_kontrakan = $request->input('book');
        DB::enableQueryLog();
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
            'pageTitle' => 'Laporan Bulanan',
        ];

        return view('admin.laporan.bulanan.ringkasan', $data);
    }

    public function get_ringkasan_bulanan(Request $request)
    {
        $year = $request->input('date');
        $code_kontrakan = $request->input('book');

        $data['dates'] = [];
        $data['type'] = 'bulanan';
        for ($i = 1; $i <= 12; $i++) {
            $dt = strlen($i) == 1 ? '0' . $i : $i;
            $data['dates'][] = "$year-$dt";
        }

        $transaksi = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->whereHas('transaksiMasuk', function ($query) use ($year) {
                $query->where('tanggal_transaksi', 'like', $year . "%");
            })
            ->orWhereHas('transaksiKeluar', function ($query) use ($year) {
                $query->where('tanggal_transaksi', 'like', $year . "%");
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
                            $query->where('tanggal_transaksi', 'like', $date . '%');
                        })
                        ->where('tipe', 'keluar')
                        ->where('code_kontrakan', $item[0]->code_kontrakan);
                    $trx[$date] = $t->sum('nominal') ?? 0;
                }
                return [
                    'nama_kontrakan' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                    'qty' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->kamar->count(),
                    'total' => collect($trx)->sum(),
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
