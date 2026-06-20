<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    public function run()
    {
        $companyId   = 2;
        $compUnitId  = 2;
        $workPointId = 2;
        $defaultStatus = 'Active';

        // Departments to ensure exist (depCode used as unique key)
        $departments = [
            ['depCode' => 'FAD001', 'depName' => 'Finance and Administration Department'],
            ['depCode' => 'PRD001', 'depName' => 'Production Department'],
            ['depCode' => 'BDD001', 'depName' => 'Business Development Department'],
        ];
        $deptRecords = [];
        foreach ($departments as $d) {
            $dept = Department::firstOrCreate(
                [
                    'depCode'    => $d['depCode'],
                    'company_id' => $companyId,
                ],
                [
                    'depName'       => $d['depName'],
                    'company_id'    => $companyId,
                    'comp_unit_id'  => $compUnitId,
                    'work_point_id' => $workPointId,
                ]
            );

            $deptRecords[$d['depCode']] = $dept;
        }

        // Sections by department
        $sectionsByDept = [
            'FAD001' => [
                ['secCode' => 'FAD00101', 'secName' => 'General Administration Section'],
                ['secCode' => 'FAD00102', 'secName' => 'Finance Section'],
                ['secCode' => 'FAD00103', 'secName' => 'Microfinance Section'],
                ['secCode' => 'FAD00104', 'secName' => 'Insurance Section'],
                ['secCode' => 'FAD00105', 'secName' => 'Information Communication Technology Section'],
                ['secCode' => 'FAD00106', 'secName' => 'Internal Audit and Control Section'],
            ],
            'PRD001' => [
                ['secCode' => 'PRD00101', 'secName' => 'Manufacturing of Commercial Explosive Section'],
                ['secCode' => 'PRD00102', 'secName' => 'Drilling and Blasting Section'],
                ['secCode' => 'PRD00103', 'secName' => 'Water Supply Section'],
                ['secCode' => 'PRD00104', 'secName' => 'Construction Section'],
                ['secCode' => 'PRD00105', 'secName' => 'Technology Transfer, Research and Innovation Section'],
                ['secCode' => 'PRD00106', 'secName' => 'Safety, Security and Health Section'],
            ],
            'BDD001' => [
                ['secCode' => 'BDD00101', 'secName' => 'General Supply Section'],
                ['secCode' => 'BDD00102', 'secName' => 'Sales and Marketing Section'],
                ['secCode' => 'BDD00103', 'secName' => 'Logistic and Freight Management Section'],
                ['secCode' => 'BDD00104', 'secName' => 'Store Management Section'],
            ],
        ];

        foreach ($sectionsByDept as $depCode => $sections) {
            if (! isset($deptRecords[$depCode])) {
                $this->command->warn("Department {$depCode} not found/created — skipping its sections.");
                continue;
            }

            $dept = $deptRecords[$depCode];

            foreach ($sections as $s) {
                Section::firstOrCreate(
                    [
                        'secCode'   => $s['secCode'],
                        'dept_id'   => $dept->id,
                        'company_id'=> $companyId,
                    ],
                    [
                        'secName'      => $s['secName'],
                        'secCode'      => $s['secCode'],
                        'company_id'   => $companyId,
                        'comp_unit_id' => $compUnitId,
                        'work_point_id'=> $workPointId,
                        'dept_id'      => $dept->id,
                        'Status'       => $defaultStatus,
                    ]
                );
            }
        }
        $this->command->info('Departments and Sections seeded (company_id = ' . $companyId . ', status = ' . $defaultStatus . ').');
    }
}