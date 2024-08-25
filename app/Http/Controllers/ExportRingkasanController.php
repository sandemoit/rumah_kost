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
use PhpOffice\PhpSpreadsheet\Style\Alignment;

const HEADER = [
    'font' => [
        'bold' => true,
        'size' => 9,
        'name' => 'Arial',
        'color' => [
            'argb' => 'FFFFFF',
        ]
    ],
    'alignment' => [
        'vertical' => Alignment::VERTICAL_CENTER,
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'argb' => 'FF003366',
        ]
    ],
];
const HEADER_SUM = [
    'font' => [
        'bold' => true,
        'size' => 9,
        'name' => 'Arial',
        'color' => [
            'argb' => 'FFFFFF',
        ]
    ],
    'alignment' => [
        'vertical' => Alignment::VERTICAL_CENTER,
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'argb' => '00B0F0',
        ]
    ],
];
const KAMAR_KOSONG = [
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'argb' => 'FFFF0000',
        ]
    ],
];
const KAMAR_NUNGGAK = [
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'argb' => 'FFFFFF00',
        ]
    ],
];
const TABLE_HEADER = [
    'font' => [
        'bold' => true,
        'size' => 10,
        'color' => [
            'argb' => '000000',
        ]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
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
const TABLE_HEADER_PENGELUARAN = [
    'font' => [
        'bold' => true,
        'size' => 10,
        'color' => [
            'argb' => '000000',
        ]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'argb' => 'FFd428',
        ],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
];
const TABLE_SUM_PEMASUKAN = [
    'font' => [
        'bold' => true,
        'size' => 10,
        'color' => [
            'argb' => 'FFFFFF',
        ]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'argb' => '604a7b',
        ],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
];
const TABLE_SUM_PENGELUARAN = [
    'font' => [
        'bold' => true,
        'size' => 10,
        'color' => [
            'argb' => 'FFFFFF',
        ]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'argb' => '808080',
        ],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['argb' => 'FF000000'],
        ],
    ],
];
const TABLE_SUM_PROFIT = [
    'font' => [
        'bold' => true,
        'size' => 10,
        'color' => [
            'argb' => 'FFFFFF',
        ]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'argb' => '10243e',
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
        'size' => 8,
        'name' => 'Arial',
        'color' => [
            'argb' => '000000',
        ]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
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
        'size' => 8,
        'name' => 'Arial',
        'color' => [
            'argb' => '000000',
        ]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
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
            $rangeValueCell['pemasukan'][$kontrakan] = null;
            $rangeValueCell['pengeluaran'][$kontrakan] = null;
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
            $rangeValueCell['pemasukan'][$pemasukan['nama_kontrakan']] = $pemasukan['nama_kontrakan'] . "!E3:";
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
            $rangeValueCell['pemasukan'][$pemasukan['nama_kontrakan']] .= $cell;
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
        // pengeluaran
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
            $rangeValueCell['pengeluaran'][$pengeluaran['nama_kontrakan']] = $pengeluaran['nama_kontrakan'] . '!E' . $cur[$pengeluaran['nama_kontrakan']] . ':';
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
            $rangeValueCell['pengeluaran'][$pengeluaran['nama_kontrakan']] .= $cell;
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
        $workSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Sum');
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
            $workSheet->setCellValue('C' . $cursorRow, Kamar::whereHas('kontrakan', function ($query) use ($sheet) {
                return $query->where('nama_kontrakan', $sheet);
            })->count());
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
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $workSheet->setCellValue('C' . $cursorRow, '=SUM(C3:C' . ($cursorRow - 1) . ')');
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
            $workSheet->setCellValue('C' . $cursorRow, Kamar::whereHas('kontrakan', function ($query) use ($sheet) {
                return $query->where('nama_kontrakan', $sheet);
            })->count());
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

        // set footer table pengeluaran
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $workSheet->setCellValue('C' . $cursorRow, '=SUM(C' . $startRowPengeluaran . ':C' . ($cursorRow - 1) . ')');
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
            $workSheet->setCellValue('C' . $cursorRow, Kamar::whereHas('kontrakan', function ($query) use ($key) {
                return $query->where('nama_kontrakan', $key);
            })->count());
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
        };
        //set footer table profit
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $workSheet->setCellValue('C' . $cursorRow, '=SUM(C' . $startRowProfit . ':C' . ($cursorRow - 1) . ')');
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
        $writer->save('Laporan KAS.xlsx');
        return response()->download('Laporan KAS.xlsx', 'Laporan KAS.xlsx', [
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
            $workSheet->getColumnDimension('A')->setWidth(13);
            $workSheet->getColumnDimension('B')->setWidth(17);
            $workSheet->getColumnDimension('C')->setWidth(14);
            $workSheet->getColumnDimension('D')->setWidth(13);

            // set row height
            $workSheet->getRowDimension(1)->setRowHeight(26.5);
            // set header table pemasukan
            $workSheet->setCellValue('A1', 'PEMASUKAN')->getStyle('A1')->applyFromArray(HEADER);
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
            $workSheet->getRowDimension(2)->setRowHeight(22);

            // set data table
            $cursorRow = 3;
            foreach ($pemasukan['kamar'] as $p) {
                $workSheet->getRowDimension($cursorRow)->setRowHeight(22);
                $workSheet->getStyle("A$cursorRow:D$cursorRow")->applyFromArray(TABLE_BODY);

                $workSheet->getStyle("B$cursorRow")->applyFromArray(['alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]]);
                $workSheet->setCellValue('A' . $cursorRow, $p['detail_kamar']->nama_kamar);
                $workSheet->setCellValue('B' . $cursorRow, $p['detail_penyewa']->nama_penyewa);
                $workSheet->setCellValue('C' . $cursorRow, Carbon::parse($p['detail_penyewa']->tanggal_masuk)->format('j-M'));
                $workSheet->setCellValue('D' . $cursorRow, $p['detail_kamar']->harga_kamar);
                $workSheet->getStyle('D' . $cursorRow)->getNumberFormat()->setFormatCode('#,##0');

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

            $workSheet->setCellValue('A' . $cursorRow, 'TOTAL')->mergeCells('A' . $cursorRow . ':D' . $cursorRow);

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

        // pengeluaran
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
            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'PENGELUARAN')
                ->getStyle('A' . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(HEADER);
            $workSheet->getRowDimension($cur[$pengeluaran['nama_kontrakan']])->setRowHeight(26.5);
            $cur[$pengeluaran['nama_kontrakan']]++;

            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'Kamar');
            $workSheet->setCellValue('B' . $cur[$pengeluaran['nama_kontrakan']], 'Item Pengeluaran');
            $workSheet->mergeCells('B' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);
            $workSheet->getStyle('A' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_HEADER_PENGELUARAN);
            $workSheet->getRowDimension($cur[$pengeluaran['nama_kontrakan']])->setRowHeight(22);

            $char = 'E';
            foreach ($data['dates'] as $p) {
                $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                $workSheet->getStyle($cell)->applyFromArray(TABLE_HEADER_PENGELUARAN);
                $workSheet->setCellValue($cell, bulan(Carbon::parse($p)->month));
                $char++;
            }
            $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], 'Total');
            $workSheet->getStyle($char . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_HEADER_PENGELUARAN);
            // set data table
            $cur[$pengeluaran['nama_kontrakan']]++;
            foreach ($pengeluaran['transaksi'] as $p) {
                $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], $p['kamar']);
                $workSheet->setCellValue('B' . $cur[$pengeluaran['nama_kontrakan']], $p['deskripsi']);
                $workSheet->mergeCells('B' . $cur[$pengeluaran['nama_kontrakan']] . ':D' . $cur[$pengeluaran['nama_kontrakan']]);
                $workSheet->getStyle("A" . $cur[$pengeluaran['nama_kontrakan']] . ":D" . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_BODY);
                $workSheet->getStyle("B" . $cur[$pengeluaran['nama_kontrakan']])->getAlignment()->setHorizontal('left');
                $workSheet->getRowDimension($cur[$pengeluaran['nama_kontrakan']])->setRowHeight(22);
                $char = 'E';
                $prevChr = '';
                foreach ($data['dates'] as $d) {
                    $cell = $char . $cur[$pengeluaran['nama_kontrakan']];
                    $workSheet->getStyle($cell)->applyFromArray(TABLE_BODY);
                    if (str_starts_with($p['tanggal_transaksi'], $d)) {
                        $workSheet->setCellValue($cell, $p['nominal']);
                    } else {
                        $workSheet->setCellValue($cell, '-');
                    }
                    $prevChr = $char;
                    $char++;
                }

                $workSheet->setCellValue($char . $cur[$pengeluaran['nama_kontrakan']], '=SUM(E' . $cur[$pengeluaran['nama_kontrakan']] . ':' . $prevChr . $cur[$pengeluaran['nama_kontrakan']] . ')');
                $workSheet->getStyle($char . $cur[$pengeluaran['nama_kontrakan']])->applyFromArray(TABLE_HEADER_PENGELUARAN);
                $cur[$pengeluaran['nama_kontrakan']]++;
            }
            $char = 'E';
            $workSheet->setCellValue('A' . $cur[$pengeluaran['nama_kontrakan']], 'TOTAL');
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
        $workSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Sum');
        $spreadsheet->addSheet($workSheet, 0);
        $workSheet->getColumnDimension('A')->setWidth(1);
        $workSheet->getColumnDimension('B')->setWidth(26);
        $workSheet->getColumnDimension('C')->setWidth(7);
        // set table pemasukan
        $workSheet->setCellValue('B1', 'PEMASUKAN');
        $workSheet->getStyle('B1')->applyFromArray(HEADER_SUM);
        $workSheet->setCellValue('B2', 'Kontrakan');
        $workSheet->setCellValue('C2', 'Qty');
        $workSheet->getStyle('B2:C2')->applyFromArray(TABLE_SUM_PEMASUKAN);
        // set row hight
        $workSheet->getRowDimension(1)->setRowHeight(29.5);
        $workSheet->getRowDimension(2)->setRowHeight(22);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . 2;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_SUM_PEMASUKAN);
            $workSheet->setCellValue($cell, bulan(Carbon::parse($p)->month));
            $workSheet->getColumnDimension($char)->setWidth(11);
            $char++;
        }
        $workSheet->setCellValue($char . 2, 'T O T A L');
        $workSheet->getStyle($char . 2)->applyFromArray(TABLE_SUM_PEMASUKAN);
        $workSheet->getColumnDimension($char)->setWidth(11);

        $cursorRow = 3;
        foreach ($sheetNames as $sheet) {
            $workSheet->getRowDimension($cursorRow)->setRowHeight(20);
            $workSheet->setCellValue('B' . $cursorRow, $sheet);
            $workSheet->setCellValue('C' . $cursorRow, Kamar::whereHas('kontrakan', function ($query) use ($sheet) {
                return $query->where('nama_kontrakan', $sheet);
            })->count());
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
            $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_SUM_PEMASUKAN);
            $workSheet->setCellValue($char++ . $cursorRow, '=' . $sheet . '!' . $charTotalPemasukan . $rowTotalPemasukan[$sheet]);
            $cursorRow++;
        }

        // set footer table pemasukan
        $workSheet->getRowDimension($cursorRow)->setRowHeight(22);
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $workSheet->setCellValue('C' . $cursorRow, '=SUM(C3:C' . ($cursorRow - 1) . ')');
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_SUM_PEMASUKAN);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_SUM_PEMASUKAN);
            $workSheet->setCellValue($cell, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_SUM_PEMASUKAN);
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . '3:' . $char . ($cursorRow - 1) . ')');

        // set table pengeluaran
        $cursorRow += 2;
        $workSheet->setCellValue('B' . $cursorRow, 'PENGELUARAN');
        $workSheet->getStyle('B' . $cursorRow)->applyFromArray(HEADER_SUM);
        $workSheet->getRowDimension($cursorRow)->setRowHeight(29.5);

        $cursorRow++;
        $workSheet->setCellValue('B' . $cursorRow, 'Kontrakan');
        $workSheet->setCellValue('C' . $cursorRow, 'Qty');
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_SUM_PENGELUARAN);
        // set row hight
        $workSheet->getRowDimension($cursorRow)->setRowHeight(22);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_SUM_PENGELUARAN);
            $workSheet->setCellValue($cell, bulan(Carbon::parse($p)->month));
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_SUM_PENGELUARAN);
        $workSheet->setCellValue($char . $cursorRow, 'T O T A L');
        $cursorRow++;
        // set data pengeluaran
        $startRowPengeluaran = $cursorRow;
        foreach ($sheetNames as $sheet) {
            $workSheet->getRowDimension($cursorRow, $sheet)->setRowHeight(20);
            $workSheet->setCellValue('B' . $cursorRow, $sheet);
            $workSheet->setCellValue('C' . $cursorRow, Kamar::whereHas('kontrakan', function ($query) use ($sheet) {
                return $query->where('nama_kontrakan', $sheet);
            })->count());
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
            $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_SUM_PENGELUARAN);
            $workSheet->setCellValue($char++ . $cursorRow, '=' . $sheet . '!' . $charTotalPengeluaran . $rowTotalPengeluaran[$sheet]);
            $cursorRow++;
        }

        // set footer table pengeluaran
        $workSheet->getRowDimension($cursorRow)->setRowHeight(22);
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $workSheet->setCellValue('C' . $cursorRow, '=SUM(C' . $startRowPengeluaran . ':C' . ($cursorRow - 1) . ')');
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_SUM_PENGELUARAN);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_SUM_PENGELUARAN);
            $workSheet->setCellValue($cell, '=SUM(' . $char . $startRowPengeluaran . ':' . $char . ($cursorRow - 1) . ')');
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_SUM_PENGELUARAN);
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . $startRowPengeluaran . ':' . $char . ($cursorRow - 1) . ')');

        // set profit
        $cursorRow += 2;
        $workSheet->setCellValue('B' . $cursorRow, 'PROFIT');
        $workSheet->getStyle('B' . $cursorRow)->applyFromArray(HEADER_SUM);
        $workSheet->getRowDimension($cursorRow)->setRowHeight(29.5);
        $cursorRow++;

        $workSheet->setCellValue('B' . $cursorRow, 'Kontrakan');
        $workSheet->setCellValue('C' . $cursorRow, 'Qty');
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_SUM_PROFIT);
        // set row hight
        $workSheet->getRowDimension($cursorRow)->setRowHeight(22);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_SUM_PROFIT);
            $workSheet->setCellValue($cell, bulan(Carbon::parse($p)->month));
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_SUM_PROFIT);
        $workSheet->setCellValue($char . $cursorRow, 'T O T A L');
        // set data profit
        $cursorRow++;
        $startRowProfit = $cursorRow;
        foreach ($profit as $key => $value) {
            $workSheet->getRowDimension($cursorRow, $key)->setRowHeight(20);
            $workSheet->setCellValue('B' . $cursorRow, $key);
            $workSheet->setCellValue('C' . $cursorRow, Kamar::whereHas('kontrakan', function ($query) use ($key) {
                return $query->where('nama_kontrakan', $key);
            })->count());
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
            $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_SUM_PROFIT);
            $workSheet->setCellValue($char . $cursorRow, "=SUM(D$cursorRow:$prevChar$cursorRow)");
            $cursorRow++;
        };
        //set footer table profit
        $workSheet->getRowDimension($cursorRow)->setRowHeight(20);
        $workSheet->setCellValue('B' . $cursorRow, 'T O T A L');
        $workSheet->setCellValue('C' . $cursorRow, '=SUM(C' . $startRowProfit . ':C' . ($cursorRow - 1) . ')');
        $workSheet->getStyle('B' . $cursorRow . ':C' . $cursorRow)->applyFromArray(TABLE_SUM_PROFIT);
        $char = 'D';
        foreach ($data['dates'] as $p) {
            $cell = $char . $cursorRow;
            $workSheet->getStyle($cell)->applyFromArray(TABLE_SUM_PROFIT);
            $workSheet->setCellValue($cell, '=SUM(' . $char . $startRowProfit . ':' . $char . ($cursorRow - 1) . ')');
            $char++;
        }
        $workSheet->getStyle($char . $cursorRow)->applyFromArray(TABLE_SUM_PROFIT);
        $workSheet->setCellValue($char . $cursorRow, '=SUM(' . $char . $startRowProfit . ':' . $char . ($cursorRow - 1) . ')');


        $writer = new Xlsx($spreadsheet);
        $writer->save('Laporan KAS.xlsx');
        return response()->download('Laporan KAS.xlsx', 'Laporan KAS.xlsx', [
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
            $data['kontrakan'] = Kontrakan::where('code_kontrakan', $code_kontrakan)->pluck('nama_kontrakan');
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
        $kontrakan = Kontrakan::all();
        if ($code_kontrakan !== 'all' && $code_kontrakan !== null) {
            $kontrakan = $kontrakan->where('code_kontrakan', $code_kontrakan);
        }
        $data['per_kontrakan']['pemasukan'] = $kontrakan
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
                                    ->where('id_kamar', 'like', "%[\"" . $item->kamar->id . "\"]%")
                                    ->whereHas('transaksiMasuk', function ($query) use ($item, $d) {
                                        return $query
                                            ->where('periode_sewa', 'LIKE', $d . "%");
                                    })->first() ?? null;
                            }
                            return [
                                'detail_kamar' => $item->kamar,
                                'detail_penyewa' => $item,
                                'detail_transaksi' => $trx, // list transaksi berdasarkan tanggal terpilih
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

        if ($code_kontrakan !== 'all' && $code_kontrakan !== null) {
            $transaksi = $transaksi->where('code_kontrakan', $code_kontrakan);
        }
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

    public function exportExcel(Request $request)
    {
        $kontrakan = Kontrakan::select('code_kontrakan', 'nama_kontrakan')->get();

        $data = [
            'pageTitle' => 'Export Laporan',
            'kontrakan' => $kontrakan
        ];

        return view('admin.laporan.export', $data);
    }
}
