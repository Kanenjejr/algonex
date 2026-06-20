<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendorSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | VENDORS
        |--------------------------------------------------------------------------
        | Vendor account_code must be an existing 8-digit accnt_subcharts.SubCode.
        |--------------------------------------------------------------------------
        */

        $company = DB::table('company_sites')
            ->where('company_code', 'MGL001')
            ->first();

        $companyId = $company ? $company->id : 2;

        $vendorGroups = [
            [
                'group' => 'Explosives',
                'keywords' => ['supplier', 'ordinary'],
                'vendors' => [
                    'SOLAR NITROCHEMICALS LIMITED',
                    'CURECHEM LTD',
                    'MAXAM TANZANIA',
                    'AEL MINING SERVICES',
                    'ORICA TANZANIA',
                    'IDEAL EXPLOSIVES',
                    'NITROCHEM AFRICA',
                    'EPC TANZANIA',
                    'BME EXPLOSIVES',
                    'PRD RIGS TANZANIA',
                ],
            ],
            [
                'group' => 'Fuel',
                'keywords' => ['supplier', 'ordinary'],
                'vendors' => [
                    'GBP LIMITED',
                    'OILCOM TANZANIA',
                    'MANSOOR INDUSTRIES',
                    'LAKE OIL LIMITED',
                    'TOTAL ENERGIES',
                    'PUMA ENERGY',
                    'ORYX ENERGIES',
                    'MERU PETROLEUM',
                    'TAQA ENERGY',
                    'MOIL LIMITED',
                ],
            ],
            [
                'group' => 'General',
                'keywords' => ['supplier', 'ordinary'],
                'vendors' => [
                    'MWANZA MINHUI AQUATIC',
                    'KAHAMA FISH SUPPLIERS',
                    'VICTORIA NILE FISH',
                    'LAKE FRESH FISH',
                    'TANZANIA BREWERIES LIMITED',
                    'SERENGETI BREWERIES LIMITED',
                    'COCA COLA KWANZA',
                    'AZAM BEVERAGES',
                ],
            ],
        ];

        $counter = 1;

        foreach ($vendorGroups as $group) {
            $account = $this->vendorAccount($group['keywords']);

            if (!$account) {
                $this->command->warn('No 8-digit supplier account found for vendor group: ' . $group['group']);
                continue;
            }

            foreach ($group['vendors'] as $vendorName) {
                DB::table('vendors')->updateOrInsert(
                    [
                        'vendor_name' => $vendorName,
                    ],
                    [
                        'company_id' => $companyId,
                        'vendor_code' => 'VEN-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
                        'phone_no' => null,
                        'email' => null,
                        'address' => 'Tanzania',
                        'tin_no' => null,

                        'account_code' => $account->SubCode,
                        'account_name' => $account->SubDescription,

                        'status' => 'Active',
                        'created_by' => 1,
                        'updated_by' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $counter++;
            }
        }

        $this->command->info('Vendors seeded with existing 8-digit supplier account codes.');
    }

    private function vendorAccount(array $keywords)
    {
        return DB::table('accnt_subcharts')
            ->where('Status', 'Active')
            ->whereRaw('LENGTH(SubCode) = 8')
            ->where('SubCode', 'LIKE', '40%')
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('SubDescription', 'LIKE', '%' . $keyword . '%');
                }
            })
            ->orderBy('SubCode')
            ->first()
            ?: DB::table('accnt_subcharts')
                ->where('Status', 'Active')
                ->whereRaw('LENGTH(SubCode) = 8')
                ->where('SubCode', 'LIKE', '40%')
                ->orderBy('SubCode')
                ->first();
    }
}