<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        // DEFAULT CUSTOMER RECEIVABLE ACCOUNT
        $defaultCustomerAccount = DB::table('accnt_subcharts')->where('Status', 'Active')
            ->where('SubCode', '41740100')->whereRaw('LENGTH(SubCode) = 8')->first();
        if (!$defaultCustomerAccount) {
            echo "Customer receivable account not found.\n";
            return;
        }
        $companyId = 1;
        $compUnitId = 1;
        $workPointId = 1;
        $userId = 1;

        $customers = [
            ['customer_code' => 'CUS001', 'customer_name' => 'DAUDI', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS002', 'customer_name' => 'VICTORIA 1', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS003', 'customer_name' => 'QWIHAYA', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS004', 'customer_name' => 'MWAKITOLYO', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS005', 'customer_name' => 'TIANPIN', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS006', 'customer_name' => 'XING LIN', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS007', 'customer_name' => 'ZHIYI MINING', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS008', 'customer_name' => 'JIUDING', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS009', 'customer_name' => 'HAJOKA', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS010', 'customer_name' => 'LONG FORTUNE', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS011', 'customer_name' => 'DENSON', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS012', 'customer_name' => 'MGM MINE', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS013', 'customer_name' => 'GREEN MOUNTAIN', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS014', 'customer_name' => 'ZHONG TAN', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS015', 'customer_name' => 'SOJEM', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS016', 'customer_name' => 'MERERANI', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS017', 'customer_name' => 'SEN WO', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS018', 'customer_name' => 'JOHNSON', 'customer_type' => 'Mining Customer'],
            ['customer_code' => 'CUS019', 'customer_name' => 'MUMANGI', 'customer_type' => 'Mining Customer'],

            ['customer_code' => 'CUS020', 'customer_name' => 'CHINA RAILWAY', 'customer_type' => 'Construction Customer'],
            ['customer_code' => 'CUS021', 'customer_name' => 'SINOHYDRO', 'customer_type' => 'Construction Customer'],

            ['customer_code' => 'CUS022', 'customer_name' => 'KASCCO', 'customer_type' => 'Fuel Customer'],
            ['customer_code' => 'CUS023', 'customer_name' => 'MAGIGE', 'customer_type' => 'Fuel Customer'],

            ['customer_code' => 'CUS024', 'customer_name' => 'BENI', 'customer_type' => 'Retail Customer'],
            ['customer_code' => 'CUS025', 'customer_name' => 'MWANDANJI', 'customer_type' => 'Retail Customer'],
            ['customer_code' => 'CUS026', 'customer_name' => 'JUSTINE', 'customer_type' => 'Retail Customer'],

            ['customer_code' => 'CUS027', 'customer_name' => 'PETER MWANZA', 'customer_type' => 'Individual Customer'],
            ['customer_code' => 'CUS028', 'customer_name' => 'YUDA', 'customer_type' => 'Individual Customer'],
        ];

        foreach ($customers as $data) {
            Customer::updateOrCreate(
                [
                    'customer_code' => $data['customer_code'],
                ],
                [
                    'customer_name' => $data['customer_name'],
                    'customer_type' => $data['customer_type'],
                    'description' => 'PFI ERP Customer',

                    'account_id' => $defaultCustomerAccount->id,

                    'phone' => null,
                    'email' => null,
                    'address' => 'Tanzania',

                    'tin_number' => null,
                    'vrn' => null,

                    'credit_limit' => 0,
                    'opening_balance' => 0,

                    'company_id' => $companyId,
                    'comp_unit_id' => $compUnitId,
                    'work_point_id' => $workPointId,

                    'created_by' => $userId,
                    'updated_by' => $userId,

                    'status' => 'Active',
                ]
            );
        }

        echo "Customers seeded successfully.\n";
    }
}