<?php

namespace App\Exports;

use App\Models\CustomerLedger;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerLedgerExport implements
    FromCollection,
    WithHeadings
{
    public function collection()
    {
        return CustomerLedger::with('customer')
            ->get()
            ->map(function ($ledger) {

                return [

                    'Customer Code' =>
                        $ledger->customer->customer_code ?? '-',

                    'Customer Name' =>
                        $ledger->customer->customer_name ?? '-',

                    'Invoice Amount' =>
                        $ledger->invoice_amount,

                    'Paid Amount' =>
                        $ledger->paid_amount,

                    'Balance' =>
                        $ledger->balance,

                    'Status' =>
                        $ledger->status,

                    'Date' =>
                        optional(
                            $ledger->created_at
                        )->format('d M Y')

                ];
            });
    }

    public function headings(): array
    {
        return [

            'Customer Code',
            'Customer Name',
            'Invoice Amount',
            'Paid Amount',
            'Balance',
            'Status',
            'Date'

        ];
    }
}