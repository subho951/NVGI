@extends('front.layouts.afterlogin')

@section('styles')
<style>
    .support-roster {
        --ink: #172033;
        --muted: #64748b;
        --border: #d9dee7;
        --soft: #f8fafc;
        --brand: #111827;
        color: var(--ink);
    }

    .support-topbar,
    .support-panel,
    .support-calendar {
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #fff;
        box-shadow: 0 10px 24px rgba(23, 32, 51, 0.06);
    }

    .support-calendar {
        --roster-sticky-top: 57px;
        position: relative;
    }

    .support-topbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
    }

    .support-kicker,
    .support-label,
    .stat-label {
        color: var(--muted);
        font-size: 0.68rem;
        font-weight: 850;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .support-title {
        margin: 2px 0 0;
        font-size: 1.35rem;
        font-weight: 850;
        letter-spacing: 0;
    }

    .support-subtitle {
        margin-top: 3px;
        color: var(--muted);
        font-size: 0.84rem;
        font-weight: 700;
    }

    .branch-legend {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px 12px;
        margin-top: 8px;
    }

    .branch-legend-item {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: var(--muted);
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

    .support-panel {
        padding: 10px;
    }

    .panel-title {
        margin: 0 0 8px;
        font-size: 0.9rem;
        font-weight: 850;
    }

    .support-filter-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        align-items: end;
    }

    .support-entry-grid {
        display: grid;
        grid-template-columns: 2fr 1.2fr 1fr 1fr auto;
        gap: 10px;
        align-items: end;
    }

    .support-copy-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
        gap: 10px;
        align-items: end;
    }

    .additional-class-grid {
        display: grid;
        grid-template-columns: 1.7fr 1fr 1fr 0.85fr 0.85fr auto;
        gap: 10px;
        align-items: end;
    }

    .support-roster .form-label {
        margin-bottom: 4px;
        color: var(--muted);
        font-size: 0.68rem;
        font-weight: 850;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .support-roster .form-control,
    .support-roster .form-select,
    .support-roster .btn {
        min-height: 38px;
        border-radius: 7px;
        font-size: 0.84rem;
    }

    .btn-dark-roster {
        border: 0;
        background: #111827;
        color: #fff;
        font-weight: 800;
    }

    .btn-dark-roster:hover {
        background: #0f172a;
        color: #fff;
    }

    .btn-pdf-roster {
        border: 1px solid #fecaca;
        background: #fff1f2;
        color: #b91c1c;
        font-weight: 800;
    }

    .btn-pdf-roster:hover {
        background: #ffe4e6;
        color: #991b1b;
    }

    .support-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .support-stat {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 70px;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #fff;
    }

    .stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #edf5ff;
        color: #0f62fe;
    }

    .stat-value {
        margin: 1px 0 0;
        font-size: 1.08rem;
        font-weight: 850;
        line-height: 1;
    }

    .date-entry-tools {
        display: flex;
        flex-wrap: wrap;
        align-items: end;
        gap: 6px;
        padding: 6px 8px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: var(--soft);
    }

    .date-entry-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 5px;
        margin-top: 6px;
    }

    .date-entry-cell {
        min-height: 84px;
        padding: 5px;
        border: 1px solid #dbe2ea;
        border-radius: 6px;
        background: #fff;
    }

    .date-entry-cell.is-skipped {
        background: #fff1f1;
        border-color: #fecaca;
    }

    .date-entry-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 4px;
        margin-bottom: 4px;
        font-size: 0.64rem;
        font-weight: 850;
    }

    .date-entry-day {
        color: var(--muted);
        font-size: 0.58rem;
        font-weight: 800;
    }

    .date-entry-meta {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex: 0 0 auto;
    }

    .date-reset-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 19px;
        height: 19px;
        padding: 0;
        border: 0;
        border-radius: 4px;
        background: #eef2f7;
        color: #64748b;
        font-size: 0.56rem;
        line-height: 1;
    }

    .date-reset-btn:hover {
        background: #fee2e2;
        color: #991b1b;
    }

    .off-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        width: 100%;
        border-radius: 5px;
        background: #fee2e2;
        color: #991b1b;
        font-size: 0.62rem;
        font-weight: 850;
    }

    .date-entry-cell .mb-2 {
        margin-bottom: 4px !important;
    }

    .date-entry-cell .form-label {
        margin-bottom: 2px;
        font-size: 0.56rem;
        line-height: 1;
    }

    .date-entry-cell .form-control {
        min-height: 28px;
        padding: 2px 6px;
        border-radius: 5px;
        font-size: 0.74rem;
    }

    .date-time-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 5px;
    }

    .calendar-board-head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 11px 12px;
        border-bottom: 1px solid var(--border);
        background: var(--soft);
    }

    .calendar-board-title {
        font-size: 0.96rem;
        font-weight: 850;
    }

    .calendar-board-meta {
        color: var(--muted);
        font-size: 0.76rem;
        font-weight: 800;
    }

    .calendar-scroll {
        overflow-x: auto;
        width: 100%;
    }

    .calendar-table-head {
        position: sticky;
        top: var(--roster-sticky-top);
        z-index: 20;
        background: #f3f5f8;
        box-shadow: 0 5px 10px rgba(23, 32, 51, 0.08);
    }

    .calendar-head-scroll {
        overflow: hidden;
        width: 100%;
    }

    .calendar-grid {
        display: grid;
        min-width: 1160px;
    }

    .calendar-sl-head,
    .calendar-sl-cell,
    .calendar-person-head,
    .calendar-day-head,
    .calendar-person,
    .calendar-cell {
        border-right: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
    }

    .calendar-sl-head,
    .calendar-person-head,
    .calendar-day-head {
        min-height: 42px;
        background: #f3f5f8;
    }

    .calendar-sl-head,
    .calendar-person-head {
        position: sticky;
        z-index: 5;
        display: flex;
        align-items: center;
        padding: 8px;
        color: var(--muted);
        font-size: 0.66rem;
        font-weight: 850;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .calendar-sl-head {
        left: 0;
        justify-content: center;
    }

    .calendar-person-head {
        left: 42px;
        justify-content: flex-start;
    }

    .calendar-day-head {
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 2px;
        padding: 4px 1px;
        text-align: center;
    }

    .calendar-day-head.is-skipped {
        background: #ffe8e8;
        color: #a13b3b;
    }

    .calendar-day-head.is-skipped .day-name {
        display: none;
    }

    .calendar-repeat-head-cell {
        border-top: 2px solid #2563eb;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
    }

    .day-name {
        font-size: 0.56rem;
        font-weight: 850;
    }

    .day-date {
        font-size: 0.6rem;
        font-weight: 750;
    }

    .calendar-sl-cell,
    .calendar-person,
    .calendar-cell {
        min-height: 74px;
        background: #fff;
    }

    .calendar-sl-cell,
    .calendar-person {
        position: sticky;
        z-index: 4;
        display: flex;
        align-items: center;
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
        justify-content: space-between;
        gap: 7px;
        padding: 7px 8px;
        text-align: left;
    }

    .calendar-person-copy {
        min-width: 0;
    }

    .person-name {
        color: var(--ink);
        font-size: 0.6rem;
        font-weight: 850;
        line-height: 1.12;
        word-break: break-word;
    }

    .person-meta {
        margin-top: 2px;
        color: var(--muted);
        font-size: 0.56rem;
        font-weight: 700;
    }

    .person-actions {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 0 0 auto;
    }

    .person-actions .btn {
        width: 28px;
        min-height: 24px;
        padding: 2px;
        border-radius: 6px;
        font-size: 0.68rem;
    }

    .calendar-cell {
        padding: 3px;
    }

    .calendar-cell.is-skipped {
        padding: 0;
        background: #fff1f1;
    }

    .calendar-cell-tools {
        display: flex;
        justify-content: flex-end;
        min-height: 19px;
        margin-bottom: 2px;
    }

    .cell-shift-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 19px;
        height: 19px;
        padding: 0;
        border: 1px solid #dbe4ee;
        border-radius: 4px;
        background: #fff;
        color: #475569;
        font-size: 0.55rem;
        line-height: 1;
    }

    .cell-shift-btn:hover {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .blank-cell {
        min-height: 51px;
    }

    .shift-card {
        min-height: 42px;
        padding: 4px 2px;
        border-radius: 4px;
        background: #96e4d7;
        color: #101828;
        text-align: center;
        line-height: 1.1;
        overflow: hidden;
    }

    .shift-card.blue { background: #7cc7f2; }
    .shift-card.mint { background: #96e4d7; }
    .shift-card.pink { background: #f5a0cf; }
    .shift-card.amber { background: #ffed9d; }
    .shift-card.violet { background: #c7b7ff; }
    .shift-card.yellow { background: #ffed9d; }
    .shift-card.light-green { background: #b7f3c8; }
    .shift-card.neutral { background: #dbe4ee; }

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
        line-height: 1.08;
        white-space: normal;
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
        color: #0f172a;
        font-size: 0.55rem;
        line-height: 1;
    }

    .shift-icon-btn.edit {
        color: #14532d;
    }

    .shift-icon-btn.delete {
        color: #991b1b;
    }

    .shift-icon-btn.locked {
        color: #64748b;
        cursor: default;
    }

    .shift-action-row form {
        margin: 0;
        line-height: 1;
    }

    .empty-roster {
        padding: 34px 16px;
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #fff;
        color: var(--muted);
        text-align: center;
        font-weight: 750;
    }

    @media (max-width: 991px) {
        .support-filter-grid,
        .support-entry-grid,
        .support-copy-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .additional-class-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .support-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .date-entry-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 575px) {
        .support-filter-grid,
        .support-entry-grid,
        .support-copy-grid,
        .additional-class-grid,
        .support-stats,
        .date-entry-grid {
            grid-template-columns: 1fr;
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
$calendarGroups = $calendarGroups ?? [];
$branchColorLegend = $branchColorLegend ?? [];
$entryCalendarDates = $entryCalendarDates ?? [];
$rosterTitle = $rosterTitle ?? 'Front Desk & Group D Roster';
$rosterRoute = $rosterRoute ?? 'employee/schedule-roster/front-desk-group-d';
$attendanceRoute = $attendanceRoute ?? ($rosterRoute . '/mark-attendance');
$pdfRoute = $pdfRoute ?? ($rosterRoute . '/pdf');
$copyRoute = $copyRoute ?? ($rosterRoute . '/copy');
$deleteRoute = $deleteRoute ?? ($rosterRoute . '/delete');
$shiftDateRoute = $shiftDateRoute ?? '';
$additionalClassRoute = $additionalClassRoute ?? '';
$rescheduleRoute = $rescheduleRoute ?? '';
$dateDeleteRoute = $dateDeleteRoute ?? '';
$allowAdditionalClass = !empty($allowAdditionalClass);
$allowTimeEdit = !empty($allowTimeEdit);
$allowReschedule = !empty($allowReschedule);
$allowDateDelete = !empty($allowDateDelete);
$allowWholeEmployeeDelete = $allowWholeEmployeeDelete ?? true;
$additionalClassEmployees = $additionalClassEmployees ?? [];
$additionalClassDates = collect($additionalClassDates ?? [])->where('is_roster_working_date', true)->values()->all();
$dateColumns = collect($calendarDates)->map(function ($date) use ($allowTimeEdit) {
    return !empty($date['is_skipped_date']) ? '16px' : ($allowTimeEdit ? 'minmax(54px, 1fr)' : 'minmax(42px, 1fr)');
})->implode(' ');
$entryWorkingDates = collect($entryCalendarDates)->where('is_roster_working_date', true)->count();
?>

<div class="support-roster">
    <div class="support-topbar mb-3">
        <div>
            <div class="support-kicker">Schedule Roster</div>
            <h2 class="support-title">{{ $rosterTitle }}</h2>
            <div class="support-subtitle">{{ $selectedMonthLabel }} | {{ $monthStartDate->format('d-m-Y') }} to {{ $monthEndDate->format('d-m-Y') }}</div>
            <div class="branch-legend">
                @foreach($branchColorLegend as $legend)
                    <span class="branch-legend-item">
                        <span class="branch-legend-swatch" style="background: {{ $legend['color'] }};"></span>
                        {{ $legend['label'] }}
                    </span>
                @endforeach
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @if($rosterRows->isNotEmpty())
                <a href="{{ $pdfUrl }}" class="btn btn-pdf-roster">
                    <i class="fa-solid fa-file-pdf me-1"></i> All PDF
                </a>
            @endif
            <span class="btn btn-light border fw-bold">
                @if($allowTimeEdit)
                    <i class="fa-solid fa-pen me-1"></i> All Dates Editable
                @else
                    <i class="fa-solid fa-lock me-1"></i> Locked
                @endif
            </span>
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

    <section class="support-panel mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div>
                <h3 class="panel-title">Mark All Present</h3>
                <div class="text-muted fw-semibold">
                    Store scheduled punch-in and punch-out for every employee. Missing rosters use each employee's latest previous timeslots.
                </div>
            </div>
            <form method="POST"
                  action="{{ url($attendanceRoute) }}"
                  class="d-flex flex-wrap align-items-end gap-2"
                  onsubmit="return confirm('Mark every employee in this roster present for the selected date? Existing real punch times will be kept.');">
                @csrf
                <div>
                    <label class="form-label" for="roster_attendance_date">Attendance Date</label>
                    <input type="date"
                           name="attendance_date"
                           id="roster_attendance_date"
                           value="{{ old('attendance_date', \Carbon\Carbon::today('Asia/Kolkata')->toDateString()) }}"
                           max="{{ \Carbon\Carbon::today('Asia/Kolkata')->toDateString() }}"
                           class="form-control"
                           required>
                </div>
                <button type="submit" class="btn btn-dark-roster">
                    <i class="fa-solid fa-user-check me-1"></i> Submit
                </button>
            </form>
        </div>
    </section>

    <section class="support-panel mb-3">
        <h3 class="panel-title">Create Roster</h3>
        <form method="GET" action="{{ url($rosterRoute) }}" class="support-filter-grid mb-3">
            <div>
                <label class="form-label" for="entry_category">Category</label>
                <select name="entry_category" id="entry_category" class="form-select">
                    @foreach($supportCategories as $category)
                        <option value="{{ $category }}" {{ $entryCategory === $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="entry_month">Month & Year</label>
                <select name="entry_month" id="entry_month" class="form-select">
                    @foreach($monthOptions as $monthOption)
                        <option value="{{ $monthOption['value'] }}" {{ $entryMonthValue === $monthOption['value'] ? 'selected' : '' }}>{{ $monthOption['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-dark-roster w-100">
                    <i class="fa-solid fa-rotate me-1"></i> Load
                </button>
            </div>
        </form>

        <form method="POST" action="{{ url($rosterRoute) }}">
            @csrf
            <input type="hidden" name="category" value="{{ $entryCategory }}">
            <input type="hidden" name="month" value="{{ $entryMonthValue }}">

            <div class="support-entry-grid mb-3">
                <div>
                    <label class="form-label" for="employee_id">Employee</label>
                    <select name="employee_id" id="employee_id" class="form-select" required>
                        <option value="">Select Employee</option>
                        @foreach($entryEmployees as $employee)
                            <option value="{{ $employee['id'] }}" {{ (string) old('employee_id') === (string) $employee['id'] ? 'selected' : '' }}>
                                {{ $employee['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="branch_name">Branch</label>
                    <select name="branch_name" id="branch_name" class="form-select" required>
                        @foreach($branchOptions as $branch)
                            <option value="{{ $branch['name'] }}" {{ old('branch_name', $entryBranchName) === $branch['name'] ? 'selected' : '' }}>{{ $branch['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="bulk_in_time">From Time</label>
                    <input type="time" id="bulk_in_time" class="form-control">
                </div>
                <div>
                    <label class="form-label" for="bulk_out_time">To Time</label>
                    <input type="time" id="bulk_out_time" class="form-control">
                </div>
                <div>
                    <button type="button" class="btn btn-light border fw-bold w-100" id="fillWorkingDates">
                        <i class="fa-solid fa-copy me-1"></i> Fill
                    </button>
                </div>
            </div>

            @if(empty($entryEmployees))
                <div class="alert alert-warning mb-3">No {{ $entryCategory }} employees are available for {{ $entryMonthLabel }}.</div>
            @endif

            <div class="date-entry-tools mb-2">
                <span class="support-label">{{ $entryMonthLabel }}</span>
                <span class="text-muted fw-bold">{{ $entryWorkingDates }} working dates</span>
            </div>

            <div class="date-entry-grid">
                @foreach($entryCalendarDates as $date)
                    <?php
                        $dateKey = $date['date'];
                        $oldInTime = old('times.' . $dateKey . '.in_time');
                        $oldOutTime = old('times.' . $dateKey . '.out_time');
                    ?>
                    <div class="date-entry-cell {{ $date['is_skipped_date'] ? 'is-skipped' : '' }}">
                        <div class="date-entry-head">
                            <span>{{ $date['date_label'] }} {{ $date['month_label'] }}</span>
                            <span class="date-entry-meta">
                                <span class="date-entry-day">{{ $date['short_day_label'] }}</span>
                                @if(!$date['is_skipped_date'])
                                    <button type="button" class="date-reset-btn reset-date-entry" data-date-key="{{ $dateKey }}" title="Reset Date" aria-label="Reset {{ $date['date_label'] }} {{ $date['month_label'] }}">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                @endif
                            </span>
                        </div>
                        @if($date['is_skipped_date'])
                            <span class="off-pill">Blank</span>
                        @else
                            <div class="date-time-row">
                                <div>
                                    <label class="form-label" for="in_{{ $dateKey }}">From</label>
                                    <input type="time" id="in_{{ $dateKey }}" name="times[{{ $dateKey }}][in_time]" value="{{ $oldInTime }}" class="form-control working-in-time" data-date-key="{{ $dateKey }}">
                                </div>
                                <div>
                                    <label class="form-label" for="out_{{ $dateKey }}">To</label>
                                    <input type="time" id="out_{{ $dateKey }}" name="times[{{ $dateKey }}][out_time]" value="{{ $oldOutTime }}" class="form-control working-out-time" data-date-key="{{ $dateKey }}">
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-3 text-end">
                <button type="submit" class="btn btn-dark-roster" {{ empty($entryEmployees) ? 'disabled' : '' }}>
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Roster
                </button>
            </div>
        </form>
    </section>

    @if($allowAdditionalClass && !empty($additionalClassRoute))
        <section class="support-panel mb-3">
            <h3 class="panel-title">Additional Class</h3>
            <form method="POST" action="{{ url($additionalClassRoute) }}" class="additional-class-grid">
                @csrf
                <input type="hidden" name="month" value="{{ $selectedMonthValue }}">
                <div>
                    <label class="form-label" for="additional_employee_id">Teacher</label>
                    <select name="employee_id" id="additional_employee_id" class="form-select" required>
                        <option value="">Select Teacher</option>
                        @foreach($additionalClassEmployees as $employee)
                            <option value="{{ $employee['id'] }}" {{ (string) old('employee_id') === (string) $employee['id'] ? 'selected' : '' }}>
                                {{ $employee['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="additional_branch_name">Branch</label>
                    <select name="branch_name" id="additional_branch_name" class="form-select" required>
                        @foreach($branchOptions as $branch)
                            <option value="{{ $branch['name'] }}" {{ old('branch_name') === $branch['name'] ? 'selected' : '' }}>{{ $branch['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="additional_roster_date">Date</label>
                    <select name="roster_date" id="additional_roster_date" class="form-select" required>
                        <option value="">Select Date</option>
                        @foreach($additionalClassDates as $date)
                            <option value="{{ $date['date'] }}" {{ old('roster_date') === $date['date'] ? 'selected' : '' }}>
                                {{ $date['date_label'] }} {{ $date['month_label'] }} - {{ $date['short_day_label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="additional_in_time">From Time</label>
                    <input type="time" name="in_time" id="additional_in_time" value="{{ old('in_time') }}" class="form-control" required>
                </div>
                <div>
                    <label class="form-label" for="additional_out_time">To Time</label>
                    <input type="time" name="out_time" id="additional_out_time" value="{{ old('out_time') }}" class="form-control" required>
                </div>
                <div>
                    <button type="submit" class="btn btn-dark-roster w-100" {{ empty($additionalClassEmployees) || empty($additionalClassDates) ? 'disabled' : '' }}>
                        <i class="fa-solid fa-plus me-1"></i> Add
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section class="support-panel mb-3">
        <h3 class="panel-title">Search Roster</h3>
        <form method="GET" action="{{ url($rosterRoute) }}" class="support-filter-grid">
            <input type="hidden" name="entry_category" value="{{ $entryCategory }}">
            <input type="hidden" name="entry_month" value="{{ $entryMonthValue }}">
            <div>
                <label class="form-label" for="month">Month & Year</label>
                <select name="month" id="month" class="form-select">
                    @foreach($monthOptions as $monthOption)
                        <option value="{{ $monthOption['value'] }}" {{ $selectedMonthValue === $monthOption['value'] ? 'selected' : '' }}>{{ $monthOption['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="category">Category</label>
                <select name="category" id="category" class="form-select">
                    <option value="">All</option>
                    @foreach($supportCategories as $category)
                        <option value="{{ $category }}" {{ $selectedCategory === $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="branch_name_filter">Branch</label>
                <select name="branch_name" id="branch_name_filter" class="form-select">
                    <option value="">All</option>
                    @foreach($branchOptions as $branch)
                        <option value="{{ $branch['name'] }}" {{ $selectedBranchName === $branch['name'] ? 'selected' : '' }}>{{ $branch['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="employee_id_filter">Employee</label>
                <select name="employee_id" id="employee_id_filter" class="form-select">
                    <option value="">All</option>
                    @foreach($searchEmployees as $employee)
                        <option value="{{ $employee['id'] }}" {{ (int) $selectedEmployeeId === (int) $employee['id'] ? 'selected' : '' }}>{{ $employee['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-dark-roster w-100">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                </button>
            </div>
        </form>
    </section>

    <section class="support-panel mb-3">
        <h3 class="panel-title">Copy Roster</h3>
        <form method="POST" action="{{ url($copyRoute) }}" class="support-copy-grid" onsubmit="return confirm('Copy roster to selected target month? Existing target rows will be kept.');">
            @csrf
            <div>
                <label class="form-label" for="copy_source_month">From Month</label>
                <select name="copy_source_month" id="copy_source_month" class="form-select">
                    @foreach($monthOptions as $monthOption)
                        <option value="{{ $monthOption['value'] }}" {{ $copySourceMonthValue === $monthOption['value'] ? 'selected' : '' }}>{{ $monthOption['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="copy_target_month">To Month</label>
                <select name="copy_target_month" id="copy_target_month" class="form-select">
                    @foreach($monthOptions as $monthOption)
                        <option value="{{ $monthOption['value'] }}" {{ $copyTargetMonthValue === $monthOption['value'] ? 'selected' : '' }}>{{ $monthOption['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="copy_category">Category</label>
                <select name="copy_category" id="copy_category" class="form-select">
                    <option value="">All</option>
                    @foreach($supportCategories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="copy_employee_id">Employee</label>
                <select name="copy_employee_id" id="copy_employee_id" class="form-select">
                    <option value="">All</option>
                    @foreach($copyEmployees as $employee)
                        <option value="{{ $employee['id'] }}">{{ $employee['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-dark-roster w-100">
                    <i class="fa-solid fa-clone me-1"></i> Copy
                </button>
            </div>
        </form>
    </section>

    <div class="support-stats mb-3">
        <div class="support-stat">
            <span class="stat-icon"><i class="fa-solid fa-calendar-check"></i></span>
            <div>
                <div class="stat-label">Rows</div>
                <p class="stat-value">{{ $stats['rows'] }}</p>
            </div>
        </div>
        <div class="support-stat">
            <span class="stat-icon"><i class="fa-solid fa-users"></i></span>
            <div>
                <div class="stat-label">Employees</div>
                <p class="stat-value">{{ $stats['employees'] }}</p>
            </div>
        </div>
        <div class="support-stat">
            <span class="stat-icon"><i class="fa-solid fa-code-branch"></i></span>
            <div>
                <div class="stat-label">Branches</div>
                <p class="stat-value">{{ $stats['branches'] }}</p>
            </div>
        </div>
        <div class="support-stat">
            <span class="stat-icon"><i class="fa-solid fa-calendar-days"></i></span>
            <div>
                <div class="stat-label">Dates</div>
                <p class="stat-value">{{ $stats['dates'] }}</p>
            </div>
        </div>
    </div>

    @if($rosterRows->isNotEmpty() && !empty($calendarGroups))
        @foreach($calendarGroups as $group)
            <?php
                $groupBranchName = (string) ($group['branch_name'] ?? '');
                $groupPdfQuery = [
                    'month' => $selectedMonthValue,
                    'category' => $group['category'],
                ];
                if ($groupBranchName !== '' && $groupBranchName !== 'All Branches') {
                    $groupPdfQuery['branch_name'] = $groupBranchName;
                }
                $employeeCount = count($group['employees']);
                $repeatHeaderIndex = $employeeCount > 2 ? $employeeCount - 2 : null;
            ?>
            <section class="support-calendar mb-3">
                <div class="calendar-board-head">
                    <div>
                        <div class="calendar-board-title">{{ $group['branch_name'] }} | {{ $group['category'] }}</div>
                        <div class="calendar-board-meta">{{ $group['employees_count'] }} employees | {{ $group['rows'] }} rows</div>
                    </div>
                    <a href="{{ url($pdfRoute) . '?' . http_build_query($groupPdfQuery) }}" class="btn btn-pdf-roster btn-sm">
                        <i class="fa-solid fa-file-pdf me-1"></i> PDF
                    </a>
                </div>
                <div class="calendar-table-head">
                    <div class="calendar-head-scroll">
                        <div class="calendar-grid" style="grid-template-columns: 42px 210px {{ $dateColumns }};">
                            <div class="calendar-sl-head">SL</div>
                            <div class="calendar-person-head">Employee</div>
                            @foreach($calendarDates as $date)
                                <div class="calendar-day-head {{ $date['is_skipped_date'] ? 'is-skipped' : '' }}" title="{{ $date['day_label'] }}, {{ $date['date_label'] }} {{ $date['month_label'] }}">
                                    <span class="day-name">{{ $date['day_initial'] }}</span>
                                    <span class="day-date">{{ $date['date_label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="calendar-scroll">
                    <div class="calendar-grid" style="grid-template-columns: 42px 210px {{ $dateColumns }};">
                        @foreach($group['employees'] as $employeeIndex => $employee)
                            @if($repeatHeaderIndex !== null && $employeeIndex === $repeatHeaderIndex)
                                <div class="calendar-sl-head calendar-repeat-head-cell">SL</div>
                                <div class="calendar-person-head calendar-repeat-head-cell">Employee</div>
                                @foreach($calendarDates as $date)
                                    <div class="calendar-day-head calendar-repeat-head-cell {{ $date['is_skipped_date'] ? 'is-skipped' : '' }}" title="{{ $date['day_label'] }}, {{ $date['date_label'] }} {{ $date['month_label'] }}">
                                        <span class="day-name">{{ $date['day_initial'] }}</span>
                                        <span class="day-date">{{ $date['date_label'] }}</span>
                                    </div>
                                @endforeach
                            @endif

                            <div class="calendar-sl-cell">{{ $employeeIndex + 1 }}</div>
                            <div class="calendar-person">
                                <div class="calendar-person-copy">
                                    <div class="person-name">{{ $employee['employee_name'] ?: '-' }}</div>
                                    <div class="person-meta">{{ $employee['employee_no'] ?: '-' }}</div>
                                </div>
                                <div class="person-actions">
                                    <?php $employeePdfQuery = array_merge($groupPdfQuery, ['employee_id' => $employee['id']]); ?>
                                    <a href="{{ url($pdfRoute) . '?' . http_build_query($employeePdfQuery) }}" class="btn btn-outline-danger" title="PDF">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>
                                    @if($allowWholeEmployeeDelete)
                                        <form method="POST" action="{{ url($deleteRoute) }}" onsubmit="return confirm('Delete this employee roster?');">
                                            @csrf
                                            <input type="hidden" name="delete_month" value="{{ $selectedMonthValue }}">
                                            <input type="hidden" name="category" value="{{ $group['category'] }}">
                                            <input type="hidden" name="branch_name" value="{{ $group['branch_name'] }}">
                                            <input type="hidden" name="employee_id" value="{{ $employee['id'] }}">
                                            <button type="submit" class="btn btn-outline-secondary" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            @foreach($calendarDates as $date)
                                <?php $cellShifts = $group['cells'][$employee['id']][$date['date']] ?? []; ?>
                                <div class="calendar-cell {{ $date['is_skipped_date'] ? 'is-skipped' : '' }}">
                                    @if($date['is_skipped_date'])
                                        <div class="blank-cell"></div>
                                    @else
                                        @if(!empty($shiftDateRoute))
                                            <div class="calendar-cell-tools">
                                                <button type="button"
                                                        class="cell-shift-btn"
                                                        title="Shift Duty/Weekoff"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#shiftRosterDateModal"
                                                        data-employee-id="{{ $employee['id'] }}"
                                                        data-employee="{{ $employee['employee_no'] }} - {{ $employee['employee_name'] }}"
                                                        data-category="{{ $group['category'] }}"
                                                        data-branch-name="{{ $groupBranchName !== 'All Branches' ? $groupBranchName : '' }}"
                                                        data-source-date="{{ $date['date'] }}"
                                                        data-source-label="{{ $date['date_label'] }} {{ $date['month_label'] }}"
                                                        data-in-time="{{ $cellShifts[0]['in_time'] ?? '' }}"
                                                        data-out-time="{{ $cellShifts[0]['out_time'] ?? '' }}"
                                                        data-month-start="{{ $monthStartDate->toDateString() }}"
                                                        data-month-end="{{ $monthEndDate->toDateString() }}">
                                                    <i class="fa-solid fa-right-left"></i>
                                                </button>
                                            </div>
                                        @endif

                                        @if(!empty($cellShifts))
                                            @foreach($cellShifts as $shift)
                                                <div class="shift-card {{ $shift['shift_class'] }}" title="{{ $shift['branch_name'] ?: '-' }} | {{ $shift['time_display'] ?: '-' }}">
                                                    <span class="shift-branch">{{ $shift['branch_code'] ?: '-' }}</span>
                                                    <span class="shift-time">{{ $shift['time_display'] ?: '-' }}</span>
                                                    @if($allowTimeEdit || $allowDateDelete)
                                                        <div class="shift-action-row">
                                                            @if($allowTimeEdit && !empty($rescheduleRoute))
                                                                <button type="button"
                                                                        class="shift-icon-btn edit"
                                                                        title="Edit Time"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#rescheduleRosterModal"
                                                                        data-roster-id="{{ $shift['roster_id'] }}"
                                                                        data-employee="{{ $employee['employee_no'] }} - {{ $employee['employee_name'] }}"
                                                                        data-date="{{ $shift['roster_date_label'] }}"
                                                                        data-in-time="{{ $shift['in_time'] }}"
                                                                        data-out-time="{{ $shift['out_time'] }}">
                                                                    <i class="fa-solid fa-pen"></i>
                                                                </button>
                                                            @endif

                                                            @if($allowDateDelete && !empty($dateDeleteRoute))
                                                                <form method="POST" action="{{ url($dateDeleteRoute) }}" onsubmit="return confirm('Delete this date roster?');">
                                                                    @csrf
                                                                    <input type="hidden" name="roster_id" value="{{ $shift['roster_id'] }}">
                                                                    <button type="submit" class="shift-icon-btn delete" title="Delete Date">
                                                                        <i class="fa-solid fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            </section>
        @endforeach
    @else
        <div class="empty-roster">
            <i class="fa-solid fa-calendar-plus fa-2x mb-3"></i>
            <div>No {{ $rosterTitle }} found for {{ $selectedMonthLabel }}.</div>
        </div>
    @endif
</div>

@if($allowTimeEdit && !empty($rescheduleRoute))
    <div class="modal fade" id="rescheduleRosterModal" tabindex="-1" aria-labelledby="rescheduleRosterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ url($rescheduleRoute) }}" class="modal-content">
                @csrf
                <input type="hidden" name="roster_id" id="reschedule_roster_id">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="rescheduleRosterModalLabel">Edit Roster Time</h5>
                        <div class="text-muted small fw-bold" id="rescheduleRosterMeta"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label for="reschedule_in_time" class="form-label">From Time</label>
                            <input type="time" name="in_time" id="reschedule_in_time" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label for="reschedule_out_time" class="form-label">To Time</label>
                            <input type="time" name="out_time" id="reschedule_out_time" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark-roster">Save</button>
                </div>
            </form>
        </div>
    </div>
@endif

@if(!empty($shiftDateRoute))
    <div class="modal fade" id="shiftRosterDateModal" tabindex="-1" aria-labelledby="shiftRosterDateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ url($shiftDateRoute) }}" class="modal-content">
                @csrf
                <input type="hidden" name="employee_id" id="shift_employee_id">
                <input type="hidden" name="category" id="shift_category">
                <input type="hidden" name="branch_name" id="shift_branch_name">
                <input type="hidden" name="source_date" id="shift_source_date">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="shiftRosterDateModalLabel">Shift Duty/Weekoff</h5>
                        <div class="text-muted small fw-bold" id="shiftRosterMeta"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="shift_target_date" class="form-label">Shift With Date</label>
                            <input type="date" name="target_date" id="shift_target_date" class="form-control" required>
                        </div>
                        @if($allowReschedule)
                            <div class="col-6">
                                <label for="shift_in_time" class="form-label">From Time</label>
                                <input type="time" name="in_time" id="shift_in_time" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label for="shift_out_time" class="form-label">To Time</label>
                                <input type="time" name="out_time" id="shift_out_time" class="form-control" required>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark-roster">Shift</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var topbar = document.querySelector('.topbar');
        var syncRosterStickyOffset = function () {
            var stickyTop = topbar ? Math.ceil(topbar.getBoundingClientRect().height) : 0;

            document.querySelectorAll('.support-calendar').forEach(function (calendar) {
                calendar.style.setProperty('--roster-sticky-top', stickyTop + 'px');
            });
        };

        document.querySelectorAll('.support-calendar').forEach(function (calendar) {
            var calendarScroll = calendar.querySelector('.calendar-scroll');
            var headerScroll = calendar.querySelector('.calendar-head-scroll');

            if (!calendarScroll || !headerScroll) {
                return;
            }

            var syncCalendarHeader = function () {
                headerScroll.scrollLeft = calendarScroll.scrollLeft;
            };

            syncCalendarHeader();
            calendarScroll.addEventListener('scroll', syncCalendarHeader, { passive: true });
        });

        syncRosterStickyOffset();
        window.addEventListener('resize', syncRosterStickyOffset);

        if (topbar && typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(syncRosterStickyOffset).observe(topbar);
        }

        var fillButton = document.getElementById('fillWorkingDates');
        if (fillButton) {
            fillButton.addEventListener('click', function () {
                var inTime = document.getElementById('bulk_in_time').value;
                var outTime = document.getElementById('bulk_out_time').value;

                if (!inTime || !outTime) {
                    return;
                }

                document.querySelectorAll('.working-in-time').forEach(function (input) {
                    input.value = inTime;
                });

                document.querySelectorAll('.working-out-time').forEach(function (input) {
                    input.value = outTime;
                });
            });
        }

        document.querySelectorAll('.reset-date-entry').forEach(function (button) {
            button.addEventListener('click', function () {
                var dateKey = button.getAttribute('data-date-key') || '';
                if (!dateKey) {
                    return;
                }

                document.querySelectorAll('[data-date-key="' + dateKey + '"].form-control').forEach(function (input) {
                    input.value = '';
                });
            });
        });

        var shiftModal = document.getElementById('shiftRosterDateModal');
        if (shiftModal) {
            shiftModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button) {
                    return;
                }

                var sourceDate = button.getAttribute('data-source-date') || '';
                var sourceLabel = button.getAttribute('data-source-label') || '';
                var targetInput = document.getElementById('shift_target_date');
                var inTimeInput = document.getElementById('shift_in_time');
                var outTimeInput = document.getElementById('shift_out_time');

                document.getElementById('shift_employee_id').value = button.getAttribute('data-employee-id') || '';
                document.getElementById('shift_category').value = button.getAttribute('data-category') || '';
                document.getElementById('shift_branch_name').value = button.getAttribute('data-branch-name') || '';
                document.getElementById('shift_source_date').value = sourceDate;
                document.getElementById('shiftRosterMeta').textContent = (button.getAttribute('data-employee') || '') + ' | ' + sourceLabel;

                targetInput.value = '';
                targetInput.min = button.getAttribute('data-month-start') || '';
                targetInput.max = button.getAttribute('data-month-end') || '';

                if (inTimeInput) {
                    inTimeInput.value = (button.getAttribute('data-in-time') || '').substring(0, 5);
                }

                if (outTimeInput) {
                    outTimeInput.value = (button.getAttribute('data-out-time') || '').substring(0, 5);
                }
            });
        }

        var rescheduleModal = document.getElementById('rescheduleRosterModal');
        if (rescheduleModal) {
            rescheduleModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button) {
                    return;
                }

                document.getElementById('reschedule_roster_id').value = button.getAttribute('data-roster-id') || '';
                document.getElementById('reschedule_in_time').value = (button.getAttribute('data-in-time') || '').substring(0, 5);
                document.getElementById('reschedule_out_time').value = (button.getAttribute('data-out-time') || '').substring(0, 5);
                document.getElementById('rescheduleRosterMeta').textContent = (button.getAttribute('data-employee') || '') + ' | ' + (button.getAttribute('data-date') || '');
            });
        }
    });
</script>
@endsection
