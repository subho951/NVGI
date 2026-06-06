<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: #f3f7fb;
            color: #183047;
            font-family: Arial, sans-serif;
        }

        .portal-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 24px;
            background: #ffffff;
            border-bottom: 1px solid #dce6ee;
            box-shadow: 0 7px 20px rgba(23, 48, 71, 0.06);
        }

        .portal-title {
            margin: 0;
            color: #143b5f;
            font-size: 20px;
            font-weight: 800;
        }

        .portal-subtitle {
            margin: 3px 0 0;
            color: #728598;
            font-size: 13px;
        }

        .portal-actions,
        .portal-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .portal-nav a {
            border: 1px solid #d7e1ea;
            border-radius: 9px;
            padding: 7px 10px;
            color: #143b5f;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
        }

        .portal-nav a.active {
            background: #143b5f;
            color: #ffffff;
        }

        .portal-main {
            max-width: 1560px;
            margin: 0 auto;
            padding: 24px;
        }

        .branch-hero {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
            padding: 20px;
            border-radius: 14px;
            background: linear-gradient(135deg, #123451 0%, #17616d 100%);
            color: #ffffff;
            box-shadow: 0 18px 38px rgba(18, 52, 81, 0.2);
        }

        .branch-hero h1 {
            margin: 0;
            font-size: 26px;
        }

        .branch-hero p {
            margin: 7px 0 0;
            color: rgba(255, 255, 255, 0.84);
        }

        .branch-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 13px;
            font-weight: 700;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 14px;
        }

        .summary-card,
        .filter-card,
        .roster-card {
            border: 1px solid #dce6ee;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 10px 22px rgba(23, 48, 71, 0.05);
        }

        .summary-card {
            padding: 13px 14px;
        }

        .summary-card span {
            display: block;
            color: #718395;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .summary-card strong {
            display: block;
            margin-top: 5px;
            color: #143b5f;
            font-size: 22px;
        }

        .filter-card {
            margin-bottom: 14px;
            padding: 12px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr 1.7fr auto;
            gap: 10px;
            align-items: end;
        }

        .filter-grid label {
            margin-bottom: 4px;
            color: #718395;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .filter-grid .form-select,
        .filter-grid .btn {
            min-height: 38px;
            border-radius: 8px;
            font-size: 13px;
        }

        .roster-card {
            overflow: hidden;
            margin-bottom: 14px;
        }

        .roster-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 13px;
            border-bottom: 1px solid #e5edf4;
            background: #fbfcfe;
        }

        .roster-card-head h2 {
            margin: 0;
            color: #143b5f;
            font-size: 17px;
            font-weight: 800;
        }

        .roster-card-head span {
            color: #718395;
            font-size: 12px;
            font-weight: 800;
        }

        .calendar-scroll {
            overflow-x: auto;
            width: 100%;
        }

        .calendar-grid {
            display: grid;
            min-width: 1180px;
        }

        .calendar-sl-head,
        .calendar-employee-head,
        .calendar-day-head,
        .calendar-sl-cell,
        .calendar-employee,
        .calendar-cell {
            border-right: 1px solid #dce6ee;
            border-bottom: 1px solid #dce6ee;
        }

        .calendar-sl-head,
        .calendar-employee-head,
        .calendar-day-head {
            min-height: 40px;
            background: #eef3f8;
            color: #506579;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .calendar-sl-head,
        .calendar-employee-head {
            position: sticky;
            z-index: 5;
            display: flex;
            align-items: center;
            padding: 7px;
        }

        .calendar-sl-head {
            left: 0;
            justify-content: center;
        }

        .calendar-employee-head {
            left: 42px;
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

        .day-name {
            font-size: 9px;
        }

        .day-date {
            font-size: 10px;
        }

        .calendar-sl-cell,
        .calendar-employee,
        .calendar-cell {
            min-height: 62px;
            background: #ffffff;
        }

        .calendar-sl-cell,
        .calendar-employee {
            position: sticky;
            z-index: 4;
            display: flex;
            align-items: center;
        }

        .calendar-sl-cell {
            left: 0;
            justify-content: center;
            color: #506579;
            font-size: 11px;
            font-weight: 800;
        }

        .calendar-employee {
            left: 42px;
            padding: 7px;
            text-align: left;
        }

        .employee-name {
            color: #183047;
            font-size: 11px;
            font-weight: 800;
            line-height: 1.15;
            word-break: break-word;
        }

        .employee-no {
            margin-top: 2px;
            color: #718395;
            font-size: 10px;
            font-weight: 700;
        }

        .calendar-cell {
            padding: 3px;
        }

        .calendar-cell.is-skipped {
            padding: 0;
            background: #fff1f1;
        }

        .blank-cell {
            min-height: 62px;
        }

        .shift-card {
            min-height: 36px;
            margin-bottom: 2px;
            padding: 4px 2px;
            border-radius: 4px;
            color: #101828;
            text-align: center;
            line-height: 1.1;
        }

        .shift-card.blue { background: #7cc7f2; }
        .shift-card.mint { background: #96e4d7; }
        .shift-card.pink { background: #f5a0cf; }
        .shift-card.amber { background: #ffed9d; }
        .shift-card.violet { background: #c7b7ff; }

        .shift-branch,
        .shift-time {
            display: block;
            font-size: 10px;
            font-weight: 800;
        }

        .empty-state {
            padding: 32px 18px;
            color: #718395;
            text-align: center;
            font-weight: 700;
        }

        @media (max-width: 700px) {
            .portal-topbar,
            .portal-main {
                padding: 14px;
            }

            .summary-grid,
            .filter-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
@php
    $calendarDates = $calendar_dates ?? [];
    $calendarGroups = $calendar_groups ?? [];
    $dateColumns = collect($calendarDates)->map(function ($date) {
        return !empty($date['is_skipped_date']) ? '16px' : 'minmax(42px, 1fr)';
    })->implode(' ');
@endphp

    <div class="portal-topbar">
        <div>
            <h2 class="portal-title">Branch Employee Management</h2>
            <p class="portal-subtitle">{{ $branch->name }} branch roster</p>
        </div>
        <div class="portal-actions">
            <nav class="portal-nav">
                <a href="{{ route('branch.portal.employees') }}">Employees</a>
                <a href="{{ route('branch.portal.rosters') }}" class="active">Roster</a>
                <a href="{{ route('branch.portal.attendance.index') }}">Attendance</a>
                <a href="{{ route('branch.portal.attendance.report') }}">Report</a>
            </nav>
            <form method="POST" action="{{ route('branch.portal.logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <main class="portal-main">
        <section class="branch-hero">
            <div>
                <h1>{{ $branch->name }} Schedule Roster</h1>
                <p>Complete branch roster for {{ $selected_month_label }}.</p>
            </div>
            <span class="branch-badge">
                <i class="fa-solid fa-code-branch"></i> {{ $branch->serial_id }}
            </span>
        </section>

        <section class="summary-grid">
            <div class="summary-card">
                <span>Roster Rows</span>
                <strong>{{ $stats['rows'] }}</strong>
            </div>
            <div class="summary-card">
                <span>Employees</span>
                <strong>{{ $stats['employees'] }}</strong>
            </div>
            <div class="summary-card">
                <span>Categories</span>
                <strong>{{ $stats['categories'] }}</strong>
            </div>
            <div class="summary-card">
                <span>Shift Dates</span>
                <strong>{{ $stats['dates'] }}</strong>
            </div>
        </section>

        <section class="filter-card">
            <form method="GET" action="{{ route('branch.portal.rosters') }}" class="filter-grid">
                <div>
                    <label for="month">Month & Year</label>
                    <select name="month" id="month" class="form-select">
                        @foreach($month_options as $month)
                            <option value="{{ $month['value'] }}" {{ $selected_month_value === $month['value'] ? 'selected' : '' }}>{{ $month['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="category">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" {{ $selected_category === $category ? 'selected' : '' }}>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="employee_id">Employee</label>
                    <select name="employee_id" id="employee_id" class="form-select">
                        <option value="">All Employees</option>
                        @foreach($employee_options as $employee)
                            <option value="{{ $employee['id'] }}" {{ (int) $selected_employee_id === (int) $employee['id'] ? 'selected' : '' }}>{{ $employee['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary fw-bold w-100">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>
                </div>
            </form>
        </section>

        @if(!empty($calendarGroups))
            @foreach($calendarGroups as $group)
                <section class="roster-card">
                    <div class="roster-card-head">
                        <h2>{{ $group['category'] }}</h2>
                        <span>{{ $group['employees_count'] }} employees | {{ $group['rows'] }} rows</span>
                    </div>
                    <div class="calendar-scroll">
                        <div class="calendar-grid" style="grid-template-columns: 42px 190px {{ $dateColumns }};">
                            <div class="calendar-sl-head">SL</div>
                            <div class="calendar-employee-head">Employee</div>
                            @foreach($calendarDates as $date)
                                <div class="calendar-day-head {{ $date['is_skipped_date'] ? 'is-skipped' : '' }}" title="{{ $date['day_label'] }}, {{ $date['date_label'] }} {{ $date['month_label'] }}">
                                    <span class="day-name">{{ $date['day_initial'] }}</span>
                                    <span class="day-date">{{ $date['date_label'] }}</span>
                                </div>
                            @endforeach

                            @foreach($group['employees'] as $employeeIndex => $employee)
                                <div class="calendar-sl-cell">{{ $employeeIndex + 1 }}</div>
                                <div class="calendar-employee">
                                    <div>
                                        <div class="employee-name">{{ $employee['employee_name'] ?: '-' }}</div>
                                        <div class="employee-no">{{ $employee['employee_no'] ?: '-' }}</div>
                                    </div>
                                </div>

                                @foreach($calendarDates as $date)
                                    @php
                                        $cellShifts = $group['cells'][$employee['id']][$date['date']] ?? [];
                                    @endphp
                                    <div class="calendar-cell {{ $date['is_skipped_date'] ? 'is-skipped' : '' }}">
                                        @if($date['is_skipped_date'])
                                            <div class="blank-cell"></div>
                                        @elseif(!empty($cellShifts))
                                            @foreach($cellShifts as $shift)
                                                <div class="shift-card {{ $shift['shift_class'] }}" title="{{ $shift['branch_name'] }} | {{ $shift['time_display'] }}">
                                                    <span class="shift-branch">{{ $shift['branch_code'] ?: '-' }}</span>
                                                    <span class="shift-time">{{ $shift['time_display'] ?: '-' }}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </section>
            @endforeach
        @else
            <section class="roster-card">
                <div class="empty-state">
                    <i class="fa-solid fa-calendar-days mb-2"></i>
                    <div>No roster found for the selected filters.</div>
                </div>
            </section>
        @endif
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
