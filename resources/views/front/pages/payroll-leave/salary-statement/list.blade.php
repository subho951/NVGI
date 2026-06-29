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

    .salary-statement-employee {
        color: var(--ink);
        font-weight: 700;
        white-space: nowrap;
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

    .salary-statement-status {
        border-radius: 999px;
        display: inline-flex;
        font-size: .72rem;
        font-weight: 800;
        padding: 4px 9px;
        white-space: nowrap;
    }

    .salary-statement-status.pending {
        background: #fff4e5;
        color: #98620d;
    }

    .salary-statement-status.generated {
        background: #eef4ff;
        color: #275a9f;
    }
</style>

<div class="salary-statement-space">
    <div class="salary-statement-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h2 class="salary-statement-title">Salary Statement</h2>
                <p class="salary-statement-subtitle">Search generated salary by month, branch and category, select employees, then generate salary statement with bank details.</p>
            </div>
            <a href="{{ url($controllerRoute . '/generated-list') }}" class="btn salary-statement-link-btn">
                <i class="fa-solid fa-list me-1"></i> Statement List
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

    <div class="card salary-statement-card mb-3">
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
                        <button type="submit" class="btn salary-statement-btn w-100">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($hasSearched)
        <div class="card salary-statement-card">
            <div class="card-body">
                <form method="POST" action="{{ url($controllerRoute . '/generate') }}" onsubmit="return confirm('Generate salary statement for selected employees?');">
                    @csrf
                    <input type="hidden" name="month" value="{{ $filters['month'] }}">
                    <input type="hidden" name="year" value="{{ $filters['year'] }}">
                    <input type="hidden" name="branch_name" value="{{ $filters['branch_name'] }}">
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div class="salary-statement-muted">
                            {{ count($rows) }} generated salary row(s) found.
                        </div>
                        <button type="submit" class="btn salary-statement-btn" {{ count($rows) === 0 ? 'disabled' : '' }}>
                            <i class="fa-solid fa-file-invoice-dollar me-1"></i> Generate Salary Statement
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table id="salary-statement-table" class="table table-bordered table-hover salary-statement-table mb-0">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="salary_statement_check_all" {{ count($rows) === 0 ? 'disabled' : '' }}>
                                    </th>
                                    <th>Empl Code</th>
                                    <th>Name</th>
                                    <th>Salary</th>
                                    <th>Bank Name</th>
                                    <th>Bank Branch</th>
                                    <th>Account No.</th>
                                    <th>IFSC Code</th>
                                    <th>Account Type</th>
                                    <th>Statement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>
                                            <input type="checkbox"
                                                   class="salary-statement-row-check"
                                                   name="salary_generation_ids[]"
                                                   value="{{ $row->salary_generation_id }}">
                                        </td>
                                        <td class="font-monospace">{{ $row->employee_no ?: '--' }}</td>
                                        <td>
                                            <div class="salary-statement-employee">{{ $row->employee_name }}</div>
                                        </td>
                                        <td class="font-monospace salary-statement-total">{{ $formatMoney($row->salary_amount) }}</td>
                                        <td>{{ $row->bank_name ?: '--' }}</td>
                                        <td>{{ $row->bank_branch ?: '--' }}</td>
                                        <td class="font-monospace">{{ $row->account_no ?: '--' }}</td>
                                        <td class="font-monospace">{{ $row->ifsc_code ?: '--' }}</td>
                                        <td>{{ $row->account_type ?: '--' }}</td>
                                        <td>
                                            @if($row->statement_generated_at)
                                                <span class="salary-statement-status generated">Generated</span>
                                                <div class="salary-statement-muted">{{ $formatDateTime($row->statement_generated_at) }}</div>
                                            @else
                                                <span class="salary-statement-status pending">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            No generated salary found for selected filters.
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
        var checkAll = document.getElementById('salary_statement_check_all');
        var rowChecks = Array.prototype.slice.call(document.querySelectorAll('.salary-statement-row-check'));

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
