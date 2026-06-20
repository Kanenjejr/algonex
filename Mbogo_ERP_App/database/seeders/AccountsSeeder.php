<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountsSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | LEGACY accounts TABLE
        |--------------------------------------------------------------------------
        | This table is used only for FK ids in products:
        | inventory_account_id, revenue_account_id, cogs_account_id.
        |
        | REAL accounting transactions must use 8-digit accnt_subcharts.SubCode.
        |--------------------------------------------------------------------------
        */

        $accounts = [
            [
                'account_code' => 'INV001',
                'account_name' => 'Inventory Control Account',
                'type' => 'Inventory',
            ],
            [
                'account_code' => 'REV001',
                'account_name' => 'Revenue Control Account',
                'type' => 'Revenue',
            ],
            [
                'account_code' => 'COGS001',
                'account_name' => 'Cost Of Goods Sold Control Account',
                'type' => 'Expense',
            ],
        ];

        foreach ($accounts as $account) {
            DB::table('accounts')->updateOrInsert(
                [
                    'account_code' => $account['account_code'],
                ],
                [
                    'account_name' => $account['account_name'],
                    'type' => $account['type'],
                    'is_active' => 1,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $this->command->info('Legacy control accounts seeded successfully.');
    }
}