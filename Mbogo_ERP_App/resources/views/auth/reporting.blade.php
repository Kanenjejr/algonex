@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background-color: #f5f7fa; min-height: 100vh;">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 text-dark">Reporting Dashboard</h2>
            <p class="text-muted mb-0">Mbogo Mining & General Supply Limited organization-wide analytics</p>
        </div>
        <div>
            <button class="btn btn-outline-primary me-2">Export PDF</button>
            <button class="btn btn-primary">Export Excel</button>
        </div>
    </div>

    {{-- Cards Section --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card shadow-sm text-white" style="background: linear-gradient(45deg, #1abc9c, #16a085); border-radius: 10px;">
                <div class="card-body text-center py-3">
                    <small>Companies</small>
                    <h3>{{ $totalCompanies }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-white" style="background: linear-gradient(45deg, #3498db, #2980b9); border-radius: 10px;">
                <div class="card-body text-center py-3">
                    <small>Units</small>
                    <h3>{{ $totalUnits }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-white" style="background: linear-gradient(45deg, #e67e22, #d35400); border-radius: 10px;">
                <div class="card-body text-center py-3">
                    <small>Location Points</small>
                    <h3>{{ $totalWorkPoints }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-white" style="background: linear-gradient(45deg, #9b59b6, #8e44ad); border-radius: 10px;">
                <div class="card-body text-center py-3">
                    <small>Departments</small>
                    <h3>{{ $totalDepartments }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-white" style="background: linear-gradient(45deg, #f39c12, #e74c3c); border-radius: 10px;">
                <div class="card-body text-center py-3">
                    <small>Sections</small>
                    <h3>{{ $totalSections }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm text-white" style="background: linear-gradient(45deg, #2ecc71, #27ae60); border-radius: 10px;">
                <div class="card-body text-center py-3">
                    <small>Users</small>
                    <h3>{{ $totalUsers }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts and Recent Work Points --}}
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm" style="border-radius: 10px;">
                <div class="card-header bg-white"><strong>Department Distribution by Company</strong></div>
                <div class="card-body" style="background-color: #fdfdfd;">
                    <canvas id="departmentChart" height="120"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm" style="border-radius: 10px;">
                <div class="card-header bg-white"><strong>Recent Work Points</strong></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($recentWorkPoints as $point)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $point->work_name }}</span>
                                <small class="text-muted">{{ $point->city }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Departments & Sections --}}
    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm" style="border-radius: 10px;">
                <div class="card-header bg-white"><strong>Recent Departments</strong></div>
                <div class="card-body table-responsive p-3">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentDepartments as $department)
                                <tr>
                                    <td>{{ $department->depName }}</td>
                                    <td>{{ $department->depCode }}</td>
                                    <td>{{ $department->Status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm" style="border-radius: 10px;">
                <div class="card-header bg-white"><strong>Recent Sections</strong></div>
                <div class="card-body table-responsive p-3">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSections as $section)
                                <tr>
                                    <td>{{ $section->secName }}</td>
                                    <td>{{ $section->secCode }}</td>
                                    <td>{{ $section->Status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="mt-5 py-3 text-center" style="background-color: #2c3e50; color: #fff;">
        &copy; {{ date('Y') }} Mbogo Mining & General Supply Limited. All rights reserved.
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('departmentChart');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($companyLabels),
        datasets: [{
            label: 'Departments',
            data: @json($departmentData),
            backgroundColor: [
                '#1abc9c', '#3498db', '#e67e22', '#9b59b6', '#f39c12', '#2ecc71'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: true } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
@endsection