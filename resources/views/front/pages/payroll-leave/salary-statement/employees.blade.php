@extends('front.layouts.afterlogin')
@section('content')
<?php
$controllerRoute = $module['controller_route'];
$formatMoney = function ($value) {
    return $value === null ? '--' : number_format(round((float) $value), 0);
};
$formatDateTime = function ($value) {
    return $value ? date('d-m-Y h:i A', strtotime($value)) : '--';
};
$query = [
    'month' => $filters['month'],
    'year' => $filters['year'],
    'branch_name' => $filters['branch_name'],
    'category' => $filters['category'],
];
?>
<style>
    .salary-statement-space {
        --ink: #173145;
        --muted: #63798d;
        --line: #dbe5ee;
        --brand: #245b68;
        --soft: #e9f4f6;
    }

    .salary-statement-header,
    .salary-statement-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .salary-statement-header {
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    .salary-statement-title {
        color: var(--ink);
        font-size: 1.45rem;
        font-weight: 700;
        margin: 0;
    }

    .salary-statement-subtitle {
        color: var(--muted);
        font-size: .9rem;
        margin: 5px 0 0;
    }

    .salary-statement-card .card-body {
        padding: 18px;
    }

    .salary-statement-table thead th {
        background: var(--soft);
        color: var(--ink);
        font-size: .74rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .salary-statement-table td {
        vertical-align: top;
        font-size: .86rem;
    }

    .salary-statement-btn {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        border-radius: 6px;
        font-weight: 700;
        white-space: nowrap;
    }

    .salary-statement-btn:hover {
        background: #1a4651;
        border-color: #1a4651;
        color: #fff;
    }

    .salary-statement-link-btn {
        border: 1px solid var(--brand);
        color: var(--brand);
        border-radius: 6px;
        font-weight: 700;
        white-space: nowrap;
    }

    .salary-statement-link-btn:hover {
        background: var(--brand);
        color: #fff;
    }

    .salary-statement-action-group {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .salary-statement-muted {
        color: var(--muted);
        font-size: .78rem;
        font-weight: 600;
        margin-top: 3px;
    }

    .salary-statement-total {
        font-weight: 800;
        white-space: nowrap;
    }
</style>

<div class="salary-statement-space">
    <div class="salary-statement-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h2 class="salary-statement-title">Salary Statement Employees</h2>
                <p class="salary-statement-subtitle">
                    {{ $monthOptions[(int) $filters['month']] ?? $filters['month'] }} {{ $filters['year'] }} | {{ $filters['branch_name'] }} | {{ $filters['category'] }}
                </p>
            </div>
            <div class="salary-statement-action-group">
                <a class="btn salary-statement-btn" href="{{ url($controllerRoute . '/download-excel') }}?{{ http_build_query($query) }}">
                    <i class="fa-solid fa-file-excel me-1"></i> Excel
                </a>
                <a class="btn salary-statement-btn" href="{{ url($controllerRoute . '/download-pdf') }}?{{ http_build_query($query) }}">
                    <i class="fa-solid fa-file-pdf me-1"></i> PDF
                </a>
                <a href="{{ url($controllerRoute . '/generated-list') }}" class="btn salary-statement-link-btn">
                    <i class="fa-solid fa-arrow-left me-1"></i> Statement List
                </a>
            </div>
        </div>
    </div>

    <div class="card salary-statement-card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover salary-statement-table mb-0">
                    <thead>
                        <tr>
                            <th>Sl No.</th>
                            <th>Empl Code</th>
                            <th>Name</th>
                            <th>Salary</th>
                            <th>Bank Name</th>
                            <th>Bank Branch</th>
                            <th>Account No.</th>
                            <th>IFSC Code</th>
                            <th>Account Type</th>
                            <th>Generated</th>
                            <th>Pay Slip</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="font-monospace">{{ $row->employee_no ?: '--' }}</td>
                                <td>{{ $row->employee_name ?: '--' }}</td>
                                <td class="font-monospace salary-statement-total">{{ $formatMoney($row->salary_amount) }}</td>
                                <td>{{ $row->bank_name ?: '--' }}</td>
                                <td>{{ $row->bank_branch ?: '--' }}</td>
                                <td class="font-monospace">{{ $row->account_no ?: '--' }}</td>
                                <td class="font-monospace">{{ $row->ifsc_code ?: '--' }}</td>
                                <td>{{ $row->account_type ?: '--' }}</td>
                                <td>{{ $formatDateTime($row->updated_at) }}</td>
                                <td>
                                    <a class="btn btn-sm salary-statement-btn" href="{{ url($controllerRoute . '/pay-slip/' . \App\Helpers\Helper::encoded($row->id)) }}">
                                        <i class="fa-solid fa-file-pdf me-1"></i> Pay Slip
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    No employee found in this salary statement.
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
