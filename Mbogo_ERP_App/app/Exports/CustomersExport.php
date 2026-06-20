<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomersExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return DB::table('customers as c')
            ->leftJoin('accounts as a', 'c.account_id', '=', 'a.id')
            ->leftJoin('company_sites as cs', 'c.company_id', '=', 'cs.id')
            ->leftJoin('company_units as cu', 'c.business_unit_id', '=', 'cu.id')
            ->leftJoin('work_points as wp', 'c.work_point_id', '=', 'wp.id')
            ->select(
                'c.customer_code',
                'c.customer_name',
                'a.account_code',
                'a.account_name',
                'cs.company_code',
                'cs.company_name',
                'cu.unit_name',
                'wp.work_name',
                'c.phone',
                'c.tin_number',
                'c.status'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            'Customer Code',
            'Customer Name',
            'Account Code',
            'Account Name',
            'Company Code',
            'Company Name',
            'Business Unit',
            'Location',
            'Phone',
            'TIN',
            'Status',
        ];
    }
}