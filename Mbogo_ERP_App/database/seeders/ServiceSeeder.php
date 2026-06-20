<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {

        $workpoints = DB::table('work_points')

            ->where('status', 'Active')

            ->get();



        foreach ($workpoints as $workpoint) {

            $businessUnit = DB::table('company_units')

                ->where(
                    'id',
                    $workpoint->comp_unit_id
                )

                ->first();



            if (!$businessUnit) {

                continue;
            }



            $unitCode = strtoupper(
                $businessUnit->unit_code
            );



            $services = [];



            if (
                str_contains($unitCode, 'NHL')
            ) {

                $services = [

                    'Corporate Management Service',

                    'Business Management Consultation',

                    'Holding Operations Service',
                ];
            }



            elseif (
                $unitCode == 'MGL0011'
            ) {

                $services = [

                    'Commercial Explosive Manufacturing Service',

                    'Explosive Packaging Service',

                    'Mining Explosive Support Service',

                    'Explosive Quality Testing Service',
                ];
            }



            elseif (
                $unitCode == 'MGL0012'
            ) {

                $services = [

                    'Explosive Supply Service',

                    'Mining Logistics Service',

                    'Explosive Transportation Service',

                    'Commercial Explosive Distribution Service',
                ];
            }



            elseif (
                $unitCode == 'MGL0013'
            ) {

                $services = [

                    'Blasting Service',

                    'Drilling Service',

                    'Drilling and Blasting Service',

                    'Excavation Service',

                    'Road Construction Service',

                    'Building Construction Service',

                    'Site Clearing Service',

                    'Mining Support Service',

                    'Heavy Equipment Hiring Service',

                    'Bulldozer Hiring Service',

                    'Excavator Hiring Service',

                    'Backhoe Hiring Service',

                    'Transportation Service',

                    'Haulage Service',

                    'Rock Breaking Service',

                    'Earth Moving Service',
                ];
            }



            elseif (
                $unitCode == 'MGL0014'
            ) {

                $services = [

                    'Construction Material Supply Service',

                    'Industrial Equipment Supply Service',

                    'Electrical Material Supply Service',

                    'Mining Equipment Supply Service',

                    'General Supply Service',
                ];
            }



            elseif (
                $unitCode == 'NCL0011'
            ) {

                $services = [

                    'Fuel Supply Service',

                    'Fuel Transportation Service',

                    'Petroleum Delivery Service',

                    'Lubricant Supply Service',
                ];
            }



            elseif (
                $unitCode == 'NCL0012'
            ) {

                $services = [

                    'Restaurant Service',

                    'Food Catering Service',

                    'Beverage Supply Service',

                    'Outdoor Catering Service',
                ];
            }



            elseif (
                $unitCode == 'NCL0013'
            ) {

                $services = [

                    'Vehicle Repair Service',

                    'Car Wash Service',

                    'Vehicle Maintenance Service',

                    'Spare Parts Installation Service',

                    'Tyre Service',
                ];
            }



            elseif (
                $unitCode == 'NCL0014'
            ) {

                $services = [

                    'Cash Point Service',

                    'Financial Transaction Service',

                    'Agency Banking Service',
                ];
            }



            elseif (
                $unitCode == 'NFL0011'
            ) {

                $services = [

                    'SME Loan Processing Service',

                    'Business Financing Service',

                    'Loan Consultation Service',
                ];
            }



            elseif (
                $unitCode == 'NFL0012'
            ) {

                $services = [

                    'Salary Loan Service',

                    'Payroll Financing Service',

                    'Employee Financial Consultation',
                ];
            }



            elseif (
                $unitCode == 'NFL0013'
            ) {

                $services = [

                    'Asset Financing Service',

                    'Motor Asset Financing',

                    'Equipment Financing Service',
                ];
            }



            elseif (
                $unitCode == 'NIL0011'
            ) {

                $services = [

                    'Motor Vehicle Insurance Service',

                    'Motor Claim Processing Service',

                    'Vehicle Risk Assessment Service',
                ];
            }



            elseif (
                $unitCode == 'NIL0012'
            ) {

                $services = [

                    'Motor Cycle Insurance Service',

                    'Motor Cycle Claim Processing',

                    'Rider Insurance Consultation',
                ];
            }



            elseif (
                $unitCode == 'NIL0013'
            ) {

                $services = [

                    'Fire Insurance Service',

                    'Property Risk Assessment Service',

                    'Fire Claim Processing Service',
                ];
            }



            elseif (
                $unitCode == 'NIL0014'
            ) {

                $services = [

                    'Life Insurance Service',

                    'Life Claim Processing Service',

                    'Insurance Consultation Service',
                ];
            }



            elseif (
                $unitCode == 'BAN0011'
            ) {

                $services = [

                    'Retail Shop Service',

                    'General Merchandise Supply',

                    'Consumer Goods Distribution',
                ];
            }



            elseif (
                $unitCode == 'BAN0012'
            ) {

                $services = [

                    'Fish Supply Service',

                    'Cold Storage Service',

                    'Fish Transportation Service',

                    'Fish Distribution Service',
                ];
            }



            elseif (
                $unitCode == 'BAN0013'
            ) {

                $services = [

                    'Fresh Water Supply Service',

                    'Water Distribution Service',

                    'Mineral Water Supply Service',

                    'Water Transportation Service',
                ];
            }



            foreach ($services as $index => $serviceName) {

                $serviceCode =

                    strtoupper(
                        $workpoint->work_code
                    )

                    . '-SRV-'

                    . ($index + 1);



                $exists = DB::table('services')

                    ->where(
                        'service_code',
                        $serviceCode
                    )

                    ->exists();



                if (!$exists) {

                    DB::table('services')

                        ->insert([

                            'company_id' =>
                                $workpoint->company_id,

                            'business_unit_id' =>
                                $businessUnit->id,

                            'work_point_id' =>
                                $workpoint->id,

                            'service_code' =>
                                $serviceCode,

                            'service_name' =>
                                strtoupper($serviceName),

                            'price' => 0,

                            'unit' => 'JOB',

                            'status' => 'Active',

                            'created_at' => now(),

                            'updated_at' => now(),
                        ]);
                }
            }
        }



        $this->command->info(
            'Professional ERP Services Seeded Successfully.'
        );
    }
}