@extends('front.layouts.afterlogin')
@section('content')
<?php
use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
$formatCount = function ($value) {
    $formatted = number_format((float) $value, 2, '.', '');

    return rtrim(rtrim($formatted, '0'), '.');
};
?>
<style>
    .leave-balance-space {
        --ink: #183247;
        --muted: #63798d;
        --line: #dbe5ee;
        --brand: #245b68;
        --soft: #e9f4f6;
    }

    .leave-balance-header,
    .leave-balance-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .leave-balance-header {
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    .leave-balance-title {
        color: var(--ink);
        font-size: 1.45rem;
        font-weight: 700;
        margin: 0;
    }

    .leave-balance-subtitle {
        color: var(--muted);
        font-size: .9rem;
        margin: 5px 0 0;
    }

    .leave-balance-card .card-body {
        padding: 18px;
    }

    .leave-balance-table thead th {
        background: var(--soft);
        color: var(--ink);
        font-size: .76rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .leave-balance-table td {
        vertical-align: middle;
        font-size: .9rem;
    }

    .employee-code {
        color: var(--muted);
        font-size: .8rem;
        font-weight: 700;
    }

    .balance-chip {
        background: #f6f9fb;
        border: 1px solid #edf2f6;
        border-radius: 6px;
        color: var(--ink);
        display: inline-block;
        min-width: 150px;
        padding: 6px 9px;
    }

    .balance-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .history-button {
        align-items: center;
        background: var(--brand);
        border: 1px solid var(--brand);
        border-radius: 6px;
        color: #fff !important;
        display: inline-flex;
        font-weight: 700;
        gap: 6px;
        padding: 7px 12px;
        text-decoration: none;
        transition: all .2s ease;
        white-space: nowrap;
    }

    .history-button:hover {
        background: #1a4651;
        border-color: #1a4651;
        color: #fff !important;
        text-decoration: none;
    }

    .history-button i {
        font-size: .85rem;
    }

    .history-link {
        font-weight: 700;
        white-space: nowrap;
    }
</style>

<div class="leave-balance-space">
    <div class="leave-balance-header">
        <h2 class="leave-balance-title">Employee Leave Balance</h2>
        <p class="leave-balance-subtitle">Review leave type-wise balance for employees and open passbook-style leave history.</p>
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

    <div class="card leave-balance-card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-bordered table-hover leave-balance-table mb-0">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Employee Code & Name</th>
                            <th>Category</th>
                            <th>Leave Type & Leave Balance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($rows && count($rows) > 0)
                            @php $sl = 1; @endphp
                            @foreach($rows as $row)
                                <tr>
                                    <td>{{ $sl++ }}</td>
                                    <td>
                                        <div class="employee-code">{{ $row->employee_no ?: '--' }}</div>
                                        <div>{{ $row->employee_name ?: '--' }}</div>
                                    </td>
                                    <td>{{ $row->employee_category ?: '--' }}</td>
                                    <td>
                                        <div class="balance-chip-list">
                                            @foreach($row->leave_balances as $leaveBalance)
                                                <span class="balance-chip">
                                                    <strong>{{ $leaveBalance->leave_type_name ?: '--' }}</strong>
                                                    <br>
                                                    Balance: {{ $formatCount($leaveBalance->balance_leave) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ url($controllerRoute . '/history/' . Helper::encoded($row->employee_id)) }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="history-button">
                                            <i class="fa-solid fa-book-open"></i>
                                            Leave History
                                        </a>
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
