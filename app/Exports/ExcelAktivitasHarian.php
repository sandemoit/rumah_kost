<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ExcelAktivitasHarian implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new IncomeAktivitasHarian(),
            new ExpenseAktivitasHarian(),
        ];
    }
}
