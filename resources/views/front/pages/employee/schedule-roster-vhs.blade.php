@extends('front.layouts.afterlogin')

@section('styles')
<style>
    .roster-shell {
        --roster-ink: #172033;
        --roster-muted: #6a7483;
        --roster-border: #d9dee7;
        --roster-brand: #0f62fe;
        color: var(--roster-ink);
    }

    .roster-topbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        border: 1px solid var(--roster-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 12px 30px rgba(23, 32, 51, 0.07);
    }

    .roster-title-kicker {
        color: var(--roster-muted);
        font-size: 0.74rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .roster-title {
        margin: 2px 0 0;
        color: var(--roster-ink);
        font-size: 1.45rem;
        font-weight: 850;
        letter-spacing: 0;
    }

    .roster-range {
        margin-top: 4px;
        color: var(--roster-muted);
        font-size: 0.88rem;
        font-weight: 700;
    }

    .roster-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: end;
        justify-content: flex-end;
        gap: 10px;
    }

    .roster-toolbar .form-label {
        margin-bottom: 5px;
        color: var(--roster-muted);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .roster-toolbar .form-select,
    .roster-toolbar .btn {
        min-height: 42px;
        border-radius: 8px;
    }

    .btn-roster-primary {
        border: 0;
        background: #111827;
        color: #fff;
        font-weight: 800;
    }

    .btn-roster-primary:hover {
        background: #0f172a;
        color: #fff;
    }

    .btn-roster-pdf {
        border: 1px solid #fecaca;
        background: #fff1f2;
        color: #b91c1c;
        font-weight: 800;
    }

    .btn-roster-pdf:hover {
        background: #ffe4e6;
        color: #991b1b;
    }

    .roster-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 10px;
        border: 1px solid #dfe5ee;
        border-radius: 999px;
        background: #f7f9fc;
        color: #334155;
        font-size: 0.76rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .roster-stat-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .roster-search-panel {
        padding: 12px;
        border: 1px solid var(--roster-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(23, 32, 51, 0.05);
    }

    .roster-search-grid {
        display: grid;
        grid-template-columns: 1fr 1.4fr 1.8fr auto auto;
        gap: 10px;
        align-items: end;
    }

    .roster-search-grid .form-label {
        margin-bottom: 4px;
        color: var(--roster-muted);
        font-size: 0.68rem;
        font-weight: 850;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .roster-search-grid .form-control,
    .roster-search-grid .form-select,
    .roster-search-grid .btn {
        min-height: 38px;
        border-radius: 7px;
        font-size: 0.84rem;
    }

    .roster-stat {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 82px;
        padding: 14px;
        border: 1px solid var(--roster-border);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(23, 32, 51, 0.05);
    }

    .roster-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #edf5ff;
        color: var(--roster-brand);
        font-size: 1rem;
    }

    .roster-stat-label {
        color: var(--roster-muted);
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .roster-stat-value {
        margin: 2px 0 0;
        color: var(--roster-ink);
        font-size: 1.22rem;
        font-weight: 850;
        line-height: 1;
    }

    .calendar-board {
        overflow: hidden;
        border: 1px solid var(--roster-border);
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 12px 30px rgba(23, 32, 51, 0.06);
    }

    .calendar-board-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 13px 14px;
        border-bottom: 1px solid var(--roster-border);
        background: #fbfcfe;
    }

    .calendar-board-title {
        color: var(--roster-ink);
        font-size: 1rem;
        font-weight: 850;
    }

    .calendar-board-note {
        color: var(--roster-muted);
        font-size: 0.78rem;
        font-weight: 800;
    }

    .branch-legend {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 8px 12px;
    }

    .branch-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: var(--roster-muted);
        font-size: 0.68rem;
        font-weight: 800;
    }

    .branch-legend-swatch {
        width: 14px;
        height: 14px;
        border: 1px solid rgba(15, 23, 42, 0.16);
        border-radius: 3px;
        flex: 0 0 auto;
    }

    .calendar-scroll {
        overflow-x: visible;
        width: 100%;
    }

    .calendar-grid {
        display: grid;
        width: 100%;
        min-width: 0;
    }

    .calendar-sl-head,
    .calendar-sl-cell,
    .calendar-corner,
    .calendar-day-head,
    .calendar-person,
    .calendar-cell {
        border-right: 1px solid var(--roster-border);
        border-bottom: 1px solid var(--roster-border);
    }

    .calendar-sl-head,
    .calendar-corner,
    .calendar-day-head {
        min-height: 46px;
        background: #f3f5f8;
    }

    .calendar-sl-head,
    .calendar-corner {
        position: sticky;
        z-index: 5;
        display: flex;
        align-items: center;
        padding: 8px;
        color: var(--roster-muted);
        font-size: 0.66rem;
        font-weight: 850;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .calendar-sl-head {
        left: 0;
        justify-content: center;
    }

    .calendar-corner {
        left: 42px;
        justify-content: flex-start;
    }

    .calendar-day-head {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 2px;
        padding: 5px 2px;
        text-align: center;
    }

    .calendar-day-head.is-today {
        background: #e8f2ff;
        color: #0f62fe;
    }

    .calendar-day-head.is-saturday {
        background: #fff7ed;
    }

    .calendar-day-head.is-skipped {
        background: #ffe8e8;
        color: #a13b3b;
        padding: 5px 0;
    }

    .calendar-day-head.is-skipped .calendar-day-name {
        display: none;
    }

    .calendar-day-head.is-skipped .calendar-day-date {
        font-size: 0.58rem;
    }

    .calendar-day-name {
        font-size: 0.58rem;
        font-weight: 850;
    }

    .calendar-day-date {
        font-size: 0.62rem;
        font-weight: 750;
    }

    .calendar-sl-cell,
    .calendar-person {
        position: sticky;
        z-index: 4;
        display: flex;
        align-items: center;
        min-height: 68px;
        background: #fff;
    }

    .calendar-sl-cell {
        left: 0;
        justify-content: center;
        color: #475569;
        font-size: 0.68rem;
        font-weight: 850;
    }

    .calendar-person {
        left: 42px;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        padding: 7px 8px;
        text-align: left;
    }

    .calendar-person-copy {
        min-width: 0;
        width: 100%;
        text-align: left;
    }

    .calendar-person-name {
        color: var(--roster-ink);
        font-size: 0.58rem;
        font-weight: 850;
        line-height: 1.12;
        overflow: visible;
        text-overflow: clip;
        white-space: normal;
        word-break: break-word;
        text-align: left;
    }

    .calendar-person-meta {
        margin-top: 2px;
        color: var(--roster-muted);
        font-size: 0.56rem;
        font-weight: 700;
        text-align: left;
    }

    .calendar-person-actions {
        flex: 0 0 auto;
    }

    .calendar-person-actions form {
        margin: 0;
        line-height: 1;
    }

    .teacher-delete-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 24px;
        padding: 0;
        border: 1px solid #fecaca;
        border-radius: 6px;
        background: #fff1f2;
        color: #b91c1c;
        font-size: 0.64rem;
        line-height: 1;
    }

    .teacher-delete-btn:hover {
        background: #ffe4e6;
        color: #991b1b;
    }

    .calendar-cell {
        min-height: 68px;
        padding: 3px;
        background: #fff;
    }

    .calendar-cell.is-skipped {
        background: #fff1f1;
        padding: 0;
    }

    .shift-stack {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .shift-card {
        min-height: 39px;
        padding: 4px 2px;
        border-radius: 4px;
        color: #101828;
        text-align: center;
        line-height: 1.12;
        overflow: hidden;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.4);
    }

    .shift-card.blue {
        background: #7cc7f2;
    }

    .shift-card.mint {
        background: #96e4d7;
    }

    .shift-card.pink {
        background: #f5a0cf;
    }

    .shift-card.amber {
        background: #ffed9d;
    }

    .shift-card.violet {
        background: #c7b7ff;
    }

    .shift-card.yellow {
        background: #ffed9d;
    }

    .shift-card.light-green {
        background: #b7f3c8;
    }

    .shift-card.neutral {
        background: #dbe4ee;
    }

    .shift-role {
        display: none;
        font-size: 0.58rem;
        font-weight: 850;
    }

    .shift-branch {
        display: block;
        font-size: 0.58rem;
        font-weight: 750;
        white-space: nowrap;
    }

    .shift-time {
        display: block;
        font-size: 0.62rem;
        font-weight: 850;
        white-space: normal;
        line-height: 1.08;
    }

    .shift-action-row {
        display: flex;
        justify-content: center;
        gap: 3px;
        margin-top: 4px;
    }

    .shift-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 19px;
        height: 19px;
        padding: 0;
        border: 0;
        border-radius: 4px;
        background: rgba(255, 255, 255, 0.76);
        color: #14532d;
        font-size: 0.55rem;
        line-height: 1;
    }

    .shift-icon-btn:hover {
        background: #dcfce7;
        color: #166534;
    }

    .calendar-empty-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        border-radius: 4px;
        background: #e5e7eb;
        color: #6b7280;
        font-size: 0.58rem;
        font-weight: 850;
        text-transform: uppercase;
    }

    .calendar-blank-cell {
        min-height: 68px;
        border-radius: 4px;
        background: transparent;
    }

    .roster-empty {
        padding: 42px 18px;
        border: 1px solid var(--roster-border);
        border-radius: 12px;
        background: #fff;
        color: var(--roster-muted);
        text-align: center;
    }

    @media (max-width: 991px) {
        .roster-stat-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .roster-search-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .roster-toolbar {
            justify-content: flex-start;
        }
    }

    @media (max-width: 575px) {
        .roster-stat-row {
            grid-template-columns: 1fr;
        }

        .roster-search-grid {
            grid-template-columns: 1fr;
        }

        .roster-topbar {
            padding: 15px;
        }
    }
</style>
@endsection

@section('content')
<?php
$rosterRows = collect($rows ?? []);
$stats = $stats ?? [
    'rows' => 0,
    'employees' => 0,
    'branches' => 0,
    'dates' => 0,
    'last_generated_at' => null,
];
$calendarDates = $calendarDates ?? [];
$calendarEmployees = $calendarEmployees ?? [];
$calendarCells = $calendarCells ?? [];
$branchOptions = $branchOptions ?? [];
$employeeOptions = $employeeOptions ?? [];
$branchColorLegend = $branchColorLegend ?? [];
$selectedBranchId = (int) ($selectedBranchId ?? 0);
$selectedEmployeeId = (int) ($selectedEmployeeId ?? 0);
$dateColumns = collect($calendarDates)->map(function ($date) {
    return !empty($date['is_skipped_date']) ? '16px' : 'minmax(42px, 1fr)';
})->implode(' ');
$pdfUrl = $pdfUrl ?? (url('employee/schedule-roster/vhs/pdf') . '?month=' . urlencode((string) $selectedMonthValue));
?>

<div class="roster-shell">
    <div class="roster-topbar mb-3">
        <div>
            <div class="roster-title-kicker">Schedule Roster</div>
            <h2 class="roster-title">VHS Teacher Roster</h2>
            <div class="roster-range">{{ $selectedMonthLabel }} | {{ $monthStartDate->format('d-m-Y') }} to {{ $monthEndDate->format('d-m-Y') }}</div>
        </div>
        <form method="POST" action="{{ url('employee/schedule-roster/vhs') }}" class="roster-toolbar">
            @csrf
            <div>
                <label for="month" class="form-label">Roster Month</label>
                <select name="month" id="month" class="form-select">
                    @foreach(($monthOptions ?? []) as $monthOption)
                        <option value="{{ $monthOption['value'] }}" {{ $selectedMonthValue === $monthOption['value'] ? 'selected' : '' }}>
                            {{ $monthOption['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-roster-primary">
                <i class="fa-solid fa-calendar-plus me-1"></i> Generate
            </button>
            @if($rosterRows->isNotEmpty())
                <a href="{{ $pdfUrl }}" class="btn btn-roster-pdf">
                    <i class="fa-solid fa-file-pdf me-1"></i> PDF
                </a>
            @endif
            <span class="roster-chip">
                <i class="fa-solid fa-lock"></i> Locked
            </span>
        </form>
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

    <section class="roster-search-panel mb-3">
        <form method="GET" action="{{ url('employee/schedule-roster/vhs') }}" class="roster-search-grid">
            <div>
                <label for="search_month" class="form-label">Roster Month</label>
                <select name="month" id="search_month" class="form-select">
                    @foreach(($monthOptions ?? []) as $monthOption)
                        <option value="{{ $monthOption['value'] }}" {{ $selectedMonthValue === $monthOption['value'] ? 'selected' : '' }}>
                            {{ $monthOption['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="branch_id" class="form-label">Branch</label>
                <select name="branch_id" id="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    @foreach($branchOptions as $branch)
                        <option value="{{ $branch['id'] }}" {{ $selectedBranchId === (int) $branch['id'] ? 'selected' : '' }}>
                            {{ $branch['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="employee_id" class="form-label">Employee</label>
                <select name="employee_id" id="employee_id" class="form-select">
                    <option value="">All Teachers</option>
                    @foreach($employeeOptions as $employee)
                        <option value="{{ $employee['id'] }}" {{ $selectedEmployeeId === (int) $employee['id'] ? 'selected' : '' }}>
                            {{ $employee['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-roster-primary w-100">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                </button>
            </div>
            <div>
                @if($rosterRows->isNotEmpty())
                    <a href="{{ $pdfUrl }}" class="btn btn-roster-pdf w-100">
                        <i class="fa-solid fa-file-pdf me-1"></i> PDF
                    </a>
                @endif
            </div>
        </form>
    </section>

    <div class="roster-stat-row mb-3">
        <div class="roster-stat">
            <span class="roster-stat-icon"><i class="fa-solid fa-calendar-check"></i></span>
            <div>
                <div class="roster-stat-label">Roster Rows</div>
                <p class="roster-stat-value">{{ $stats['rows'] }}</p>
            </div>
        </div>
        <div class="roster-stat">
            <span class="roster-stat-icon"><i class="fa-solid fa-user-tie"></i></span>
            <div>
                <div class="roster-stat-label">Teachers</div>
                <p class="roster-stat-value">{{ $stats['employees'] }}</p>
            </div>
        </div>
        <div class="roster-stat">
            <span class="roster-stat-icon"><i class="fa-solid fa-code-branch"></i></span>
            <div>
                <div class="roster-stat-label">Branches</div>
                <p class="roster-stat-value">{{ $stats['branches'] }}</p>
            </div>
        </div>
        <div class="roster-stat">
            <span class="roster-stat-icon"><i class="fa-solid fa-calendar-days"></i></span>
            <div>
                <div class="roster-stat-label">Shift Dates</div>
                <p class="roster-stat-value">{{ $stats['dates'] }}</p>
            </div>
        </div>
    </div>

    @if($rosterRows->isNotEmpty() && !empty($calendarEmployees) && !empty($calendarDates))
        <section class="calendar-board">
            <div class="calendar-board-head">
                <div class="calendar-board-title">{{ $selectedMonthLabel }} Schedule</div>
                <div>
                    <div class="branch-legend">
                        @foreach($branchColorLegend as $legend)
                            <span class="branch-legend-item">
                                <span class="branch-legend-swatch" style="background: {{ $legend['color'] }};"></span>
                                {{ $legend['label'] }}
                            </span>
                        @endforeach
                    </div>
                    <div class="calendar-board-note text-end mt-1">Sundays and 2nd/4th Saturdays shown blank</div>
                </div>
            </div>
            <div class="calendar-scroll">
                <div class="calendar-grid" style="grid-template-columns: 42px 160px {{ $dateColumns }};">
                    <div class="calendar-sl-head">SL</div>
                    <div class="calendar-corner">Teacher</div>
                    @foreach($calendarDates as $date)
                        <div class="calendar-day-head {{ $date['is_today'] ? 'is-today' : '' }} {{ $date['is_saturday'] ? 'is-saturday' : '' }} {{ $date['is_skipped_date'] ? 'is-skipped' : '' }}" title="{{ $date['day_label'] }}, {{ $date['date_label'] }} {{ $date['month_label'] }}">
                            <span class="calendar-day-name">{{ $date['day_initial'] }}</span>
                            <span class="calendar-day-date">{{ $date['date_label'] }}</span>
                        </div>
                    @endforeach

                    @foreach($calendarEmployees as $employeeIndex => $employee)
                        <div class="calendar-sl-cell">{{ $employeeIndex + 1 }}</div>
                        <div class="calendar-person">
                            <div class="calendar-person-copy">
                                <div class="calendar-person-name">{{ $employee['employee_name'] ?: '-' }}</div>
                                <div class="calendar-person-meta">{{ $employee['employee_no'] ?: '-' }}</div>
                            </div>
                            <div class="calendar-person-actions">
                                <form method="POST" action="{{ url('employee/schedule-roster/vhs/delete') }}" onsubmit="return confirm('Delete this teacher roster?');">
                                    @csrf
                                    <input type="hidden" name="delete_month" value="{{ $selectedMonthValue }}">
                                    <input type="hidden" name="employee_id" value="{{ $employee['id'] }}">
                                    <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                                    <button type="submit" class="teacher-delete-btn" title="Delete Teacher Roster">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        @foreach($calendarDates as $date)
                            <?php
                                $cellShifts = $calendarCells[$employee['id']][$date['date']] ?? [];
                            ?>
                            <div class="calendar-cell {{ $date['is_skipped_date'] ? 'is-skipped' : '' }}">
                                @if($date['is_skipped_date'])
                                    <div class="calendar-blank-cell" title="No roster"></div>
                                @elseif(!empty($cellShifts))
                                    <div class="shift-stack">
                                        @foreach($cellShifts as $shift)
                                            <div class="shift-card {{ $shift['shift_class'] }}" title="{{ $shift['branch_name'] ?: '-' }} | {{ $shift['time_display'] ?: '-' }}">
                                                <span class="shift-role">VHS Teacher</span>
                                                <span class="shift-branch">{{ $shift['branch_code'] ?: '-' }}</span>
                                                <span class="shift-time">{{ $shift['time_display'] ?: '-' }}</span>
                                                @if(!empty($shift['can_edit_vhs_time']))
                                                    <div class="shift-action-row">
                                                        <button type="button"
                                                                class="shift-icon-btn"
                                                                title="Edit Saturday Time"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#vhsTimeEditModal"
                                                                data-roster-id="{{ $shift['roster_id'] }}"
                                                                data-teacher="{{ $employee['employee_no'] }} - {{ $employee['employee_name'] }}"
                                                                data-date="{{ $shift['roster_date_label'] }}"
                                                                data-branch="{{ $shift['branch_name'] ?: '-' }}"
                                                                data-in-time="{{ $shift['in_time'] }}"
                                                                data-out-time="{{ $shift['out_time'] }}">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="calendar-empty-cell">N/A</div>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </section>
    @else
        <div class="roster-empty">
            <i class="fa-solid fa-calendar-plus fa-2x mb-3"></i>
            <div>No VHS teacher roster generated for {{ $selectedMonthLabel }}.</div>
        </div>
    @endif
</div>

<div class="modal fade" id="vhsTimeEditModal" tabindex="-1" aria-labelledby="vhsTimeEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ url('employee/schedule-roster/vhs/update-time') }}" class="modal-content">
            @csrf
            <input type="hidden" name="roster_id" id="vhs_time_roster_id">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="vhsTimeEditModalLabel">Edit Saturday Time</h5>
                    <div class="text-muted small fw-bold" id="vhsTimeEditMeta"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label for="vhs_time_in" class="form-label">From Time</label>
                        <input type="time" name="in_time" id="vhs_time_in" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label for="vhs_time_out" class="form-label">To Time</label>
                        <input type="time" name="out_time" id="vhs_time_out" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-roster-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var timeEditModal = document.getElementById('vhsTimeEditModal');
        if (!timeEditModal) {
            return;
        }

        timeEditModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) {
                return;
            }

            document.getElementById('vhs_time_roster_id').value = button.getAttribute('data-roster-id') || '';
            document.getElementById('vhs_time_in').value = (button.getAttribute('data-in-time') || '').substring(0, 5);
            document.getElementById('vhs_time_out').value = (button.getAttribute('data-out-time') || '').substring(0, 5);
            document.getElementById('vhsTimeEditMeta').textContent = [
                button.getAttribute('data-teacher') || '',
                button.getAttribute('data-date') || '',
                button.getAttribute('data-branch') || ''
            ].filter(Boolean).join(' | ');
        });
    });
</script>
@endsection
