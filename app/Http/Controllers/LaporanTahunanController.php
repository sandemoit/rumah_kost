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

        // Query transaksi masuk dengan groupBy kamar dan agregasi nominal
        $transaksiMasukQuery = TransaksiList::select('code_kontrakan', 'id_kamar', DB::raw('SUM(nominal) as total_nominal'))
            ->where('tipe', 'masuk')
            ->whereHas('transaksiMasuk', function ($query) use ($year) {
                $query->whereYear('tanggal_transaksi', $year);
            })
            ->groupBy('id_kamar', 'code_kontrakan');

        // Query transaksi keluar dengan groupBy kamar dan agregasi nominal
        $transaksiKeluarQuery = TransaksiList::select('code_kontrakan', 'id_kamar', DB::raw('SUM(nominal) as total_nominal'))
            ->where('tipe', 'keluar')
            ->whereHas('transaksiKeluar', function ($query) use ($year) {
                $query->whereYear('tanggal_transaksi', $year);
            })
            ->groupBy('id_kamar', 'code_kontrakan');

        // Filter berdasarkan code_kontrakan jika ada
        if ($code_kontrakan !== 'all') {
            $transaksiMasukQuery->where('code_kontrakan', $code_kontrakan);
            $transaksiKeluarQuery->where('code_kontrakan', $code_kontrakan);
        }

        // Ambil hasil query transaksi masuk dan keluar
        $transaksiMasuk = $transaksiMasukQuery->with(['kamar' => function ($q) {
            $q->orderBy('nama_kamar');
        }, 'transaksiMasuk'])->get();
        $transaksiKeluar = $transaksiKeluarQuery->with(['kamar' => function ($q) {
            $q->orderBy('nama_kamar');
        }, 'transaksiKeluar'])->get();

        // Ambil semua kamar yang terkait dengan setiap kontrakan
        $allKamarByKontrakan = Kamar::with('kontrakan')->get()->groupBy('id_kontrakan')->map(function ($kamar) {
            return $kamar->pluck('id')->toArray();
        });

        // Looping untuk transaksi masuk
        foreach ($transaksiMasuk as $transaksi) {
            $id_kamar = json_decode($transaksi->id_kamar, true);
            if ($id_kamar) {
                $kamar = Kamar::whereIn('id', $id_kamar)->get();
                $transaksi->nama_kamar = $kamar->pluck('nama_kamar')->implode(', ') ?? 'Unknown';
            }
        }

        // Looping untuk transaksi keluar
        foreach ($transaksiKeluar as $transaksi) {
            // Decode id_kamar JSON array
            $id_kamar = json_decode($transaksi->id_kamar, true);

            // Jika id_kamar ada, proses tiap elemen
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


        $html = view('components.ringkasan_tahunan', $data)->render();
        return response()->json(['html' => $html]);
    }
}
