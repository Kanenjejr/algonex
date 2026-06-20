<?php

namespace App\Http\Controllers;

use App\Models\CampNew;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyDashboardController extends Controller
{
    private array $companies = [
        [
            'id' => 1,
            'name' => 'Mbogo Mining and General Supply Limited',
            'code' => 'MGL001'
        ],
        [
            'id' => 2,
            'name' => 'Nile Complex Plaza Limited',
            'code' => 'NCL001'
        ],
        [
            'id' => 3,
            'name' => 'Nyashishi Finance Limited',
            'code' => 'NFL001'
        ],
        [
            'id' => 4,
            'name' => 'Nibengo Insurance Limited',
            'code' => 'NIL001'
        ],
        [
            'id' => 5,
            'name' => 'Barnabs Athanas Nibengo',
            'code' => 'BAN001'
        ],
    ];

    private array $departments = [
        [
            'name' => 'Finance & Administration',
            'permission' => 'Finance-Administration-Modules',
            'sections' => [
                ['icon' => 'fa-coins', 'name' => 'Finance & Administration Section', 'route' => 'business-admin', 'permission' => 'Administration-Modules'],
                ['icon' => 'fa-hand-holding-dollar', 'name' => 'Requisition', 'route' => 'requisition', 'permission' => 'View-Requisition-Menu'],
                ['icon' => 'fa-microchip', 'name' => 'Microfinance', 'route' => 'micro.transactions.index', 'permission' => 'Microfinancing-Modules'],
                ['icon' => 'fa-shield', 'name' => 'Insurance', 'route' => 'assets.index', 'permission' => 'View-Insurance-Menu'],
                ['icon' => 'fa-computer', 'name' => 'ICT', 'route' => 'business-admin', 'permission' => 'View-ICT-Menu'],
                ['icon' => 'fa-search', 'name' => 'Internal Audit', 'route' => 'business-admin', 'permission' => 'View-Internal-Audit-Menu'],
            ],
        ],

        [
            'name' => 'Production Department',
            'permission' => 'Production-Inventory-Manufacturing-Modules',
            'sections' => [
                ['icon' => 'fa-industry', 'name' => 'Manufacturing', 'route' => 'manufacturing', 'permission' => 'Inventory-Manufacturing-Modules'],
                ['icon' => 'fa-bolt', 'name' => 'Drilling & Blasting', 'route' => 'manufacturing', 'permission' => 'View-Drilling-Blasting-Menu'],
                ['icon' => 'fa-water', 'name' => 'Water Supply', 'route' => 'manufacturing', 'permission' => 'View-Water-Supply-Menu'],
                ['icon' => 'fa-hammer', 'name' => 'Construction', 'route' => 'manufacturing', 'permission' => 'View-Construction-Menu'],
                ['icon' => 'fa-lightbulb', 'name' => 'Innovation', 'route' => 'manufacturing', 'permission' => 'View-Innovation-Menu'],
            ],
        ],

        [
            'name' => 'Business Development',
            'permission' => 'Business-Development-Sales-Marketing-Modules',
            'sections' => [
                ['icon' => 'fa-box', 'name' => 'General Supply', 'route' => 'sales.management.dashboard', 'permission' => 'View-General-Supply-Menu'],
                ['icon' => 'fa-chart-line', 'name' => 'Sales & Marketing', 'route' => 'sales.management.dashboard', 'permission' => 'Sales-Marketing-Modules'],
                ['icon' => 'fa-truck', 'name' => 'Logistics', 'route' => 'sales.management.dashboard', 'permission' => 'View-Logistics-Menu'],
                ['icon' => 'fa-store', 'name' => 'Store Management', 'route' => 'assets.index', 'permission' => 'View-Store-Management-Menu'],
            ],
        ],
    ];

    private array $allowedRoles = [
        'Administrator',
        'Chief Accountant',
        'Accountant Director',
        'Managing Director'
    ];

    private function getNews()
    {
        return CampNew::visible()->orderByDesc('publish_at')
            ->latest()->take(5)->get();
    }

    private function getUserNotice(): string
    {
        $user = Auth::user();
        return "Welcome {$user->name}, please review the latest company announcements and departmental updates.";
    }

    private function can(string $permission): bool
    {
        $user = Auth::user();
        return $user ? $user->can($permission) : false;
    }

    private function filterDepartments(): array
    {
        $visibleDepartments = [];

        foreach ($this->departments as $department) {
            $visibleSections = [];

            foreach ($department['sections'] as $section) {
                if ($this->can($section['permission'])) {
                    $visibleSections[] = $section;
                }
            }

            $parentAllowed = !empty($department['permission']) && $this->can($department['permission']);

            if ($parentAllowed || count($visibleSections) > 0) {
                $department['sections'] = $visibleSections;
                $visibleDepartments[] = $department;
            }
        }

        return $visibleDepartments;
    }

    public function index()
    {
        return view('auth.company-dashboard', [
            'companies'    => $this->companies,
            'departments'  => $this->filterDepartments(),
            'news'         => $this->getNews(),
            'allowedRoles' => $this->allowedRoles,
            'userNotice'   => $this->getUserNotice(),
        ]);
    }
}