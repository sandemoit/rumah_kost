<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeAktivitasHarian implements FromCollection, WithHeadings, WithTitle
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Data Pemasukan, bisa diambil dari database
        return collect([
            ['Akasia-01', 'Aska', '1-Nov', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000'],
            ['Akasia-02', 'Aska', '1-Nov', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000'],
            ['Akasia-03', 'Aska', '1-Nov', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000'],
        ]);
    }

    public function headings(): array
    {
        return [
            ['KAMAR', 'Nama Penyewa', 'Tgl Masuk', 'Price', 'Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'August', 'Sept', 'Oct', 'Nov', 'Des'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header
            1 => ['font' => ['bold' => true]],
            'A' => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Pemasukan';
    }
}
