<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CompanySite;
use App\Models\Company_unit;
use App\Models\WorkPoint;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * IMPORTANT:
     * - The first company, first unit and first work point are kept as they were.
     * - All other companies, units and work points follow the code series.
     * - No hard-coded IDs are used for company_id or comp_unit_id.
     *   IDs are taken from the records after saving to avoid wrong ID issues.
     *
     * @return void
     */
    public function run()
    {
        $companies = [
            [
                'company_code' => 'NHL001',
                'company_name' => 'NILE Holding Limited',
                'Type' => 'HOLDING',
                'city' => 'MWANZA',
                'district' => 'MWANZA MJINI',
                'TIN' => '123-456-789',
                'phone_No' => '07XXXXXXXX',
                'stamp' => 'Stamp Mbogo Mining.png',
                'signature' => 'sign.png',
                'logo' => 'Mbogo Logo.png',
                'units' => [
                    [
                        'unit_code' => 'NHL0011',
                        'unit_name' => 'NILE Holding Limited',
                        'location' => 'Mwanza',
                        'city' => 'Mwanza',
                        'district' => 'Mwanza Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'NHL00111',
                                'work_name' => 'NILE Holding Limited',
                                'location' => 'Mwanza',
                                'city' => 'MWANZA',
                                'district' => 'MWANZA MJINI',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                ],
            ],

            [
                'company_code' => 'MGL001',
                'company_name' => 'Mbogo Mining and General Supply Limited',
                'Type' => 'MINING',
                'city' => 'MWANZA',
                'district' => 'ILEMELA',
                'TIN' => '123-456-789',
                'phone_No' => '07XXXXXXXX',
                'stamp' => 'Stamp Mbogo Mining.png',
                'signature' => 'sign.png',
                'logo' => 'Mbogo Logo.png',
                'units' => [
                    [
                        'unit_code' => 'MGL0011',
                        'unit_name' => 'MANUFACTUREING OF COMMERCIAL EXPLOSIVES',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'MGL00111',
                                'work_name' => 'IHAYABUYAGA MAGU MWANZA',
                                'location' => 'Ihayabuyaga',
                                'city' => 'MWANZA',
                                'district' => 'Magu',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                    [
                        'unit_code' => 'MGL0012',
                        'unit_name' => 'TRADING OF COMERCIAL EXPLOSIVES',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'MGL00121',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                            [
                                'work_code' => 'MGL00122',
                                'work_name' => 'MERERANI MERERANI MANYARA',
                                'location' => 'Mererani',
                                'city' => 'MANYARA',
                                'district' => 'Simanjiro',
                                'phone_No' => '07XXXXXXXX',
                            ],
                            [
                                'work_code' => 'MGL00123',
                                'work_name' => 'MBEZI LUISI UBUNGO DAR ES SALAAM',
                                'location' => 'Mbezi Luisi',
                                'city' => 'DAR ES SALAAM',
                                'district' => 'Ubungo',
                                'phone_No' => '07XXXXXXXX',
                            ],
                            [
                                'work_code' => 'MGL00124',
                                'work_name' => 'RUANDA MBINGA RUVUMA',
                                'location' => 'Ruanda',
                                'city' => 'MBINGA',
                                'district' => 'Ruvuma',
                                'phone_No' => '07XXXXXXXX',
                            ],
                            [
                                'work_code' => 'MGL00125',
                                'work_name' => 'LUGOBA BAGAMOYO PWANI',
                                'location' => 'Lugoba',
                                'city' => 'PWANI',
                                'district' => 'Bagamoyo',
                                'phone_No' => '07XXXXXXXX',
                            ],
                            [
                                'work_code' => 'MGL00126',
                                'work_name' => 'MWAKITOLIYO KAHAMA SHINYANGA',
                                'location' => 'Mwakitoliyo',
                                'city' => 'SHINYANGA',
                                'district' => 'Kahama',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                    [
                        'unit_code' => 'MGL0013',
                        'unit_name' => 'BLASTING AND DRILLING SERVICES',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'MGL00131',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                    [
                        'unit_code' => 'MGL0014',
                        'unit_name' => 'GENERAL SUPPLY',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'MGL00141',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                ],
            ],

            [
                'company_code' => 'NCL001',
                'company_name' => 'Nile Complex Plaza Limited',
                'Type' => 'FUEL',
                'city' => 'MWANZA',
                'district' => 'ILEMELA',
                'TIN' => '123-456-789',
                'phone_No' => '07XXXXXXXX',
                'stamp' => 'Stamp Nile.png',
                'signature' => 'sign.png',
                'logo' => 'Nile Pub Logo.png',
                'units' => [
                    [
                        'unit_code' => 'NCL0011',
                        'unit_name' => 'NILE OIL: TRADING OF PETROLEUM PRODUCTS',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'NCL00111',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                    [
                        'unit_code' => 'NCL0012',
                        'unit_name' => 'NILE PUB: TRADING OF FOODS AND DRINKS',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'NCL00112',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                    [
                        'unit_code' => 'NCL0013',
                        'unit_name' => 'NILE GARAGE: SPARE PARTS, CAR WASH AND SERVICES',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'NCL00113',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                    [
                        'unit_code' => 'NCL0014',
                        'unit_name' => 'NILE FINANCIAL HUB: CASH POINT',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'NCL00114',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                ],
            ],

            [
                'company_code' => 'NFL001',
                'company_name' => 'Nyashishi Finance Limited',
                'Type' => 'FINANCE',
                'city' => 'MWANZA',
                'district' => 'ILEMELA',
                'TIN' => '123-456-789',
                'phone_No' => '07XXXXXXXX',
                'stamp' => 'Microfinance Nyashishi.png',
                'signature' => 'sign.png',
                'logo' => null,
                'units' => [
                    [
                        'unit_code' => 'NFL0011',
                        'unit_name' => 'SMALL AND MEDIUM ENTERPRISES LOAN',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'NFL00111',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                    [
                        'unit_code' => 'NFL0012',
                        'unit_name' => 'SALARIED WORKERS LOAN',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'NFL00112',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                    [
                        'unit_code' => 'NFL0013',
                        'unit_name' => 'ASSET LOAN',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'NFL00113',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                ],
            ],

            [
                'company_code' => 'NIL001',
                'company_name' => 'Nibengo Insurance Limited',
                'Type' => 'INSUARANCE',
                'city' => 'MWANZA',
                'district' => 'ILEMELA',
                'TIN' => '123-456-789',
                'phone_No' => '07XXXXXXXX',
                'stamp' => 'Stamp Nibengo insuarance.png',
                'signature' => 'sign.png',
                'logo' => null,
                'units' => [
                    [
                        'unit_code' => 'NIL0011',
                        'unit_name' => 'MOTOR VEHICLE INSURANCE',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'NIL00111',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                    [
                        'unit_code' => 'NIL0012',
                        'unit_name' => 'MOTOR CYCLE INSURANCE',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'NIL00112',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                    [
                        'unit_code' => 'NIL0013',
                        'unit_name' => 'FIRE INSURANCE',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'NIL00113',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                    [
                        'unit_code' => 'NIL0014',
                        'unit_name' => 'LIFE INSURANCE',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'NIL00114',
                                'work_name' => 'HEAD OFFICE NYASHISHI MWANZA',
                                'location' => 'Nyashishi',
                                'city' => 'MWANZA',
                                'district' => 'Ilemela',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                ],
            ],

            [
                'company_code' => 'BAN001',
                'company_name' => 'Barnabas Athanas Nibengo',
                'Type' => 'PROPRIETOR',
                'city' => 'MWANZA',
                'district' => 'ILEMELA',
                'TIN' => '123-456-789',
                'phone_No' => '07XXXXXXXX',
                'stamp' => null,
                'signature' => 'sign.png',
                'logo' => null,
                'units' => [
                    [
                        'unit_code' => 'BAN0011',
                        'unit_name' => 'NILE FRESH SPRINGS WATER',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'BAN00111',
                                'work_name' => 'RUANDA MBINGA RUVUMA',
                                'location' => 'Ruanda',
                                'city' => 'MBINGA',
                                'district' => 'Ruvuma',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                    [
                        'unit_code' => 'BAN0012',
                        'unit_name' => 'NILE SATO SANGALA',
                        'location' => 'Meliwa',
                        'city' => 'DODOMA',
                        'district' => 'Dodoma Mjini',
                        'phone_No' => '07XXXXXXXX',
                        'work_points' => [
                            [
                                'work_code' => 'BAN00121',
                                'work_name' => 'MBEZI LUISI UBUNGO DAR ES SALAAM',
                                'location' => 'Mbezi Luisi',
                                'city' => 'DAR ES SALAAM',
                                'district' => 'Ubungo',
                                'phone_No' => '07XXXXXXXX',
                            ],
                            [
                                'work_code' => 'BAN00122',
                                'work_name' => 'USAGARA MISUNGWI MWANZA',
                                'location' => 'Usagara',
                                'city' => 'MWANZA',
                                'district' => 'Misungwi',
                                'phone_No' => '07XXXXXXXX',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($companies as $companyData) {
            $units = $companyData['units'];
            unset($companyData['units']);

            $company = new CompanySite();
            $company->company_code = $companyData['company_code'];
            $company->company_name = $companyData['company_name'];
            $company->Type = $companyData['Type'];
            $company->city = $companyData['city'];
            $company->district = $companyData['district'];
            $company->TIN = $companyData['TIN'];
            $company->phone_No = $companyData['phone_No'];
            $company->stamp = $companyData['stamp'];
            $company->signature = $companyData['signature'];
            $company->logo = $companyData['logo'];
            $company->save();

            foreach ($units as $unitData) {
                $workPoints = $unitData['work_points'];
                unset($unitData['work_points']);

                $unit = new Company_unit();
                $unit->unit_code = $unitData['unit_code'];
                $unit->unit_name = $unitData['unit_name'];
                $unit->location = $unitData['location'];
                $unit->city = $unitData['city'];
                $unit->district = $unitData['district'];
                $unit->phone_No = $unitData['phone_No'];
                $unit->company_id = $company->id;
                $unit->save();

                foreach ($workPoints as $workData) {
                    $workPoint = new WorkPoint();
                    $workPoint->work_code = $workData['work_code'];
                    $workPoint->work_name = $workData['work_name'];
                    $workPoint->location = $workData['location'];
                    $workPoint->city = $workData['city'];
                    $workPoint->district = $workData['district'];
                    $workPoint->phone_No = $workData['phone_No'];
                    $workPoint->company_id = $company->id;
                    $workPoint->comp_unit_id = $unit->id;
                    $workPoint->save();
                }
            }
        }
    }
}
