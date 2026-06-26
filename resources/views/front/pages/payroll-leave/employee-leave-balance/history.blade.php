@extends('front.layouts.afterlogin')
@section('content')
<?php
$controllerRoute = $module['controller_route'];
$formatCount = function ($value) {
    $formatted = number_format((float) $value, 2, '.', '');

    return rtrim(rtrim($formatted, '0'), '.');
};
?>
<style>
    .leave-history-space {
        --ink: #173145;
        --muted: #63798d;
        --line: #dbe5ee;
        --brand: #245b68;
        --soft: #e9f4f6;
        --credit: #146c43;
        --debit: #b02a37;
    }

    .leave-history-header,
    .leave-history-card,
    .summary-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .leave-history-header {
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    .leave-history-title {
        color: var(--ink);
        font-size: 1.45rem;
        font-weight: 700;
        margin: 0;
    }

    .leave-history-subtitle {
        color: var(--muted);
        font-size: .9rem;
        margin: 5px 0 0;
    }

    .btn-leave-primary {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        border-radius: 6px;
        font-weight: 600;
    }

    .btn-leave-primary:hover {
        background: #1a4651;
        border-color: #1a4651;
        color: #fff;
    }

    .leave-type-tabs {
        border-bottom: 1px solid var(--line);
        gap: 6px;
    }

    .leave-type-tabs .nav-link {
        background: #f6f9fb;
        border: 1px solid var(--line);
        border-bottom: 0;
        border-radius: 8px 8px 0 0;
        color: var(--brand);
        font-weight: 800;
        min-width: 135px;
        padding: 9px 14px;
        text-align: left;
    }

    .leave-type-tabs .nav-link.active {
        background: #fff;
        color: var(--ink);
    }

    .tab-balance {
        color: var(--muted);
        display: block;
        font-size: .78rem;
        font-weight: 700;
        margin-top: 2px;
    }

    .leave-tab-content {
        background: #fff;
        border: 1px solid var(--line);
        border-top: 0;
        border-radius: 0 0 8px 8px;
        padding: 18px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .summary-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        margin-bottom: 18px;
    }

    .summary-card {
        padding: 14px;
        box-shadow: none;
    }

    .summary-label {
        color: var(--muted);
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .summary-value {
        color: var(--ink);
        font-size: 1.35rem;
        font-weight: 800;
        margin: 2px 0 0;
    }

    .leave-history-card {
        box-shadow: none;
    }

    .leave-history-card .card-body {
        padding: 18px;
    }

    .passbook-table thead th {
        background: var(--soft);
        color: var(--ink);
        font-size: .76rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .passbook-table td {
        vertical-align: middle;
        font-size: .9rem;
    }

    .credit-value {
        color: var(--credit);
        font-weight: 700;
    }

    .debit-value {
        color: var(--debit);
        font-weight: 700;
    }

    .balance-value {
        color: var(--ink);
        font-weight: 800;
    }

    .detail-title {
        color: var(--ink);
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 10px;
    }

    @media (max-width: 767px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }

        .leave-type-tabs .nav-link {
            min-width: 115px;
        }
    }
</style>

<div class="leave-history-space">
    <div class="leave-history-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="leave-history-title">Leave History</h2>
                <p class="leave-history-subtitle">
                    {{ $employee['employee_no'] }} - {{ $employee['employee_name'] }}
                    | {{ $employee['category'] ?: '--' }}
                </p>
            </div>
            <a href="{{ url($controllerRoute . '/list') }}" class="btn btn-leave-primary px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <ul class="nav nav-tabs leave-type-tabs" id="leaveHistoryTabs" role="tablist">
        @foreach($leaveTypeHistories as $history)
            @php $isActive = ((int) $history['leave_type_id'] === (int) $selectedLeaveTypeId); @endphp
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $isActive ? 'active' : '' }}"
                        id="leave-type-{{ $history['leave_type_id'] }}-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#leave-type-{{ $history['leave_type_id'] }}"
                        type="button"
                        role="tab"
                        aria-controls="leave-type-{{ $history['leave_type_id'] }}"
                        aria-selected="{{ $isActive ? 'true' : 'false' }}">
                    {{ $history['leave_type_name'] ?: '--' }}
                    <span class="tab-balance">Balance: {{ $formatCount($history['summary']['balance']) }}</span>
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content leave-tab-content" id="leaveHistoryTabContent">
        @foreach($leaveTypeHistories as $history)
            @php $isActive = ((int) $history['leave_type_id'] === (int) $selectedLeaveTypeId); @endphp
            <div class="tab-pane fade {{ $isActive ? 'show active' : '' }}"
                 id="leave-type-{{ $history['leave_type_id'] }}"
                 role="tabpanel"
                 aria-labelledby="leave-type-{{ $history['leave_type_id'] }}-tab">
                <div class="summary-grid">
                    <div class="summary-card">
                        <div class="summary-label">Total Allotted</div>
                        <p class="summary-value">{{ $formatCount($history['summary']['allotted']) }}</p>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">Leave Taken</div>
                        <p class="summary-value">{{ $formatCount($history['summary']['used']) }}</p>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">Balance</div>
                        <p class="summary-value">{{ $formatCount($history['summary']['balance']) }}</p>
                    </div>
                </div>

                <div class="card leave-history-card mb-3">
                    <div class="card-body">
                        <div class="detail-title">Passbook</div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover passbook-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Particulars</th>
                                        <th>Allotment</th>
                                        <th>Leave Taken</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($history['passbookRows'] as $passbookRow)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($passbookRow['date'])->format('d-m-Y') }}</td>
                                            <td>{{ $passbookRow['particulars'] }}</td>
                                            <td class="credit-value">{{ ((float) $passbookRow['credit'] > 0) ? $formatCount($passbookRow['credit']) : '--' }}</td>
                                            <td class="debit-value">{{ ((float) $passbookRow['debit'] > 0) ? $formatCount($passbookRow['debit']) : '--' }}</td>
                                            <td class="balance-value">{{ $formatCount($passbookRow['balance']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-danger">No leave history found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card leave-history-card">
                    <div class="card-body">
                        <div class="detail-title">Leave Taken History</div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover passbook-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Leave Taken</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($history['takenRows'] as $takenRow)
                                        <tr>
                                            <td>{{ optional($takenRow->leave_date)->format('d-m-Y') }}</td>
                                            <td class="debit-value">{{ $formatCount($takenRow->leave_count) }}</td>
                                            <td>{{ $takenRow->remarks ?: '--' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No leave taken history found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
