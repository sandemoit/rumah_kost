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
const HEADER = [
    'font' => [
        'bold' => true,
        'size' => 12,
        'color' => [
            'argb' => 'FFFFFF',
        ]
    ],
    'alignment' => [
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'argb' => 'FF003366',
        ]
    ],
];
const TABLE_HEADER = [
    'font' => [
        'bold' => true,
        'size' => 11,
        'color' => [
            'argb' => '000000',
        ]
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'argb' => 'FFCC99FF',
        ],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
];
const TABLE_BODY = [
    'font' => [
        'size' => 11,
        'color' => [
            'argb' => '000000',
        ]
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
];

const TABLE_FOOTER = [
    'font' => [
        'bold' => true,
        'size' => 11,
        'color' => [
            'argb' => '000000',
        ]
    ],
    'alignment' => [
        // 'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ],

    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'argb' => 'FFCCCCCC',
        ],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
];
class ExportRingkasanController extends Controller
{
    public function harian(Request $request)
    {
        $data = $this->getData($request->input('date'), $request->input('book'), 'harian');
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $profit = [];

        foreach ($data['kontrakan'] as $kontrakan) {
            $workSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $kontrakan);
            $spreadsheet->addSheet($workSheet);

            $rowTotalPemasukan[$kontrakan] = 3;
            $rowTotalPengeluaran[$kontrakan] = 6;
        }

        // loop every sheets
        foreach ($data['per_kontrakan']['pemasukan'] as $pemasukan) {
            $workSheet = $spreadsheet->getSheetByName($pemasukan['nama_kontrakan']);
            $workSheet->getColumnDimension('A')->setWidth(9);
            $workSheet->getColumnDimension('B')->setWidth(17);
            $workSheet->getColumnDimension('C')->setWidth(14);
            $workSheet->getColumnDimension('D')->setWidth(13);

            // set header table pemasukan
            $workSheet->setCellValue('A1', 'PEMASUKAN HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')))
                ->mergeCells('A1:D1')
                ->getStyle('A1')->applyFromArray(HEADER);
            // set row height
            $workSheet->getRowDimension(1)->setRowHeight(20);
            $workSheet->setCellValue('A2', 'Kamar');
            $workSheet->setCellValue('B2', 'Nama Penyewa');
            $workSheet->setCellValue('C2', 'Tgl Masuk');
            $workSheet->setCellValue('D2', 'Price');
            $workSheet->getStyle('A2:D2')->applyFromArray(TABLE_HEADER);

            $char = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . '2';
                $workSheet->setCellValue($cell, Carbon::parse($p)->format('d'));
                $workSheet->getStyle($cell)->applyFromArray(TABLE_HEADER);
                $char++;
            }
            $workSheet->setCellValue($char . '2', 'Total');
            $workSheet->getStyle($char . '2')->applyFromArray(TABLE_HEADER);
            // set row 2 hegiht
            $workSheet->getRowDimension(2)->setRowHeight(24);

            // set data table
            $cursorRow = 3;
            foreach ($pemasukan['kamar'] as $p) {
                $workSheet->getRowDimension($cursorRow)->setRowHeight(17);
                $workSheet->getStyle("A$cursorRow:D$cursorRow")->applyFromArray(TABLE_BODY);

                $workSheet->setCellValue('A' . $cursorRow, $p['detail_kamar']->nama_kamar);
                $workSheet->setCellValue('B' . $cursorRow, $p['detail_penyewa']->nama_penyewa);
                $workSheet->setCellValue('C' . $cursorRow, dateIndo($p['detail_penyewa']->tanggal_masuk));
                $workSheet->setCellValue('D' . $cursorRow, $p['detail_kamar']->harga_kamar);

                $char = 'E';
                $prevChr = '';
                foreach ($p['detail_transaksi'] as $d) {

                    $cell = $char . $cursorRow;
                    $workSheet->getStyle($cell)->applyFromArray(TABLE_BODY);
                    $workSheet->setCellValue($cell, $d->nominal ?? '-');
                    $prevChr = $char;
                    $char++;
                }
                $workSheet->setCellValue($char . $cursorRow, '=SUM(E' . $cursorRow . ':' . $prevChr . $cursorRow . ')');
                $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_HEADER);

                $cursorRow++;
            }
            $workSheet->setCellValue('A' . $cursorRow, 'Total')->mergeCells('A' . $cursorRow . ':D' . $cursorRow);
            $char = 'E';
            $workSheet->getStyle("A$cursorRow:D$cursorRow")->applyFromArray(TABLE_FOOTER);
            foreach ($data['dates'] as $d) {
                $cell = $char . $cursorRow;
                $workSheet->getStyle($cell)->applyFromArray(TABLE_FOOTER);
                $workSheet->setCellValue($cell, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');
                $char++;
            }
            $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');
            $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_FOOTER);

            $rowTotalPemasukan[$pemasukan['nama_kontrakan']] = $cursorRow;

            $cursorRow += 2;
            $cur[$pemasukan['nama_kontrakan']] = $cursorRow;
        }

        // prevent cursor out of bound
        foreach ($data['kontrakan'] as $kontrakan) {
            if (!isset($cur[$kontrakan])) {
                $cur[$kontrakan] = 6;
            }
        }

        foreach ($data['per_kontrakan']['pengeluaran'] as $pengeluaran) {
            $workSheet = $spreadsheet->getSheetByName($pengeluaran['nama_kontrakan']);

            // set header table pengeluaran
            $cur[$pengeluaran['nama_kontrakan']]++;
            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'PENGELUARAN HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')))->mergeCells('A' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);
            $workSheet->getStyle('A' . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(HEADER);
            $workSheet->getRowDimension($cur[$pengeluaran['nama_kontrakan']])->setRowHeight(20);
            $cur[$pengeluaran['nama_kontrakan']]++;

            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'Kamar');
            $workSheet->setCellValue('B' . $cur[$pengeluaran['nama_kontrakan']], 'Item Pengeluaran');
            $workSheet->mergeCells('B' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);
            $workSheet->getStyle('A' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_HEADER);
            $char = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                $workSheet->getStyle($cell)->applyFromArray(TABLE_HEADER);
                $workSheet->setCellValue($cell, Carbon::parse($p)->format('d'));
                $char++;
            }
            $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], 'Total');
            $workSheet->getStyle($char . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_HEADER);
            // set data table
            $cur[$pengeluaran['nama_kontrakan']]++;
            foreach ($pengeluaran['transaksi'] as $p) {

                $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], $p['kamar']);
                $workSheet->setCellValue('B' . $cur[$pengeluaran['nama_kontrakan']], $p['deskripsi']);
                $workSheet->mergeCells('B' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);
                $workSheet->getStyle("A" . $cur[$pengeluaran['nama_kontrakan']] . ":D" . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_BODY);
                $workSheet->getStyle("B" . $cur[$pengeluaran['nama_kontrakan']])->getAlignment()->setHorizontal('left');
                $char = 'E';
                $prevChr = '';
                foreach ($data['dates'] as $d) {
                    $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                    $workSheet->getStyle($cell)->applyFromArray(TABLE_BODY);
                    if ($p['tanggal_transaksi'] == $d) {
                        $workSheet->setCellValue($cell, $p['nominal']);
                    } else {
                        $workSheet->setCellValue($cell, '-');
                    }
                    $prevChr = $char;
                    $char++;
                }
                $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], '=SUM(E' . $cur[$pengeluaran['nama_kontrakan']] . ':' . $prevChr . $cur[$pengeluaran['nama_kontrakan']] . ')');
                $workSheet->getStyle($char . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_HEADER);
                $cur[$pengeluaran['nama_kontrakan']]++;
            }
            $char = 'E';
            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'Total');
            $workSheet->mergeCells('A' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);
            $workSheet->getStyle('A' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_FOOTER);
            foreach ($data['dates'] as $d) {
                $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                $workSheet->getStyle($cell)->applyFromArray(TABLE_FOOTER);
                $workSheet->setCellValue($cell, '=SUM(' . $char . ($cur[$pengeluaran['nama_kontrakan']] - count($pengeluaran['transaksi'])) . ':' . $char . ($cur[$pengeluaran['nama_kontrakan']] - 1) . ')');
                $char++;
            }
            $rowTotalPengeluaran[$pengeluaran['nama_kontrakan']] = $cur[$pengeluaran['nama_kontrakan']];
            $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], '=SUM(' . $char . ($cur[$pengeluaran['nama_kontrakan']] - count($pengeluaran['transaksi'])) . ':' . $char . ($cur[$pengeluaran['nama_kontrakan']] - 1) . ')');
            $workSheet->getStyle($char . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_FOOTER);
        }

        $sheetNames = $spreadsheet->getSheetNames();
        // sum sheet
        $workSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'sum');
        $spreadsheet->addSheet($workSheet, 0);
        $workSheet->getRowDimension(1)->setRowHeight(20);
        $workSheet->getColumnDimension('A')->setWidth(1);
        // set table pemasukan
        $workSheet->setCellValue('B1', 'PEMASUKAN HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')));
        $workSheet->mergeCells('B1:F1');
        $workSheet->getStyle('B1')->applyFromArray(HEADER);
        $workSheet->setCellValue('B2', 'Kontrakan');
        $workSheet->setCellValue('C2', 'Qty');
        $workSheet->getStyle('B2:C2')->applyFromArray(TABLE_HEADER);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . 2;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_HEADER);
            $workSheet->setCellValue($cell, Carbon::parse($p)->format('d'));
            $char++;
        }
        $workSheet->setCellValue($char . 2, 'T O T A L');
        $workSheet->getStyle($char . 2)->applyFromArray(TABLE_HEADER);
        $cursorRow = 3;
        foreach ($sheetNames as $sheet) {
            $workSheet->setCellValue('B' . $cursorRow, $sheet);
            $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_BODY);
            $char = 'D';
            $charTotalPemasukan = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . $cursorRow;
                $workSheet->getStyle($cell)->applyFromArray(TABLE_BODY);
                $workSheet->setCellValue($cell, "=IF(" . $sheet . '!' . $charTotalPemasukan . ($rowTotalPemasukan[$sheet]) . ">500," . $sheet . '!' . $charTotalPemasukan . ($rowTotalPemasukan[$sheet]) . ",0)");
                $profit[$sheet][$p]['pemasukan'] = $cell;
                $charTotalPemasukan++;
                $char++;
            }
            $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_HEADER);
            $workSheet->setCellValue($char++ . $cursorRow, '=' . $sheet . '!' . $charTotalPemasukan . $rowTotalPemasukan[$sheet]);
            $cursorRow++;
        }

        // set footer table pemasukan
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L')
            ->mergeCells('B' . $cursorRow . ':C' . $cursorRow);
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_FOOTER);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_FOOTER);
            $workSheet->setCellValue($cell, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_FOOTER);
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');




        // set table pengeluaran
        $cursorRow += 2;
        $workSheet->setCellValue('B' . $cursorRow, 'PENGELUARAN HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')));
        $workSheet->mergeCells('B' . $cursorRow . ':F' . $cursorRow);
        $workSheet->getStyle('B' . $cursorRow . ':F' . $cursorRow)->applyFromArray(HEADER);
        $cursorRow++;
        $workSheet->setCellValue('B' . $cursorRow, 'Kontrakan');
        $workSheet->setCellValue('C' . $cursorRow, 'Qty');
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_HEADER);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_HEADER);
            $workSheet->setCellValue($cell, Carbon::parse($p)->format('d'));
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_HEADER);
        $workSheet->setCellValue($char . $cursorRow, 'T O T A L');
        $cursorRow++;
        // set data pengeluaran
        $startRowPengeluaran = $cursorRow;
        foreach ($sheetNames as $sheet) {
            $workSheet->setCellValue('B' . $cursorRow, $sheet);
            $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_BODY);
            $char = 'D';
            $charTotalPengeluaran = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . $cursorRow;
                $workSheet->getStyle($cell)->applyFromArray(TABLE_BODY);
                $workSheet->setCellValue($cell, '=' . $sheet . '!' . $charTotalPengeluaran++ . $rowTotalPengeluaran[$sheet]);
                $profit[$sheet][$p]['pengeluaran'] = $cell;
                $char++;

            }
            $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_HEADER);
            $workSheet->setCellValue($char++ . $cursorRow, '=' . $sheet . '!' . $charTotalPengeluaran . $rowTotalPengeluaran[$sheet]);
            $cursorRow++;
        }

        // set footer table pemasukan
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $workSheet->mergeCells('B' . $cursorRow . ':C' . $cursorRow);
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_FOOTER);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_FOOTER);
            $workSheet->setCellValue($cell, '=SUM(' . $char . $startRowPengeluaran . ':' . $char . ($cursorRow - 1) . ')');
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_FOOTER);
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . $startRowPengeluaran . ':' . $char . ($cursorRow - 1) . ')');


        // set profit
        $cursorRow += 2;
        $workSheet->setCellValue('B' . $cursorRow, 'PROFIT HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')));
        $workSheet->mergeCells('B' . $cursorRow . ':F' . $cursorRow);
        $workSheet->getStyle('B' . $cursorRow . ':F' . $cursorRow)->applyFromArray(HEADER);
        $cursorRow++;

        $workSheet->setCellValue('B' . $cursorRow, 'Kontrakan');
        $workSheet->setCellValue('C' . $cursorRow, 'Qty');
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_HEADER);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_HEADER);
            $workSheet->setCellValue($cell, Carbon::parse($p)->format('d'));
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_HEADER);
        $workSheet->setCellValue($char . $cursorRow, 'T O T A L');
        // set data profit
        $cursorRow++;
        $startRowProfit = $cursorRow;
        foreach ($profit as $key => $value) {
            $workSheet->setCellValue('B' . $cursorRow, $key);
            $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_BODY);
            $char = 'D';
            $prevChar = '';
            foreach ($value as $pr) {
                $cell = $char . $cursorRow;
                $workSheet->getStyle($cell)->applyFromArray(TABLE_BODY);
                $workSheet->setCellValue($cell, '=' . $pr['pemasukan'] . '-' . $pr['pengeluaran']);
                $prevChar = $char;
                $char++;
            }
            $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_HEADER);
            $workSheet->setCellValue($char . $cursorRow, "=SUM(D$cursorRow:$prevChar$cursorRow)");
            $cursorRow++;
        }
        ;
        //set footer table profit
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $workSheet->mergeCells('B' . $cursorRow . ':C' . $cursorRow);
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_FOOTER);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_FOOTER);
            $workSheet->setCellValue($cell, '=SUM(' . $char . $startRowProfit . ':' . $char . ($cursorRow - 1) . ')');
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_FOOTER);
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . $startRowProfit . ':' . $char . ($cursorRow - 1) . ')');


        $writer = new Xlsx($spreadsheet);
        $writer->save('ringkasan.xlsx');
        return response()->download('ringkasan.xlsx', 'ringkasan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

    }

    public function bulanan(Request $request)
    {
        $data = $this->getData($request->input('date'), $request->input('book'), 'bulanan');
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $profit = [];

        foreach ($data['kontrakan'] as $kontrakan) {
            $workSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, $kontrakan);
            $spreadsheet->addSheet($workSheet);

            $rowTotalPemasukan[$kontrakan] = 3;
            $rowTotalPengeluaran[$kontrakan] = 6;
        }

        // loop every sheets
        foreach ($data['per_kontrakan']['pemasukan'] as $pemasukan) {
            $workSheet = $spreadsheet->getSheetByName($pemasukan['nama_kontrakan']);
            $workSheet->getColumnDimension('A')->setWidth(9);
            $workSheet->getColumnDimension('B')->setWidth(17);
            $workSheet->getColumnDimension('C')->setWidth(14);
            $workSheet->getColumnDimension('D')->setWidth(13);

            // set header table pemasukan
            $workSheet->setCellValue('A1', 'PEMASUKAN HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')))
                ->mergeCells('A1:D1')
                ->getStyle('A1')->applyFromArray(HEADER);
            // set row height
            $workSheet->getRowDimension(1)->setRowHeight(20);
            $workSheet->setCellValue('A2', 'Kamar');
            $workSheet->setCellValue('B2', 'Nama Penyewa');
            $workSheet->setCellValue('C2', 'Tgl Masuk');
            $workSheet->setCellValue('D2', 'Price');
            $workSheet->getStyle('A2:D2')->applyFromArray(TABLE_HEADER);

            $char = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . '2';
                $workSheet->setCellValue($cell, bulan(Carbon::parse($p)->month));
                $workSheet->getStyle($cell)->applyFromArray(TABLE_HEADER);
                $char++;
            }
            $workSheet->setCellValue($char . '2', 'Total');
            $workSheet->getStyle($char . '2')->applyFromArray(TABLE_HEADER);
            // set row 2 hegiht
            $workSheet->getRowDimension(2)->setRowHeight(24);

            // set data table
            $cursorRow = 3;
            foreach ($pemasukan['kamar'] as $p) {
                $workSheet->getRowDimension($cursorRow)->setRowHeight(17);
                $workSheet->getStyle("A$cursorRow:D$cursorRow")->applyFromArray(TABLE_BODY);

                $workSheet->setCellValue('A' . $cursorRow, $p['detail_kamar']->nama_kamar);
                $workSheet->setCellValue('B' . $cursorRow, $p['detail_penyewa']->nama_penyewa);
                $workSheet->setCellValue('C' . $cursorRow, dateIndo($p['detail_penyewa']->tanggal_masuk));
                $workSheet->setCellValue('D' . $cursorRow, $p['detail_kamar']->harga_kamar);

                $char = 'E';
                $prevChr = '';
                foreach ($p['detail_transaksi'] as $d) {

                    $cell = $char . $cursorRow;
                    $workSheet->getStyle($cell)->applyFromArray(TABLE_BODY);
                    $workSheet->setCellValue($cell, $d->nominal ?? '-');
                    $prevChr = $char;
                    $char++;
                }
                $workSheet->setCellValue($char . $cursorRow, '=SUM(E' . $cursorRow . ':' . $prevChr . $cursorRow . ')');
                $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_HEADER);

                $cursorRow++;
            }
            $workSheet->setCellValue('A' . $cursorRow, 'Total')->mergeCells('A' . $cursorRow . ':D' . $cursorRow);
            $char = 'E';
            $workSheet->getStyle("A$cursorRow:D$cursorRow")->applyFromArray(TABLE_FOOTER);
            foreach ($data['dates'] as $d) {
                $cell = $char . $cursorRow;
                $workSheet->getStyle($cell)->applyFromArray(TABLE_FOOTER);
                $workSheet->setCellValue($cell, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');
                $char++;
            }
            $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');
            $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_FOOTER);

            $rowTotalPemasukan[$pemasukan['nama_kontrakan']] = $cursorRow;

            $cursorRow += 2;
            $cur[$pemasukan['nama_kontrakan']] = $cursorRow;
        }

        // prevent cursor out of bound
        foreach ($data['kontrakan'] as $kontrakan) {
            if (!isset($cur[$kontrakan])) {
                $cur[$kontrakan] = 6;
            }
        }

        foreach ($data['per_kontrakan']['pengeluaran'] as $pengeluaran) {
            $workSheet = $spreadsheet->getSheetByName($pengeluaran['nama_kontrakan']);

            // set header table pengeluaran
            $cur[$pengeluaran['nama_kontrakan']]++;
            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'PENGELUARAN HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')))->mergeCells('A' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);
            $workSheet->getStyle('A' . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(HEADER);
            $workSheet->getRowDimension($cur[$pengeluaran['nama_kontrakan']])->setRowHeight(20);
            $cur[$pengeluaran['nama_kontrakan']]++;

            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'Kamar');
            $workSheet->setCellValue('B' . $cur[$pengeluaran['nama_kontrakan']], 'Item Pengeluaran');
            $workSheet->mergeCells('B' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);
            $workSheet->getStyle('A' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_HEADER);
            $char = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                $workSheet->getStyle($cell)->applyFromArray(TABLE_HEADER);
                $workSheet->setCellValue($cell, bulan(Carbon::parse($p)->month));
                $char++;
            }
            $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], 'Total');
            $workSheet->getStyle($char . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_HEADER);
            // set data table
            $cur[$pengeluaran['nama_kontrakan']]++;
            foreach ($pengeluaran['transaksi'] as $p) {

                $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], $p['kamar']);
                $workSheet->setCellValue('B' . $cur[$pengeluaran['nama_kontrakan']], $p['deskripsi']);
                $workSheet->mergeCells('B' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);
                $workSheet->getStyle("A" . $cur[$pengeluaran['nama_kontrakan']] . ":D" . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_BODY);
                $workSheet->getStyle("B" . $cur[$pengeluaran['nama_kontrakan']])->getAlignment()->setHorizontal('left');
                $char = 'E';
                $prevChr = '';
                foreach ($data['dates'] as $d) {
                    $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                    $workSheet->getStyle($cell)->applyFromArray(TABLE_BODY);
                    if ($p['tanggal_transaksi'] == $d) {
                        $workSheet->setCellValue($cell, $p['nominal']);
                    } else {
                        $workSheet->setCellValue($cell, '-');
                    }
                    $prevChr = $char;
                    $char++;
                }
                $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], '=SUM(E' . $cur[$pengeluaran['nama_kontrakan']] . ':' . $prevChr . $cur[$pengeluaran['nama_kontrakan']] . ')');
                $workSheet->getStyle($char . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_HEADER);
                $cur[$pengeluaran['nama_kontrakan']]++;
            }
            $char = 'E';
            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'Total');
            $workSheet->mergeCells('A' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);
            $workSheet->getStyle('A' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_FOOTER);
            foreach ($data['dates'] as $d) {
                $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                $workSheet->getStyle($cell)->applyFromArray(TABLE_FOOTER);
                $workSheet->setCellValue($cell, '=SUM(' . $char . ($cur[$pengeluaran['nama_kontrakan']] - count($pengeluaran['transaksi'])) . ':' . $char . ($cur[$pengeluaran['nama_kontrakan']] - 1) . ')');
                $char++;
            }
            $rowTotalPengeluaran[$pengeluaran['nama_kontrakan']] = $cur[$pengeluaran['nama_kontrakan']];
            $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], '=SUM(' . $char . ($cur[$pengeluaran['nama_kontrakan']] - count($pengeluaran['transaksi'])) . ':' . $char . ($cur[$pengeluaran['nama_kontrakan']] - 1) . ')');
            $workSheet->getStyle($char . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_FOOTER);
        }

        $sheetNames = $spreadsheet->getSheetNames();
        // sum sheet
        $workSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'sum');
        $spreadsheet->addSheet($workSheet, 0);
        $workSheet->getRowDimension(1)->setRowHeight(20);
        $workSheet->getColumnDimension('A')->setWidth(1);
        // set table pemasukan
        $workSheet->setCellValue('B1', 'PEMASUKAN HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')));
        $workSheet->mergeCells('B1:F1');
        $workSheet->getStyle('B1')->applyFromArray(HEADER);
        $workSheet->setCellValue('B2', 'Kontrakan');
        $workSheet->setCellValue('C2', 'Qty');
        $workSheet->getStyle('B2:C2')->applyFromArray(TABLE_HEADER);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . 2;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_HEADER);
            $workSheet->setCellValue($cell, bulan(Carbon::parse($p)->month));
            $char++;
        }
        $workSheet->setCellValue($char . 2, 'T O T A L');
        $workSheet->getStyle($char . 2)->applyFromArray(TABLE_HEADER);
        $cursorRow = 3;
        foreach ($sheetNames as $sheet) {
            $workSheet->setCellValue('B' . $cursorRow, $sheet);
            $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_BODY);
            $char = 'D';
            $charTotalPemasukan = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . $cursorRow;
                $workSheet->getStyle($cell)->applyFromArray(TABLE_BODY);
                $workSheet->setCellValue($cell, "=IF(" . $sheet . '!' . $charTotalPemasukan . ($rowTotalPemasukan[$sheet]) . ">500," . $sheet . '!' . $charTotalPemasukan . ($rowTotalPemasukan[$sheet]) . ",0)");
                $profit[$sheet][$p]['pemasukan'] = $cell;
                $charTotalPemasukan++;
                $char++;
            }
            $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_HEADER);
            $workSheet->setCellValue($char++ . $cursorRow, '=' . $sheet . '!' . $charTotalPemasukan . $rowTotalPemasukan[$sheet]);
            $cursorRow++;
        }

        // set footer table pemasukan
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L')
            ->mergeCells('B' . $cursorRow . ':C' . $cursorRow);
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_FOOTER);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_FOOTER);
            $workSheet->setCellValue($cell, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_FOOTER);
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');




        // set table pengeluaran
        $cursorRow += 2;
        $workSheet->setCellValue('B' . $cursorRow, 'PENGELUARAN HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')));
        $workSheet->mergeCells('B' . $cursorRow . ':F' . $cursorRow);
        $workSheet->getStyle('B' . $cursorRow . ':F' . $cursorRow)->applyFromArray(HEADER);
        $cursorRow++;
        $workSheet->setCellValue('B' . $cursorRow, 'Kontrakan');
        $workSheet->setCellValue('C' . $cursorRow, 'Qty');
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_HEADER);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_HEADER);
            $workSheet->setCellValue($cell, bulan(Carbon::parse($p)->month));
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_HEADER);
        $workSheet->setCellValue($char . $cursorRow, 'T O T A L');
        $cursorRow++;
        // set data pengeluaran
        $startRowPengeluaran = $cursorRow;
        foreach ($sheetNames as $sheet) {
            $workSheet->setCellValue('B' . $cursorRow, $sheet);
            $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_BODY);
            $char = 'D';
            $charTotalPengeluaran = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . $cursorRow;
                $workSheet->getStyle($cell)->applyFromArray(TABLE_BODY);
                $workSheet->setCellValue($cell, '=' . $sheet . '!' . $charTotalPengeluaran++ . $rowTotalPengeluaran[$sheet]);
                $profit[$sheet][$p]['pengeluaran'] = $cell;
                $char++;

            }
            $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_HEADER);
            $workSheet->setCellValue($char++ . $cursorRow, '=' . $sheet . '!' . $charTotalPengeluaran . $rowTotalPengeluaran[$sheet]);
            $cursorRow++;
        }

        // set footer table pemasukan
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $workSheet->mergeCells('B' . $cursorRow . ':C' . $cursorRow);
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_FOOTER);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_FOOTER);
            $workSheet->setCellValue($cell, '=SUM(' . $char . $startRowPengeluaran . ':' . $char . ($cursorRow - 1) . ')');
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_FOOTER);
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . $startRowPengeluaran . ':' . $char . ($cursorRow - 1) . ')');


        // set profit
        $cursorRow += 2;
        $workSheet->setCellValue('B' . $cursorRow, 'PROFIT HARIAN BULAN ' . strtoupper(Carbon::parse($data['dates'][0])->format('F Y')));
        $workSheet->mergeCells('B' . $cursorRow . ':F' . $cursorRow);
        $workSheet->getStyle('B' . $cursorRow . ':F' . $cursorRow)->applyFromArray(HEADER);
        $cursorRow++;

        $workSheet->setCellValue('B' . $cursorRow, 'Kontrakan');
        $workSheet->setCellValue('C' . $cursorRow, 'Qty');
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_HEADER);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_HEADER);
            $workSheet->setCellValue($cell, bulan(Carbon::parse($p)->month));
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_HEADER);
        $workSheet->setCellValue($char . $cursorRow, 'T O T A L');
        // set data profit
        $cursorRow++;
        $startRowProfit = $cursorRow;
        foreach ($profit as $key => $value) {
            $workSheet->setCellValue('B' . $cursorRow, $key);
            $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_BODY);
            $char = 'D';
            $prevChar = '';
            foreach ($value as $pr) {
                $cell = $char . $cursorRow;
                $workSheet->getStyle($cell)->applyFromArray(TABLE_BODY);
                $workSheet->setCellValue($cell, '=' . $pr['pemasukan'] . '-' . $pr['pengeluaran']);
                $prevChar = $char;
                $char++;
            }
            $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_HEADER);
            $workSheet->setCellValue($char . $cursorRow, "=SUM(D$cursorRow:$prevChar$cursorRow)");
            $cursorRow++;
        }
        ;
        //set footer table profit
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $workSheet->mergeCells('B' . $cursorRow . ':C' . $cursorRow);
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_FOOTER);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_FOOTER);
            $workSheet->setCellValue($cell, '=SUM(' . $char . $startRowProfit . ':' . $char . ($cursorRow - 1) . ')');
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_FOOTER);
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . $startRowProfit . ':' . $char . ($cursorRow - 1) . ')');


        $writer = new Xlsx($spreadsheet);
        $writer->save('ringkasan.xlsx');
        return response()->download('ringkasan.xlsx', 'ringkasan.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);

    }




    public function getData($date, $code_kontrakan, $type = 'harian')
    {


        if ($type == 'harian') {
            $data['dates'] = [];
            for ($i = 0; $i < Carbon::parse($date)->daysInMonth; $i++) {
                $data['dates'][] = Carbon::parse($date)->addDays($i)->format('Y-m-d');
            }
        } else {
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
        if ($code_kontrakan == 'all') {
            $data['kontrakan'] = Kontrakan::all()->pluck('nama_kontrakan');
        } else {
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

        $data['per_kontrakan']['pemasukan'] = Kontrakan::all()
            ->map(function ($item) use ($data, $dateParam, $transaksi) {
                return [
                    //nama kontrakan
                    'nama_kontrakan' => $item->nama_kontrakan,
                    // daftar kamar
                    'kamar' => Penyewa::all()
                        ->where('id_kontrakan', $item->id)
                        ->map(function ($item) use ($data, $dateParam) {
                            $trx = [];
                            foreach ($data['dates'] as $d) {
                                $trx[$d] = TransaksiList::where('id_penyewa', $item->id)
                                    ->with('transaksiMasuk', 'penyewa')
                                    ->where('id_kamar', 'like', "%[\"" . $item->kamar->id. "\"]%")
                                    ->whereHas('transaksiMasuk', function ($query) use ($item, $d) {
                                        return $query
                                        ->where('periode_sewa', 'LIKE', $d . "%");
                                    })->first() ?? null;
                            }
                            return [
                                'detail_kamar' => $item->kamar,
                                'detail_penyewa' => $item,
                                'detail_transaksi' => $trx,// list transaksi berdasarkan tanggal terpilih
                            ];
                        })->sortBy(function ($item) {
                            return $item['detail_kamar']->nama_kamar;
                        }),
                    'transaksi_kamar' => [
                        [
                            'kamar' => Kamar::all(), //detail kamar
                            'penyewa' => [
                                'detail_penyewa' => null,
                                'detail_transaksi' => null,
                            ],
                        ]
                    ]
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
}
