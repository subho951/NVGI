@extends('front.layouts.afterlogin')
@section('content')
<?php
$controllerRoute = $module['controller_route'];
$leaveCodes = collect($leaveTypes ?? [])->map(function ($leaveType) {
    return strtoupper(trim((string) $leaveType->name));
})->filter()->unique()->values();

if ($leaveCodes->isEmpty()) {
    $leaveCodes = collect(['CL', 'ML']);
}

$isTsaCategory = (bool) ($isTsaCategory ?? false);
if ($isTsaCategory) {
    $leaveCodes = collect();
}

$earningSalaryHeads = collect($salaryHeads)->where('type', \App\Models\SalaryHead::TYPE_EARNING)->values();
$deductionSalaryHeads = collect($salaryHeads)->where('type', \App\Models\SalaryHead::TYPE_DEDUCTION)->values();
if ($isTsaCategory) {
    $deductionSalaryHeads = $deductionSalaryHeads->reject(function ($salaryHead) {
        return preg_replace('/[^a-z0-9]+/', '', strtolower((string) $salaryHead->name)) === 'late';
    })->values();
}

$formatMoney = function ($value) {
    return $value === null ? '--' : number_format((float) $value, 2);
};
$formatRoundedMoney = function ($value) {
    return $value === null ? '--' : number_format(round((float) $value));
};
$formatCount = function ($value) {
    $formatted = number_format((float) $value, 2, '.', '');
    return rtrim(rtrim($formatted, '0'), '.');
};
$formatDate = function ($value) {
    return $value ? date('d-m-Y', strtotime($value)) : '--';
};
$formatDateTime = function ($value) {
    return $value ? date('d-m-Y h:i A', strtotime($value)) : '--';
};
$emptyColspan = 9 + $leaveCodes->count() + ($isTsaCategory ? 1 : 0);
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
        vertical-align: top;
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

    .salary-generation-employee {
        color: var(--ink);
        font-weight: 700;
        white-space: nowrap;
    }

    .salary-generation-muted {
        color: var(--muted);
        font-size: .78rem;
        font-weight: 600;
        margin-top: 3px;
    }

    .salary-generation-status {
        border-radius: 999px;
        display: inline-flex;
        font-size: .72rem;
        font-weight: 800;
        padding: 4px 9px;
        white-space: nowrap;
    }

    .salary-generation-status.ready {
        background: #e9f8ef;
        color: #1f7a3d;
    }

    .salary-generation-status.missing {
        background: #fff4e5;
        color: #98620d;
    }

    .salary-generation-status.generated {
        background: #eef4ff;
        color: #275a9f;
        margin-top: 6px;
    }

    .salary-generation-amount {
        border-radius: 6px;
        display: inline-block;
        font-weight: 800;
        min-width: 82px;
        padding: 4px 7px;
        text-align: right;
    }

    .salary-generation-amount.earning {
        background: #e8f7ee;
        color: #1f7a3d;
    }

    .salary-generation-amount.deduction {
        background: #fdecec;
        color: #b42318;
    }

    .salary-generation-total {
        font-weight: 800;
        white-space: nowrap;
    }

    .salary-generation-head-line {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        min-width: 220px;
        padding: 2px 0;
    }

    .salary-generation-head-name {
        color: var(--ink);
        font-weight: 700;
        white-space: nowrap;
    }

    .salary-generation-head-total {
        border-top: 1px solid var(--line);
        margin-top: 5px;
        padding-top: 6px;
    }
</style>

<div class="salary-generation-space">
    <div class="salary-generation-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h2 class="salary-generation-title">Salary Generation</h2>
                <p class="salary-generation-subtitle">Search monthly salary by branch and category, review calculated salary heads, leave balance and attendance deduction, then generate selected employee salary.</p>
            </div>
            <a href="{{ url($controllerRoute . '/generated-list') }}" class="btn salary-generation-link-btn">
                <i class="fa-solid fa-list me-1"></i> Generated Salary
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

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card salary-generation-card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ url($controllerRoute . '/list') }}">
                <input type="hidden" name="search" value="1">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="month" class="form-label">Month <span class="text-danger">*</span></label>
                        <select class="form-select" name="month" id="month" required>
                            @foreach($monthOptions as $monthValue => $monthLabel)
                                <option value="{{ $monthValue }}" {{ ((int) $filters['month'] === (int) $monthValue) ? 'selected' : '' }}>{{ $monthLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                        <select class="form-select" name="year" id="year" required>
                            @foreach($yearOptions as $yearValue)
                                <option value="{{ $yearValue }}" {{ ((int) $filters['year'] === (int) $yearValue) ? 'selected' : '' }}>{{ $yearValue }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="branch_name" class="form-label">Branch <span class="text-danger">*</span></label>
                        <select class="form-select" name="branch_name" id="branch_name" required>
                            <option value="">Select branch</option>
                            @foreach($branchOptions as $branchName)
                                <option value="{{ $branchName }}" {{ ($filters['branch_name'] === $branchName) ? 'selected' : '' }}>{{ $branchName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="category" id="category" required>
                            <option value="">Select category</option>
                            @foreach($categoryOptions as $category)
                                <option value="{{ $category }}" {{ ($filters['category'] === $category) ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn salary-generation-btn w-100">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($hasSearched)
        @php
            $readyRowCount = $rows->filter(fn ($row) => $row->is_ready)->count();
        @endphp
        <div class="card salary-generation-card">
            <div class="card-body">
                <form method="POST" action="{{ url($controllerRoute . '/generate') }}" onsubmit="return confirm('Generate salary for selected employees?');">
                    @csrf
                    <input type="hidden" name="month" value="{{ $filters['month'] }}">
                    <input type="hidden" name="year" value="{{ $filters['year'] }}">
                    <input type="hidden" name="branch_name" value="{{ $filters['branch_name'] }}">
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div class="salary-generation-muted">
                            {{ count($rows) }} employee row(s) found. {{ $readyRowCount }} ready for generation.
                        </div>
                        <button type="submit" class="btn salary-generation-btn" {{ $readyRowCount === 0 ? 'disabled' : '' }}>
                            <i class="fa-solid fa-file-invoice-dollar me-1"></i> Generate Salary
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="salary-generation-table" class="table table-bordered table-hover salary-generation-table mb-0">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="salary_generation_check_all" {{ $readyRowCount === 0 ? 'disabled' : '' }}>
                                    </th>
                                    <th>Name</th>
                                    <th>Employee Code</th>
                                    <th>DOJ</th>
                                    <th>Gross Salary</th>
                                    <th>Earning Total</th>
                                    <th>Deduction Total</th>
                                    <th>Net</th>
                                    @if($isTsaCategory)
                                        <th>Hour</th>
                                    @endif
                                    @foreach($leaveCodes as $leaveCode)
                                        <th>{{ $leaveCode }}</th>
                                    @endforeach
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                   class="salary-generation-row-check"
                                                   name="employee_ids[]"
                                                   value="{{ $row->employee_id }}"
                                                   {{ $row->is_ready ? '' : 'disabled' }}>
                                        </td>
                                        <td>
                                            <div class="salary-generation-employee">{{ $row->employee_name }}</div>
                                            <div class="salary-generation-muted">{{ $filters['category'] }}</div>
                                        </td>
                                        <td class="font-monospace">{{ $row->employee_no ?: '--' }}</td>
                                        <td>{{ $formatDate($row->doj) }}</td>
                                        <td class="font-monospace salary-generation-total">
                                            <div>Monthly: {{ $formatMoney($row->gross_salary) }}</div>
                                            <div class="salary-generation-muted">
                                                Payable: {{ $formatMoney($row->payable_gross_salary) }}
                                            </div>
                                            <div class="salary-generation-muted">
                                                {{ $formatDate($row->employment_period['start']) }} to
                                                {{ $formatDate($row->employment_period['end']) }}
                                                ({{ $row->employment_period['eligible_days'] }}/{{ $row->employment_period['days'] }} days)
                                            </div>
                                        </td>
                                        <td>
                                            @foreach($earningSalaryHeads as $salaryHead)
                                                <div class="salary-generation-head-line">
                                                    <span class="salary-generation-head-name">{{ $salaryHead->name }}</span>
                                                    <span class="salary-generation-amount earning font-monospace">{{ $formatMoney($row->salary_values[(int) $salaryHead->id] ?? null) }}</span>
                                                </div>
                                            @endforeach
                                            <div class="salary-generation-head-line salary-generation-head-total">
                                                <span class="salary-generation-head-name">Total</span>
                                                <span class="font-monospace salary-generation-total text-success">{{ $formatMoney($row->earning_total) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @foreach($deductionSalaryHeads as $salaryHead)
                                                <div class="salary-generation-head-line">
                                                    <span class="salary-generation-head-name">{{ $salaryHead->name }}</span>
                                                    <span class="salary-generation-amount deduction font-monospace">{{ $formatMoney($row->salary_values[(int) $salaryHead->id] ?? null) }}</span>
                                                </div>
                                            @endforeach
                                            <div class="salary-generation-head-line salary-generation-head-total">
                                                <span class="salary-generation-head-name">Total</span>
                                                <span class="font-monospace salary-generation-total text-danger">{{ $formatMoney($row->deduction_total) }}</span>
                                            </div>
                                        </td>
                                        <td class="font-monospace salary-generation-total">
                                            <span style="background-color: #000;color: #FFF;padding: 5px 10px;font-size: 17px;">{{ $formatRoundedMoney($row->net_salary) }}</span>
                                        </td>
                                        @if($isTsaCategory)
                                            <td>
                                                <div>Assigned: <strong>{{ $formatCount($row->absence['assigned_hours']) }}</strong></div>
                                                <div>Attendance: <strong>{{ $formatCount($row->absence['attendance_hours']) }}</strong></div>
                                            </td>
                                        @endif
                                        @foreach($leaveCodes as $leaveCode)
                                            @php
                                                $leave = $row->leave[$leaveCode] ?? ['alloted' => 0, 'balance' => 0];
                                            @endphp
                                            <td>
                                                <div>Alloted: <strong>{{ $formatCount($leave['alloted'] ?? 0) }}</strong></div>
                                                <div>Balance: <strong>{{ $formatCount($leave['balance'] ?? 0) }}</strong></div>
                                            </td>
                                        @endforeach
                                        <td>
                                            @if($row->is_ready)
                                                <span class="salary-generation-status ready">Ready</span>
                                            @else
                                                <span class="salary-generation-status missing">{{ $row->missing_salary_head_count }} Missing</span>
                                            @endif

                                            @if($isTsaCategory)
                                                <div class="salary-generation-muted">
                                                    Short Hours: {{ $formatCount($row->absence['short_hours'] ?? 0) }}
                                                </div>
                                                <div class="salary-generation-muted">
                                                    Hourly Rate: {{ $formatMoney($row->absence['hourly_rate'] ?? 0) }}
                                                </div>
                                                <div class="salary-generation-muted">
                                                    Hour Deduction: {{ $formatMoney($row->absence['amount'] ?? 0) }}
                                                </div>
                                                <div class="salary-generation-muted">
                                                    Holiday Excluded: {{ $formatCount($row->absence['holiday_days'] ?? 0) }} day(s)
                                                </div>
                                                <div class="salary-generation-muted">
                                                    Before DOJ Excluded: {{ $formatCount($row->absence['pre_doj_excluded_days'] ?? 0) }} day(s)
                                                </div>
                                            @else
                                                <div class="salary-generation-muted">
                                                    Late Count: {{ $formatCount($row->absence['late_count'] ?? 0) }}
                                                </div>
                                                <div class="salary-generation-muted">
                                                    Late Penalty: {{ $formatCount($row->absence['late_penalty_units'] ?? 0) }} day(s)
                                                </div>
                                                <div class="salary-generation-muted">
                                                    Late Deduction: {{ $formatMoney($row->absence['late_amount'] ?? 0) }}
                                                </div>
                                                <div class="salary-generation-muted">
                                                    Absent Count: {{ $formatCount($row->absence['absent_days']) }} day(s)
                                                </div>
                                                <div class="salary-generation-muted">
                                                    Approved Leave: {{ $formatCount($row->absence['approved_leave_days'] ?? 0) }} day(s)
                                                </div>
                                                <div class="salary-generation-muted">
                                                    Unpaid: {{ $formatCount($row->absence['unpaid_absent_days']) }} day(s)
                                                </div>
                                                <div class="salary-generation-muted">
                                                    Absent Deduction: {{ $formatMoney($row->absence['amount']) }}
                                                </div>
                                                <div class="salary-generation-muted">
                                                    Holiday Excluded: {{ $formatCount($row->absence['holiday_days'] ?? 0) }} day(s)
                                                </div>
                                                <div class="salary-generation-muted">
                                                    Before DOJ Excluded: {{ $formatCount($row->absence['pre_doj_excluded_days'] ?? 0) }} day(s)
                                                </div>
                                            @endif

                                            @if($row->generated_at)
                                                <div><span class="salary-generation-status generated">Generated {{ $formatDateTime($row->generated_at) }}</span></div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $emptyColspan }}" class="text-center text-muted py-4">
                                            No employee found for selected filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var checkAll = document.getElementById('salary_generation_check_all');
        var rowChecks = Array.prototype.slice.call(document.querySelectorAll('.salary-generation-row-check:not(:disabled)'));

        if (!checkAll) {
            return;
        }

        function syncCheckAllState() {
            var checkedCount = rowChecks.filter(function(checkbox) {
                return checkbox.checked;
            }).length;

            checkAll.checked = rowChecks.length > 0 && checkedCount === rowChecks.length;
            checkAll.indeterminate = checkedCount > 0 && checkedCount < rowChecks.length;
        }

        checkAll.addEventListener('change', function() {
            rowChecks.forEach(function(checkbox) {
                checkbox.checked = checkAll.checked;
            });
            syncCheckAllState();
        });

        rowChecks.forEach(function(checkbox) {
            checkbox.addEventListener('change', syncCheckAllState);
        });

        syncCheckAllState();
    });
</script>
@endsection
