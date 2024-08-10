<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseAktivitasHarian implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Data Pengeluaran, bisa diambil dari database
        return collect([
            ['All', 'Gaji Kebersihan', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000'],
            ['All', 'Gaji Kebersihan', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000', '700,000'],
        ]);
    }

    public function headings(): array
    {
        return [
            ['KAMAR', 'Item Pengeluaran', 'Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'August', 'Sept', 'Oct', 'Nov', 'Des'],
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
        return 'Pengeluaran';
    }
}
