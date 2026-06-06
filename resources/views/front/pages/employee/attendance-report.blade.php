@extends('front.layouts.afterlogin')

@section('styles')
<style>
    .attendance-report {
        --report-ink: #172033;
        --report-muted: #6f7b8b;
        --report-line: #dfe5ec;
        color: var(--report-ink);
    }

    .report-header,
    .report-panel,
    .report-stat {
        border: 1px solid var(--report-line);
        border-radius: 14px;
        background: #ffffff;
        box-shadow: 0 9px 24px rgba(24, 42, 62, .05);
    }

    .report-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
    }

    .report-kicker {
        color: #62738a;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .report-title {
        margin: 3px 0 0;
        font-size: 25px;
        font-weight: 900;
    }

    .report-period {
        color: var(--report-muted);
        font-size: 13px;
        font-weight: 700;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1.2fr 1.7fr auto;
        gap: 12px;
        align-items: end;
        padding: 16px;
    }

    .filter-grid label {
        margin-bottom: 5px;
        color: var(--report-muted);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .07em;
        text-transform: uppercase;
    }

    .filter-grid .form-control,
    .filter-grid .form-select,
    .filter-grid .btn {
        min-height: 42px;
        border-radius: 9px;
    }

    .report-stats {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 10px;
    }

    .report-stat {
        padding: 13px 14px;
    }

    .report-stat span {
        display: block;
        color: var(--report-muted);
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .report-stat strong {
        display: block;
        margin-top: 4px;
        font-size: 22px;
    }

    .report-stat.late strong {
        color: #1265c4;
    }

    @media (max-width: 1100px) {
        .filter-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .report-stats {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 620px) {
        .report-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .filter-grid {
            grid-template-columns: 1fr;
        }

        .report-stats {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endsection

@section('content')
<div class="attendance-report">
    <section class="report-header mb-3">
        <div>
            <div class="report-kicker">Employees</div>
            <h1 class="report-title">Attendance Report</h1>
        </div>
        <div class="report-period">{{ $selected_period_label }}</div>
    </section>

    <section class="report-panel mb-3">
        <form method="GET" action="{{ route('employee.attendance-report') }}" class="filter-grid">
            <div>
                <label for="from_date">From Date</label>
                <input type="date" name="from_date" id="from_date" value="{{ $selected_from_date }}" class="form-control" required>
            </div>
            <div>
                <label for="to_date">To Date</label>
                <input type="date" name="to_date" id="to_date" value="{{ $selected_to_date }}" class="form-control" required>
            </div>
            <div>
                <label for="branch_id">Branch</label>
                <select name="branch_id" id="branch_id" class="form-select">
                    <option value="">All Branches</option>
                    @foreach($branch_options as $branchOption)
                        <option value="{{ $branchOption->id }}" {{ (int) $selected_branch_id === (int) $branchOption->id ? 'selected' : '' }}>
                            {{ $branchOption->name }}{{ $branchOption->serial_id ? ' - ' . $branchOption->serial_id : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="employee_id">Employee</label>
                <select name="employee_id" id="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employee_options as $employeeOption)
                        <option value="{{ $employeeOption->employee_id }}" {{ (int) $selected_employee_id === (int) $employeeOption->employee_id ? 'selected' : '' }}>
                            {{ $employeeOption->employee_no }} - {{ $employeeOption->employee_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary fw-bold w-100">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Search
                </button>
            </div>
        </form>
    </section>

    <section class="report-stats mb-3">
        <div class="report-stat"><span>Employees</span><strong>{{ $stats['employees'] }}</strong></div>
        <div class="report-stat"><span>Scheduled</span><strong>{{ $stats['scheduled'] }}</strong></div>
        <div class="report-stat"><span>Completed</span><strong>{{ $stats['completed'] }}</strong></div>
        <div class="report-stat"><span>Punched In</span><strong>{{ $stats['working'] }}</strong></div>
        <div class="report-stat"><span>Absent</span><strong>{{ $stats['absent'] }}</strong></div>
        <div class="report-stat"><span>Not Marked</span><strong>{{ $stats['pending'] }}</strong></div>
        <div class="report-stat late"><span>Late Count</span><strong>{{ $stats['late'] }}</strong></div>
    </section>

    @include('front.pages.employee.partials.attendance-matrix', ['matrix_table_id' => 'example'])
</div>
@endsection
