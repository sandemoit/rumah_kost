<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\Kontrakan;
use App\Models\Penyewa;
use App\Models\TransaksiList;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportRingkasanController extends Controller
{
    public function harian(Request $request)
    {
        $data = $this->get_harian($request->input('date'), $request->input('book'), 'harian');
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $profit = [];

        foreach($data['kontrakan'] as $kontrakan) {
            $workSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $kontrakan);
            $spreadsheet->addSheet($workSheet);
            
        $rowTotalPemasukan[$kontrakan] = 3;
        }
        
        // loop every sheets
        foreach ($data['per_kontrakan']['pemasukan'] as $pemasukan) {
            $workSheet = $spreadsheet->getSheetByName($pemasukan['nama_kontrakan']);
            // set header table pemasukan
            $workSheet->setCellValue('A1', 'PEMASUKAN HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')));
            $workSheet->setCellValue('A2', 'Kamar');
            $workSheet->setCellValue('B2', 'Nama Penyewa');
            $workSheet->setCellValue('C2', 'Tgl Masuk');
            $workSheet->setCellValue('D2', 'Price');
            $char = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . '2';
                $workSheet->setCellValue($cell, Carbon::parse($p)->format('d'));
                $char++;
            }
            $workSheet->setCellValue($char . '2', 'Total');

            // set data table
            $cursorRow = 3;
            foreach ($pemasukan['penyewa'] as $p) {
                $workSheet->setCellValue('A' . $cursorRow, $p['nama_kamar']);
                $workSheet->setCellValue('B' . $cursorRow, $p['penyewa']);
                $workSheet->setCellValue('C' . $cursorRow, $p['periode_sewa']);
                $workSheet->setCellValue('D' . $cursorRow, $p['price']);

                $char = 'E';
                $prevChr = '';
                foreach ($data['dates'] as $d) {
                    $cell = $char . $cursorRow;
                    $workSheet->setCellValue($cell, $p['transaksi']->where('transaksiMasuk.periode_sewa', $d)->sum('nominal') ?? 0);
                    $prevChr = $char;
                    $char++;
                }
                $workSheet->setCellValue($char . $cursorRow, '=SUM(E' . $cursorRow . ':' . $prevChr . $cursorRow . ')');
                $cursorRow++;
            }
            $char = 'E';
            $workSheet->setCellValue('A' . $cursorRow, 'Total');
            foreach ($data['dates'] as $d) {
                $cell = $char . $cursorRow;

                $workSheet->setCellValue($cell, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');
                $char++;
            }
            $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');
            $rowTotalPemasukan[$pemasukan['nama_kontrakan']] = $cursorRow;

            $cursorRow += 2;
            $cur[$pemasukan['nama_kontrakan']] = $cursorRow;
        }

        // prevent cursor out of bound
        foreach($data['kontrakan'] as $kontrakan){
            if(!isset($cur[$kontrakan])){
                $cur[$kontrakan] = 6;
            }
        }

        foreach ($data['per_kontrakan']['pengeluaran'] as $pengeluaran) {
            $workSheet = $spreadsheet->getSheetByName($pengeluaran['nama_kontrakan']);
            
            // set header table pengeluaran
            $cur[$pengeluaran['nama_kontrakan']]++;
            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'PENGELUARAN HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')));
            $cur[$pengeluaran['nama_kontrakan']]++;
            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'Kamar');
            $workSheet->setCellValue('B' . $cur[$pengeluaran['nama_kontrakan']], 'Item Pengeluaran');
            $workSheet->mergeCells('B' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);
            $char = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                $workSheet->setCellValue($cell, Carbon::parse($p)->format('d'));
                $char++;
            }
            $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], 'Total');

            // set data table
            $cur[$pengeluaran['nama_kontrakan']]++;
            foreach ($pengeluaran['transaksi'] as $p) {

                $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], $p['kamar']);
                $workSheet->setCellValue('B' . $cur[$pengeluaran['nama_kontrakan']], $p['deskripsi']);
                $workSheet->mergeCells('B' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);

                $char = 'E';
                $prevChr = '';
                foreach ($data['dates'] as $d) {
                    $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                    if ($p['tanggal_transaksi'] == $d) {
                        $workSheet->setCellValue($cell, $p['nominal']);
                    } else {
                        $workSheet->setCellValue($cell, 0);
                    }
                    $prevChr = $char;
                    $char++;
                }
                $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], '=SUM(E' . $cur[$pengeluaran['nama_kontrakan']] . ':' . $prevChr . $cur[$pengeluaran['nama_kontrakan']] . ')');
                $cur[$pengeluaran['nama_kontrakan']]++;
            }
            $char = 'E';
            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'Total');
            foreach ($data['dates'] as $d) {
                $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                $workSheet->setCellValue($cell, '=SUM(' . $char . ($cur[$pengeluaran['nama_kontrakan']] - count($pengeluaran['transaksi'])) . ':' . $char . ($cur[$pengeluaran['nama_kontrakan']] - 1) . ')');
                $char++;
            }
            $rowTotalPengeluaran[$pengeluaran['nama_kontrakan']] = $cur[$pengeluaran['nama_kontrakan']];
            $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], '=SUM(' . $char . ($cur[$pengeluaran['nama_kontrakan']] - count($pengeluaran['transaksi'])) . ':' . $char . ($cur[$pengeluaran['nama_kontrakan']] - 1) . ')');
        }

        $sheetNames = $spreadsheet->getSheetNames();
        // sum sheet
        $workSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'sum');
        $spreadsheet->addSheet($workSheet, 0);

        // set table pemasukan
        $workSheet->setCellValue('B1', 'PEMASUKAN HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')));
        $workSheet->setCellValue('B2', 'Kontrakan');
        $workSheet->setCellValue('C2', 'Qty');
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . 2;
            $workSheet->setCellValue($cell, Carbon::parse($p)->format('d'));
            $char++;
        }
        $workSheet->setCellValue($char . 2, 'T O T A L');
        $cursorRow = 3;
        foreach ($sheetNames as $sheet) {
            $workSheet->setCellValue('B' . $cursorRow, $sheet);
            $char = 'D';
            $charTotalPemasukan = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . $cursorRow;
                $workSheet->setCellValue($cell, '=' . $sheet . '!' . $charTotalPemasukan++ . $rowTotalPemasukan[$sheet]);
                $profit[$sheet][$p]['pemasukan'] = $cell;
                $char++;
            }
            $workSheet->setCellValue($char++ . $cursorRow, '=' . $sheet . '!' . $charTotalPemasukan . $rowTotalPemasukan[$sheet]);
            $cursorRow++;
        }

        // set footer table pemasukan
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->setCellValue($cell, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');
            $char++;

        }
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');




        // set table pengeluaran
        $cursorRow += 2;
        $workSheet->setCellValue('B' . $cursorRow++, 'PENGELUARAN HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')));
        $workSheet->setCellValue('B' . $cursorRow, 'Kontrakan');
        $workSheet->setCellValue('C' . $cursorRow, 'Qty');
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->setCellValue($cell, Carbon::parse($p)->format('d'));
            $char++;
        }
        $workSheet->setCellValue($char . $cursorRow, 'T O T A L');
        $cursorRow++;
        // set data pengeluaran
        $startRowPengeluaran = $cursorRow;
        foreach ($sheetNames as $sheet) {
            $workSheet->setCellValue('B' . $cursorRow, $sheet);
            $char = 'D';
            $charTotalPengeluaran = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . $cursorRow;
                $workSheet->setCellValue($cell, '=' . $sheet . '!' . $charTotalPengeluaran++ . $rowTotalPengeluaran[$sheet]);
                $profit[$sheet][$p]['pengeluaran'] = $cell;
                $char++;

            }
            $workSheet->setCellValue($char++ . $cursorRow, '=' . $sheet . '!' . $charTotalPengeluaran . $rowTotalPengeluaran[$sheet]);
            $cursorRow++;
        }

        // set footer table pemasukan
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->setCellValue($cell, '=SUM(' . $char . $startRowPengeluaran . ':' . $char . ($cursorRow - 1) . ')');
            $char++;
        }
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . $startRowPengeluaran . ':' . $char . ($cursorRow - 1) . ')');


        // set profit
        $cursorRow += 2;
        $workSheet->setCellValue('B' . $cursorRow++, 'PROFIT HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')));
        $workSheet->setCellValue('B' . $cursorRow, 'Kontrakan');
        $workSheet->setCellValue('C' . $cursorRow, 'Qty');
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->setCellValue($cell, Carbon::parse($p)->format('d'));
            $char++;
        }
        $workSheet->setCellValue($char . $cursorRow, 'T O T A L');
        // set data profit
        $cursorRow++;
        $startRowProfit = $cursorRow;
        foreach ($profit as $key => $value) {
            $workSheet->setCellValue('B' . $cursorRow, $key);
            $char = 'D';
            $prevChar = '';
            foreach ($value as $pr) {
                $cell = $char . $cursorRow;
                $workSheet->setCellValue($cell, '=' . $pr['pemasukan'] . '-' . $pr['pengeluaran']);
                $prevChar = $char;
                $char++;
            }
            $workSheet->setCellValue($char . $cursorRow, "=SUM(D$cursorRow:$prevChar$cursorRow)");
            $cursorRow++;
        }
        ;
        //set footer table profit
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->setCellValue($cell, '=SUM(' . $char . $startRowProfit . ':' . $char . ($cursorRow - 1) . ')');
            $char++;
        }
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . $startRowProfit . ':' . $char . ($cursorRow - 1) . ')');
        $writer = new Xlsx($spreadsheet);
        $writer->save('ringkasan.xlsx');
        return response()->download('ringkasan.xlsx', 'ringkasan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function get_harian($date, $code_kontrakan, $type = 'harian')
    {

        
        if ($type == 'harian') {
            $data['dates'] = [];
            for ($i = 0; $i < Carbon::parse($date)->daysInMonth; $i++) {
                $data['dates'][] = Carbon::parse($date)->addDays($i)->format('Y-m-d');
            }
        }else{
            $data['dates'] = [];
            for ($i = 1; $i <= 12; $i++) {
                $dt = strlen($i) == 1 ? '0' . $i : $i;
                $data['dates'][] = "$date-$dt";
            }

        }
        $dateParam = Carbon::parse($date)->format('Y-m');
        if ($type == 'bulanan') {
            $dateParam = Carbon::parse($date)->format('Y');
        }
        if($code_kontrakan == 'all'){
            $data['kontrakan'] = Kontrakan::all()->pluck('nama_kontrakan');
        }else{
            $data['kontrakan'] = Kontrakan::where('code_kontrakan', $code_kontrakan)->pluck('nama_kontrakan')->toArray();
        }
        $transaksi = TransaksiList::with(['transaksiMasuk', 'transaksiKeluar'])
            ->whereHas('transaksiMasuk', function ($query) use ($dateParam) {

                $query->where('periode_sewa', 'like', $dateParam . "%");
            })
            ->orWhereHas('transaksiKeluar', function ($query) use ($dateParam) {
                $query->where('tanggal_transaksi', 'like', $dateParam . "%");
            })
            ->get();
        if ($code_kontrakan !== 'all' && $code_kontrakan !== null) {
            $transaksi = $transaksi->where('code_kontrakan', $code_kontrakan);
        }

        $data['per_kontrakan']['pemasukan'] = $transaksi
            ->where('tipe', 'masuk')
            ->groupBy('code_kontrakan')
            ->map(function ($item) use ($data, $dateParam, $code_kontrakan) {


                return [
                    'nama_kontrakan' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                    'qty' => $item->count(),
                    'total' => $item->where('tipe', 'masuk')->sum('nominal'),
                    'penyewa' => Penyewa::whereHas('transaksiList', function ($query) use ($item, $dateParam) {
                        $query->where('code_kontrakan', $item[0]->code_kontrakan);
                        $query->where('tipe', 'masuk');
                        $query->whereHas('transaksiMasuk', function ($query) use ($dateParam) {
                            $query->where('periode_sewa', 'like', $dateParam . "%");
                        });
                    })->get()
                        ->map(function ($item) use ($data, $code_kontrakan) {
                            $trx = [];
                            foreach ($data['dates'] as $date) {
                                $t = TransaksiList::with(['transaksiMasuk'])
                                    ->whereHas('transaksiMasuk', function ($query) use ($date) {
                                        $query->where('periode_sewa', 'like', $date . "%");
                                    })
                                    ->where('code_kontrakan', $code_kontrakan);
                                if ($code_kontrakan !== 'all' && $code_kontrakan !== null) {
                                    $t = $t->where('code_kontrakan', $code_kontrakan);
                                }
                                $trx[$date] = $t->sum('nominal') ?? 0;
                            }
                            return [
                                'nama_kamar' => $item->kamar->nama_kamar ?? 'Unknown',
                                'transaksi' => $item->transaksiList,
                                'penyewa' => $item->nama_penyewa,
                                'periode_sewa' => Carbon::parse($item->tanggal_masuk)->format('d-M'),
                                'price' => $item->kamar->harga_kamar,
                            ];
                        }),
                ];
            })
            ->values();

        $data['per_kontrakan']['pengeluaran'] = $transaksi
            ->where('tipe', 'keluar')
            ->groupBy('code_kontrakan')
            ->map(function ($item) use ($data, $date, $code_kontrakan) {

                return [
                    'nama_kontrakan' => Kontrakan::where('code_kontrakan', $item[0]->code_kontrakan)->first()->nama_kontrakan ?? 'Unknown',
                    'transaksi' => $item->map(function ($item) {
                        $kamar = Kamar::whereIn('id', json_decode($item->id_kamar))->pluck('nama_kamar')->implode(', ');
                        return [
                            'kamar' => $kamar,
                            'nominal' => $item->nominal,
                            'deskripsi' => $item->transaksiKeluar->deskripsi,
                            'tanggal_transaksi' => $item->transaksiKeluar->tanggal_transaksi,
                        ];
                    }),
                ];
            })
            ->values();


        return collect($data);
    }

    public function bulanan(Request $request)
    {

        $data = $this->get_harian($request->input('date'), $request->input('book'), 'bulanan');
        
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $profit = [];
        // loop every sheets
        foreach ($data['per_kontrakan']['pemasukan'] as $pemasukan) {
            $workSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $pemasukan['nama_kontrakan']);
            $spreadsheet->addSheet($workSheet);
            // set header table pemasukan
            $workSheet->setCellValue('A1', 'PEMASUKAN');
            $workSheet->setCellValue('A2', 'Kamar');
            $workSheet->setCellValue('B2', 'Nama Penyewa');
            $workSheet->setCellValue('C2', 'Tgl Masuk');
            $workSheet->setCellValue('D2', 'Price');
            $char = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . '2';
                $workSheet->setCellValue($cell, Carbon::parse($p)->format('M'));
                $char++;
            }
            $workSheet->setCellValue($char . '2', 'Total');
            // set data table
            $cursorRow = 3;
            foreach ($pemasukan['penyewa'] as $p) {
                $workSheet->setCellValue('A' . $cursorRow, $p['nama_kamar']);
                $workSheet->setCellValue('B' . $cursorRow, $p['penyewa']);
                $workSheet->setCellValue('C' . $cursorRow, $p['periode_sewa']);
                $workSheet->setCellValue('D' . $cursorRow, $p['price']);

                $char = 'E';
                $prevChr = '';
                foreach ($data['dates'] as $d) {
                    $cell = $char . $cursorRow;
                    $workSheet->setCellValue($cell, $p['transaksi']->filter(function($item) use ($d) {
                        return str_contains($item->transaksiMasuk->periode_sewa, $d);
                    })->sum('nominal') ?? 0);
                    $prevChr = $char;
                    $char++;
                }
                $workSheet->setCellValue($char . $cursorRow, '=SUM(E' . $cursorRow . ':' . $prevChr . $cursorRow . ')');
                $cursorRow++;
            }
            $char = 'E';
            $workSheet->setCellValue('A' . $cursorRow, 'Total');
            foreach ($data['dates'] as $d) {
                $cell = $char . $cursorRow;

                $workSheet->setCellValue($cell, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');
                $char++;
            }
            $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');
            $rowTotalPemasukan[$pemasukan['nama_kontrakan']] = $cursorRow;

            $cursorRow += 2;
            $cur[$pemasukan['nama_kontrakan']] = $cursorRow;
        }

        foreach ($data['per_kontrakan']['pengeluaran'] as $pengeluaran) {
            $workSheet = $spreadsheet->getSheetByName($pengeluaran['nama_kontrakan']);
            // set header table pengeluaran
            $cur[$pengeluaran['nama_kontrakan']]++;
            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'PENGELUARAN HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')));
            $cur[$pengeluaran['nama_kontrakan']]++;
            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'Kamar');
            $workSheet->setCellValue('B' . $cur[$pengeluaran['nama_kontrakan']], 'Item Pengeluaran');
            $workSheet->mergeCells('B' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);
            $char = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                $workSheet->setCellValue($cell, Carbon::parse($p)->format('M'));
                $char++;
            }
            $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], 'Total');

            // set data table
            $cur[$pengeluaran['nama_kontrakan']]++;
            foreach ($pengeluaran['transaksi'] as $p) {

                $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], $p['kamar']);
                $workSheet->setCellValue('B' . $cur[$pengeluaran['nama_kontrakan']], $p['deskripsi']);
                $workSheet->mergeCells('B' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);

                $char = 'E';
                $prevChr = '';
                foreach ($data['dates'] as $d) {
                    $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                    if (Carbon::parse($p['tanggal_transaksi'])->format('Y-m') == $d) {
                        $workSheet->setCellValue($cell, $p['nominal']);
                    } else {
                        $workSheet->setCellValue($cell, 0);
                    }
                    $prevChr = $char;
                    $char++;
                }
                $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], '=SUM(E' . $cur[$pengeluaran['nama_kontrakan']] . ':' . $prevChr . $cur[$pengeluaran['nama_kontrakan']] . ')');
                $cur[$pengeluaran['nama_kontrakan']]++;
            }
            $char = 'E';
            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'Total');
            foreach ($data['dates'] as $d) {
                $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                $workSheet->setCellValue($cell, '=SUM(' . $char . ($cur[$pengeluaran['nama_kontrakan']] - count($pengeluaran['transaksi'])) . ':' . $char . ($cur[$pengeluaran['nama_kontrakan']] - 1) . ')');
                $char++;
            }
            $rowTotalPengeluaran[$pengeluaran['nama_kontrakan']] = $cur[$pengeluaran['nama_kontrakan']];
            $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], '=SUM(' . $char . ($cur[$pengeluaran['nama_kontrakan']] - count($pengeluaran['transaksi'])) . ':' . $char . ($cur[$pengeluaran['nama_kontrakan']] - 1) . ')');
        }

        $sheetNames = $spreadsheet->getSheetNames();
        // sum sheet
        $workSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'sum');
        $spreadsheet->addSheet($workSheet, 0);

        // set table pemasukan
        $workSheet->setCellValue('B1', 'PEMASUKAN');
        $workSheet->setCellValue('B2', 'Kontrakan');
        $workSheet->setCellValue('C2', 'Qty');
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . 2;
            $workSheet->setCellValue($cell, Carbon::parse($p)->format('M'));
            $char++;
        }
        $workSheet->setCellValue($char . 2, 'T O T A L');
        $cursorRow = 3;
        foreach ($sheetNames as $sheet) {
            $workSheet->setCellValue('B' . $cursorRow, $sheet);
            $char = 'D';
            $charTotalPemasukan = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . $cursorRow;
                $workSheet->setCellValue($cell, '=' . $sheet . '!' . $charTotalPemasukan++ . $rowTotalPemasukan[$sheet]);
                $profit[$sheet][$p]['pemasukan'] = $cell;
                $char++;
            }
            $workSheet->setCellValue($char++ . $cursorRow, '=' . $sheet . '!' . $charTotalPemasukan . $rowTotalPemasukan[$sheet]);
            $cursorRow++;
        }

        // set footer table pemasukan
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->setCellValue($cell, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');
            $char++;

        }
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');




        // set table pengeluaran
        $cursorRow += 2;
        $workSheet->setCellValue('B' . $cursorRow++, 'PENGELUARAN');
        $workSheet->setCellValue('B' . $cursorRow, 'Kontrakan');
        $workSheet->setCellValue('C' . $cursorRow, 'Qty');
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->setCellValue($cell, Carbon::parse($p)->format('M'));
            $char++;
        }
        $workSheet->setCellValue($char . $cursorRow, 'T O T A L');
        $cursorRow++;
        // set data pengeluaran
        $startRowPengeluaran = $cursorRow;
        foreach ($sheetNames as $sheet) {
            $workSheet->setCellValue('B' . $cursorRow, $sheet);
            $char = 'D';
            $charTotalPengeluaran = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . $cursorRow;
                $workSheet->setCellValue($cell, '=' . $sheet . '!' . $charTotalPengeluaran++ . $rowTotalPengeluaran[$sheet]);
                $profit[$sheet][$p]['pengeluaran'] = $cell;
                $char++;

            }
            $workSheet->setCellValue($char++ . $cursorRow, '=' . $sheet . '!' . $charTotalPengeluaran . $rowTotalPengeluaran[$sheet]);
            $cursorRow++;
        }

        // set footer table pemasukan
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->setCellValue($cell, '=SUM(' . $char . $startRowPengeluaran . ':' . $char . ($cursorRow - 1) . ')');
            $char++;
        }
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . $startRowPengeluaran . ':' . $char . ($cursorRow - 1) . ')');


        // set profit
        $cursorRow += 2;
        $workSheet->setCellValue('B' . $cursorRow++, 'PROFIT');
        $workSheet->setCellValue('B' . $cursorRow, 'Kontrakan');
        $workSheet->setCellValue('C' . $cursorRow, 'Qty');
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->setCellValue($cell, Carbon::parse($p)->format('M'));
            $char++;
        }
        $workSheet->setCellValue($char . $cursorRow, 'T O T A L');
        // set data profit
        $cursorRow++;
        $startRowProfit = $cursorRow;
        foreach ($profit as $key => $value) {
            $workSheet->setCellValue('B' . $cursorRow, $key);
            $char = 'D';
            $prevChar = '';
            foreach ($value as $pr) {
                $cell = $char . $cursorRow;
                $workSheet->setCellValue($cell, '=' . $pr['pemasukan'] . '-' . $pr['pengeluaran']);
                $prevChar = $char;
                $char++;
            }
            $workSheet->setCellValue($char . $cursorRow, "=SUM(D$cursorRow:$prevChar$cursorRow)");
            $cursorRow++;
        }
        ;
        //set footer table profit
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->setCellValue($cell, '=SUM(' . $char . $startRowProfit . ':' . $char . ($cursorRow - 1) . ')');
            $char++;
        }
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . $startRowProfit . ':' . $char . ($cursorRow - 1) . ')');
        $writer = new Xlsx($spreadsheet);
        $writer->save('ringkasan.xlsx');
        return response()->download('ringkasan.xlsx', 'ringkasan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

    }
}
