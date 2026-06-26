@extends('front.layouts.afterlogin')
@section('content')
<?php
$controllerRoute = $module['controller_route'];
$formatDateTime = function ($value) {
    return $value ? date('d-m-Y h:i A', strtotime($value)) : '--';
};
?>
<style>
    .salary-generation-space {
        --ink: #173145;
        --muted: #63798d;
        --line: #dbe5ee;
        --brand: #245b68;
        --soft: #e9f4f6;
    }

    .salary-generation-header,
    .salary-generation-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .salary-generation-header {
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    .salary-generation-title {
        color: var(--ink);
        font-size: 1.45rem;
        font-weight: 700;
        margin: 0;
    }

    .salary-generation-subtitle {
        color: var(--muted);
        font-size: .9rem;
        margin: 5px 0 0;
    }

    .salary-generation-card .card-body {
        padding: 18px;
    }

    .salary-generation-table thead th {
        background: var(--soft);
        color: var(--ink);
        font-size: .74rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .salary-generation-table td {
        vertical-align: middle;
        font-size: .86rem;
    }

    .salary-generation-btn {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        border-radius: 6px;
        font-weight: 700;
        white-space: nowrap;
    }

    .salary-generation-btn:hover {
        background: #1a4651;
        border-color: #1a4651;
        color: #fff;
    }

    .salary-generation-link-btn {
        border: 1px solid var(--brand);
        color: var(--brand);
        border-radius: 6px;
        font-weight: 700;
        white-space: nowrap;
    }

    .salary-generation-link-btn:hover {
        background: var(--brand);
        color: #fff;
    }

    .salary-generation-muted {
        color: var(--muted);
        font-size: .78rem;
        font-weight: 600;
        margin-top: 3px;
    }
</style>

<div class="salary-generation-space">
    <div class="salary-generation-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h2 class="salary-generation-title">Generated Salary</h2>
                <p class="salary-generation-subtitle">Month, branch and category wise generated salary batches with Excel download.</p>
            </div>
            <a href="{{ url($controllerRoute . '/list') }}" class="btn salary-generation-link-btn">
                <i class="fa-solid fa-magnifying-glass me-1"></i> Salary Search
            </a>
        </div>
    </div>

    @if(session('success_message'))
        <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show autohide" role="alert">
            {{ session('success_message') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error_message'))
        <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show autohide" role="alert">
            {{ session('error_message') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card salary-generation-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover salary-generation-table mb-0">
                    <thead>
                        <tr>
                            <th>Sl No.</th>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Branch</th>
                            <th>Category</th>
                            <th>Employee Count</th>
                            <th>Last Generated</th>
                            <th>Download Excel</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $monthOptions[(int) $row->salary_month] ?? $row->salary_month }}</td>
                                <td>{{ $row->salary_year }}</td>
                                <td>{{ $row->branch_name }}</td>
                                <td>{{ $row->employee_category }}</td>
                                <td>{{ $row->employee_count }}</td>
                                <td>{{ $formatDateTime($row->last_generated_at) }}</td>
                                <td>
                                    <a class="btn btn-sm salary-generation-btn"
                                       href="{{ url($controllerRoute . '/download-excel') }}?{{ http_build_query(['month' => $row->salary_month, 'year' => $row->salary_year, 'branch_name' => $row->branch_name, 'category' => $row->employee_category]) }}">
                                        <i class="fa-solid fa-file-excel me-1"></i> Download Excel
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No generated salary found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
