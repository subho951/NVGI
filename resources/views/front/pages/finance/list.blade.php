@extends('front.layouts.afterlogin')
@section('content')
<?php
use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap');

    .finance-space {
        --ink: #13263a;
        --ink-soft: #49617a;
        --bg-paper: #f6f8fb;
        --bg-surface: #ffffff;
        --line: #dbe5f0;
        --income: #138a54;
        --expense: #b1462f;
        --accent: #caa35a;
        font-family: 'Sora', sans-serif;
    }
    .finance-header-card {
        background: linear-gradient(135deg, #13263a 0%, #1f3f5f 60%, #2f567d 100%);
        color: #fff;
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 18px 40px rgba(19, 38, 58, 0.22);
        margin-bottom: 18px;
    }
    .finance-header-card h2 {
        font-family: 'Playfair Display', serif;
        margin: 0;
        font-size: 1.8rem;
        letter-spacing: .2px;
    }
    .finance-header-card p {
        margin: 8px 0 0;
        opacity: .9;
        font-size: .92rem;
    }
    .quick-card {
        border: 1px solid var(--line);
        border-radius: 14px;
        background: var(--bg-surface);
        box-shadow: 0 12px 24px rgba(17, 32, 52, .07);
        padding: 16px;
        height: 100%;
    }
    .quick-card .label {
        color: var(--ink-soft);
        font-size: .75rem;
        letter-spacing: .9px;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .quick-card .value {
        color: var(--ink);
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
    }
    .filter-card,
    .ledger-card {
        border: 1px solid var(--line);
        border-radius: 14px;
        background: var(--bg-surface);
        box-shadow: 0 10px 24px rgba(18, 33, 53, .06);
    }
    .filter-card .card-body,
    .ledger-card .card-body {
        padding: 18px;
    }
    .filter-title,
    .ledger-title {
        color: var(--ink);
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 14px;
    }
    .finance-space .form-label {
        color: var(--ink-soft);
        font-size: .78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .6px;
        margin-bottom: 6px;
    }
    .finance-space .form-control,
    .finance-space .form-select {
        border-color: #d1dce7;
        min-height: 38px;
        border-radius: 9px;
    }
    .finance-space .form-control:focus,
    .finance-space .form-select:focus {
        border-color: #86a7c9;
        box-shadow: 0 0 0 .2rem rgba(54, 106, 160, .15);
    }
    .btn-premium {
        border: 0;
        border-radius: 9px;
        min-height: 38px;
        font-weight: 600;
        letter-spacing: .2px;
    }
    .btn-search {
        background: #1a4265;
        color: #fff;
    }
    .btn-search:hover {
        background: #143653;
        color: #fff;
    }
    .btn-add {
        background: #caa35a;
        color: #1d2531;
    }
    .btn-add:hover {
        background: #bc9446;
        color: #111;
    }
    .ledger-card {
        margin-top: 16px;
    }
    .finance-table {
        margin-bottom: 0;
    }
    .finance-table thead th {
        white-space: nowrap;
        border-bottom: 0;
        background: #172e45;
        color: #fff;
        font-size: .78rem;
        letter-spacing: .5px;
        text-transform: uppercase;
        padding: .78rem .62rem;
    }
    .finance-table td {
        vertical-align: middle;
        font-size: .88rem;
        color: #203549;
    }
    .amount {
        font-weight: 700;
        white-space: nowrap;
    }
    .amount-income {
        color: var(--income);
    }
    .amount-expense {
        color: var(--expense);
    }
    .txn-type {
        font-size: .72rem;
        border-radius: 999px;
        padding: 4px 10px;
        color: #fff;
        display: inline-block;
        letter-spacing: .4px;
    }
    .txn-income {
        background: linear-gradient(120deg, #138a54, #18a967);
    }
    .txn-expense {
        background: linear-gradient(120deg, #9f3d2a, #c45437);
    }
    .action-link {
        color: #1b4266;
        margin: 0 4px;
        font-size: .96rem;
    }
    .action-link:hover {
        color: #0e2338;
    }
    .summary-section {
        margin-top: 2px;
    }
    .summary-card {
        border: 1px solid var(--line);
        border-radius: 14px;
        background: var(--bg-surface);
        box-shadow: 0 10px 24px rgba(18, 33, 53, .06);
        overflow: hidden;
        height: 100%;
    }
    .summary-card-head {
        background: #152d45;
        color: #fff;
        padding: 12px 16px;
        font-size: .82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .summary-card .table-responsive {
        border-radius: 0;
    }
    .summary-table {
        margin-bottom: 0;
    }
    .summary-table thead th {
        background: #eef4fa;
        color: #203549;
        font-size: .74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .45px;
        white-space: nowrap;
    }
    .summary-table td {
        font-size: .84rem;
        color: #203549;
    }
    .summary-table .text-end {
        white-space: nowrap;
    }
    .table-responsive {
        border-radius: 12px;
    }
    @media (max-width: 767px) {
        .finance-header-card {
            padding: 16px;
        }
        .finance-header-card h2 {
            font-size: 1.4rem;
        }
        .ledger-card .card-body,
        .filter-card .card-body {
            padding: 14px;
        }
    }
</style>

<div class="finance-space">
    <div class="finance-header-card">
        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h2>Finance Ledger</h2>
                <p>Track every transaction, audit movement by date and user, and generate printable invoices instantly.</p>
            </div>
            <a href="{{ url('finance/add') }}" class="btn btn-premium btn-add">
                <i class="fa-solid fa-plus me-1"></i> Add Transaction
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

    <div class="row g-3 mb-2">
        <div class="col-md-3">
            <div class="quick-card">
                <p class="label">Total Records</p>
                <p class="value">{{ number_format((int)$total_records) }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quick-card">
                <p class="label">Total Income</p>
                <p class="value text-success">INR {{ number_format((float)$total_income, 2) }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quick-card">
                <p class="label">Total Expense</p>
                <p class="value text-danger">INR {{ number_format((float)$total_expense, 2) }}</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="quick-card">
                <p class="label">Net Balance</p>
                <p class="value {{ ((float)$net_balance >= 0)?'text-success':'text-danger' }}">INR {{ number_format((float)$net_balance, 2) }}</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3 summary-section">
        <div class="col-lg-6">
            <div class="summary-card">
                <div class="summary-card-head">Unit Wise Summary</div>
                <div class="table-responsive">
                    <table class="table table-sm summary-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Unit</th>
                                <th class="text-end">Records</th>
                                <th class="text-end">Income</th>
                                <th class="text-end">Expense</th>
                                <th class="text-end">Net Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($unit_summary_rows as $summary)
                                <tr>
                                    <td>{{ $summary['unit_name'] }}</td>
                                    <td class="text-end">{{ number_format((int)$summary['records']) }}</td>
                                    <td class="text-end text-success">INR {{ number_format((float)$summary['income'], 2) }}</td>
                                    <td class="text-end text-danger">INR {{ number_format((float)$summary['expense'], 2) }}</td>
                                    <td class="text-end {{ ((float)$summary['net_balance'] >= 0) ? 'text-success' : 'text-danger' }}">INR {{ number_format((float)$summary['net_balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3">No transactions found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="summary-card">
                <div class="summary-card-head">Branch Wise Summary</div>
                <div class="table-responsive">
                    <table class="table table-sm summary-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Unit</th>
                                <th>Branch</th>
                                <th class="text-end">Records</th>
                                <th class="text-end">Income</th>
                                <th class="text-end">Expense</th>
                                <th class="text-end">Net Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branch_summary_rows as $summary)
                                <tr>
                                    <td>{{ $summary['unit_name'] }}</td>
                                    <td>{{ $summary['branch_name'] }}</td>
                                    <td class="text-end">{{ number_format((int)$summary['records']) }}</td>
                                    <td class="text-end text-success">INR {{ number_format((float)$summary['income'], 2) }}</td>
                                    <td class="text-end text-danger">INR {{ number_format((float)$summary['expense'], 2) }}</td>
                                    <td class="text-end {{ ((float)$summary['net_balance'] >= 0) ? 'text-success' : 'text-danger' }}">INR {{ number_format((float)$summary['net_balance'], 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3">No transactions found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card filter-card">
        <div class="card-body">
            <div class="filter-title">Search Filters</div>
            <form method="GET" action="{{ url('finance/list') }}">
                <div class="row g-3">
                    <div class="col-lg-2 col-md-4">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" name="from_date" id="from_date" value="{{ $from_date }}">
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" name="to_date" id="to_date" value="{{ $to_date }}">
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" name="type" id="type">
                            <option value="">All</option>
                            <option value="INCOME" {{ (($type == 'INCOME')?'selected':'') }}>INCOME</option>
                            <option value="EXPENSE" {{ (($type == 'EXPENSE')?'selected':'') }}>EXPENSE</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label for="unit_id" class="form-label">Unit</label>
                        <select class="form-select" name="unit_id" id="unit_id">
                            <option value="">All Units</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ ((string)$unit_id === (string)$unit->id)?'selected':'' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label for="branch_id" class="form-label">Branch</label>
                        <select class="form-select" name="branch_id" id="branch_id">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option class="branch unit{{ $branch->unit_id }}" value="{{ $branch->id }}" {{ ((string)$branch_id === (string)$branch->id)?'selected':'' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label for="created_by" class="form-label">Created By</label>
                        <select class="form-select" name="created_by" id="created_by">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ ((string)$created_by === (string)$user->id)?'selected':'' }}>
                                    {{ trim($user->first_name.' '.$user->last_name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-premium btn-search px-4">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                        </button>
                        <a href="{{ url('finance/list') }}" class="btn btn-outline-secondary btn-premium px-4">
                            <i class="fa-solid fa-rotate-left me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card ledger-card">
        <div class="card-body">
            <div class="ledger-title">Transaction Register</div>
            <div class="table-responsive">
                <table id="example" class="table table-hover table-bordered finance-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Txn No</th>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Particulars</th>
                            <th>Note</th>
                            <th>Unit</th>
                            <th>Branch</th>
                            <th>Payment Mode</th>
                            <th>Payment Reference</th>
                            <th>Entry By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($rows && count($rows) > 0)
                            @php $sl = 1; @endphp
                            @foreach($rows as $row)
                                @php
                                    $encodedId = Helper::encoded($row->id);
                                    $amountClass = (($row->type == 'INCOME') ? 'amount-income' : 'amount-expense');
                                    $typeClass = (($row->type == 'INCOME') ? 'txn-income' : 'txn-expense');
                                @endphp
                                <tr>
                                    <td>{{ $sl++ }}</td>
                                    <td>{{ $row->txn_no }}</td>
                                    <td>{{ (($row->transaction_timestamp)?date('d M Y h:i A', strtotime($row->transaction_timestamp)):'--') }}</td>
                                    <td>
                                        <span class="txn-type {{ $typeClass }}">{{ $row->type }}</span>
                                    </td>
                                    <td class="amount {{ $amountClass }}">INR {{ number_format((float)$row->transaction_amount, 2) }}</td>
                                    <td>{{ $row->particulars }}</td>
                                    <td>{{ (($row->note != '')?$row->note:'--') }}</td>
                                    <td>{{ (($row->unit_name != '')?$row->unit_name:'--') }}</td>
                                    <td>{{ (($row->branch_name != '')?$row->branch_name:'--') }}</td>
                                    <td>{{ (($row->payment_mode != '')?$row->payment_mode:'--') }}</td>
                                    <td>{{ (($row->payment_reference != '')?$row->payment_reference:'--') }}</td>
                                    <td>{{ (($row->creator_name != '')?$row->creator_name:'System') }}</td>
                                    <td class="text-center">
                                        <a href="{{ url($controllerRoute.'/invoice/'.$encodedId) }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="action-link"
                                           title="Invoice">
                                            <i class="fa-solid fa-file-invoice-dollar"></i>
                                        </a>
                                        <?php if($row->fee_id <= 0){?>
                                            <a href="{{ url($controllerRoute.'/edit/'.$encodedId) }}"
                                            class="action-link"
                                            title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        <?php }?>
                                        <!-- <a href="javascript:void(0);"
                                           onclick="showConfirmBox('{{ $encodedId }}', '{{ $controllerRoute.'/delete/' }}', 'Are you sure you want to delete this transaction?')"
                                           class="action-link text-danger"
                                           title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a> -->
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
@section('scripts')
<script>
    $(function () {
        function bindUnitWiseBranch(unitId, branchSelector, optionClass, selectedBranch) {
            var branchSelect = $(branchSelector);

            branchSelect.val('');
            branchSelect.find('.' + optionClass).hide();

            if (unitId !== '') {
                branchSelect.find('.unit' + unitId).show();
            }

            if (selectedBranch !== '') {
                branchSelect.val(selectedBranch);
            }
        }

        bindUnitWiseBranch($('#unit_id').val(), '#branch_id', 'branch', $('#branch_id').val());

        $('#unit_id').on('change', function () {
            bindUnitWiseBranch($(this).val(), '#branch_id', 'branch', '');
        });
    });
</script>
@endsection
