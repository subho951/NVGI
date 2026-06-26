@extends('front.layouts.afterlogin')
@section('content')
<?php
use App\Helpers\Helper;
use App\Models\SalaryHead;

$controllerRoute = $module['controller_route'];
$formatMoney = function ($value) {
    return number_format((float) $value, 2);
};
$formatDateTime = function ($value) {
    if (!$value) {
        return '--';
    }

    return date('d-m-Y h:i A', strtotime($value));
};
?>
<style>
    .employee-salary-space {
        --ink: #173145;
        --muted: #63798d;
        --line: #dbe5ee;
        --brand: #245b68;
        --soft: #e9f4f6;
    }

    .employee-salary-header,
    .employee-salary-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .employee-salary-header {
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    .employee-salary-title {
        color: var(--ink);
        font-size: 1.45rem;
        font-weight: 700;
        margin: 0;
    }

    .employee-salary-subtitle {
        color: var(--muted);
        font-size: .9rem;
        margin: 5px 0 0;
    }

    .employee-salary-card .card-body {
        padding: 18px;
    }

    .employee-salary-table thead th {
        background: var(--soft);
        color: var(--ink);
        font-size: .76rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .employee-salary-table td {
        vertical-align: top;
        font-size: .88rem;
    }

    .employee-main {
        color: var(--ink);
        font-weight: 700;
        white-space: nowrap;
    }

    .employee-sub {
        color: var(--muted);
        font-size: .8rem;
        margin-top: 3px;
    }

    .salary-head-grid {
        display: grid;
        gap: 6px;
        min-width: 320px;
    }

    .salary-head-chip {
        align-items: center;
        border: 1px solid #edf2f6;
        border-radius: 6px;
        display: flex;
        gap: 8px;
        justify-content: space-between;
        padding: 6px 8px;
    }

    .salary-head-chip.earning {
        background: #f1fbf5;
    }

    .salary-head-chip.deduction {
        background: #fff5f3;
    }

    .salary-head-name {
        color: var(--ink);
        font-weight: 700;
    }

    .salary-head-meta {
        color: var(--muted);
        display: block;
        font-size: .74rem;
        font-weight: 600;
        margin-top: 2px;
    }

    .salary-head-amount {
        color: var(--ink);
        font-family: monospace;
        font-weight: 700;
        white-space: nowrap;
    }

    .salary-summary {
        display: grid;
        gap: 5px;
        min-width: 155px;
    }

    .salary-summary-line {
        align-items: center;
        background: #f6f9fb;
        border: 1px solid #edf2f6;
        border-radius: 6px;
        color: var(--ink);
        display: flex;
        gap: 12px;
        justify-content: space-between;
        padding: 5px 8px;
        white-space: nowrap;
    }

    .salary-summary-line strong {
        font-family: monospace;
    }

    .btn-salary-calculate {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        border-radius: 6px;
        font-weight: 700;
        white-space: nowrap;
    }

    .btn-salary-calculate:hover {
        background: #1a4651;
        border-color: #1a4651;
        color: #fff;
    }

    .empty-breakdown {
        color: var(--muted);
        font-weight: 600;
        white-space: nowrap;
    }
</style>

<div class="employee-salary-space">
    <div class="employee-salary-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="employee-salary-title">Employee wise salary</h2>
                <p class="employee-salary-subtitle">Calculate salary-head-wise value from employee gross salary and store each employee breakdown.</p>
            </div>
            <form method="POST" action="{{ url($controllerRoute . '/calculate-all') }}" onsubmit="return confirm('Calculate salary for all active employees?');">
                @csrf
                <button type="submit" class="btn btn-salary-calculate" {{ ((int) $activeSalaryHeadCount === 0 || !($rows && count($rows) > 0)) ? 'disabled' : '' }}>
                    <i class="fa-solid fa-calculator me-1"></i> Calculate All
                </button>
            </form>
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

    @if((int) $activeSalaryHeadCount === 0)
        <div class="alert alert-warning">
            Please add and activate salary heads before calculating employee salary.
        </div>
    @endif

    <div class="card employee-salary-card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="employee-wise-salary-table" class="table table-bordered table-hover employee-salary-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Category</th>
                            <th>Gross Salary</th>
                            <th>Salary Head Wise Value</th>
                            <th>Total</th>
                            <th>Last Calculate</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($rows && count($rows) > 0)
                            @php $sl = 1; @endphp
                            @foreach($rows as $row)
                                @php
                                    $employee = $row->employee;
                                    $salaryRows = $row->salary_rows;
                                    $calculateUrl = url($controllerRoute . '/calculate/' . Helper::encoded($employee->id));
                                @endphp
                                <tr>
                                    <td>{{ $sl++ }}</td>
                                    <td>
                                        <div class="employee-main">{{ $row->employee_name }}</div>
                                        <div class="employee-sub">{{ $employee->employee_no ?: '--' }}</div>
                                    </td>
                                    <td>{{ $row->employee_category ?: '--' }}</td>
                                    <td class="font-monospace">{{ $formatMoney($row->gross_salary) }}</td>
                                    <td>
                                        @if($salaryRows->isNotEmpty())
                                            <div class="salary-head-grid">
                                                @foreach($salaryRows as $salaryRow)
                                                    <div class="salary-head-chip {{ $salaryRow->salary_head_type === SalaryHead::TYPE_DEDUCTION ? 'deduction' : 'earning' }}">
                                                        <div>
                                                            <span class="salary-head-name">{{ $salaryRow->salary_head_name }}</span>
                                                            <span class="salary-head-meta">
                                                                {{ $salaryRow->salary_head_type }} | {{ $salaryRow->formula_label ?: '--' }} | Payslip: {{ $salaryRow->is_payslip_show ? 'YES' : 'NO' }}
                                                            </span>
                                                        </div>
                                                        <span class="salary-head-amount">{{ $formatMoney($salaryRow->calculated_amount) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="empty-breakdown">Not calculated</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="salary-summary">
                                            <div class="salary-summary-line">
                                                <span>Earning</span>
                                                <strong>{{ $formatMoney($row->earning_total) }}</strong>
                                            </div>
                                            <div class="salary-summary-line">
                                                <span>Deduction</span>
                                                <strong>{{ $formatMoney($row->deduction_total) }}</strong>
                                            </div>
                                            <div class="salary-summary-line">
                                                <span>Net</span>
                                                <strong>{{ $formatMoney($row->net_salary) }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $formatDateTime($row->last_calculated_at) }}</td>
                                    <td>
                                        <form method="POST" action="{{ $calculateUrl }}" onsubmit="return confirm('Calculate salary for {{ addslashes($row->employee_name . ($row->employee_category ? ' - ' . $row->employee_category : '')) }}?');">
                                            @csrf
                                            <input type="hidden" name="employee_category" value="{{ $row->employee_category }}">
                                            <button type="submit" class="btn btn-sm btn-salary-calculate" {{ ((int) $activeSalaryHeadCount === 0) ? 'disabled' : '' }}>
                                                <i class="fa-solid fa-calculator me-1"></i> Calculate
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
