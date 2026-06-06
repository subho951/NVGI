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
        box-shadow: 0 9px 24px rgba(24, 42, 62, 0.05);
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
        grid-template-columns: 1fr 1.2fr 1.7fr auto;
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
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
    }

    .report-stat {
        padding: 13px 14px;
    }

    .report-stat span {
        display: block;
        color: var(--report-muted);
        font-size: 10px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .report-stat strong {
        display: block;
        margin-top: 4px;
        font-size: 23px;
    }

    .table-panel {
        padding: 14px;
    }

    .attendance-table {
        width: 100%;
        font-size: 12px;
    }

    .attendance-table th {
        background: #172f49;
        color: #ffffff;
        font-size: 10px;
        letter-spacing: .04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .attendance-table td {
        vertical-align: middle;
        white-space: nowrap;
    }

    .employee-cell strong,
    .employee-cell span {
        display: block;
    }

    .employee-cell span {
        margin-top: 2px;
        color: var(--report-muted);
        font-size: 10px;
    }

    .report-status {
        display: inline-flex;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .report-status.completed { background: #e6f7e9; color: #21812c; }
    .report-status.working { background: #fff4d9; color: #a16400; }
    .report-status.absent { background: #ffebea; color: #b7332c; }
    .report-status.pending { background: #edf1f5; color: #687586; }

    .photo-links {
        display: flex;
        gap: 5px;
    }

    .photo-links a {
        display: inline-flex;
        padding: 4px 7px;
        border: 1px solid #d8e0e8;
        border-radius: 7px;
        color: #315c86;
        font-size: 9px;
        font-weight: 900;
        text-decoration: none;
    }

    @media (max-width: 900px) {
        .filter-grid {
            grid-template-columns: 1fr 1fr;
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
        <div class="report-period">{{ $selected_month_label }}</div>
    </section>

    <section class="report-panel mb-3">
        <form method="GET" action="{{ route('employee.attendance-report') }}" class="filter-grid">
            <div>
                <label for="month">Month</label>
                <input type="month" name="month" id="month" value="{{ $selected_month }}" class="form-control" required>
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
        <div class="report-stat"><span>Scheduled</span><strong>{{ $stats['scheduled'] }}</strong></div>
        <div class="report-stat"><span>Completed</span><strong>{{ $stats['completed'] }}</strong></div>
        <div class="report-stat"><span>Punched In</span><strong>{{ $stats['working'] }}</strong></div>
        <div class="report-stat"><span>Absent</span><strong>{{ $stats['absent'] }}</strong></div>
        <div class="report-stat"><span>Not Marked</span><strong>{{ $stats['pending'] }}</strong></div>
    </section>

    <section class="report-panel table-panel">
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered attendance-table align-middle">
                <thead>
                    <tr>
                        <th>Sl.</th>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Category</th>
                        <th>Branch</th>
                        <th>Scheduled</th>
                        <th>Punch In</th>
                        <th>Punch Out</th>
                        <th>Worked</th>
                        <th>Status</th>
                        <th>Photos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row['attendance_date']->format('d-m-Y') }}</td>
                            <td class="employee-cell">
                                <strong>{{ $row['employee_name'] ?: '-' }}</strong>
                                <span>{{ $row['employee_no'] ?: '-' }}</span>
                            </td>
                            <td>{{ $row['category'] }}</td>
                            <td>{{ $row['branch_name'] }}</td>
                            <td>{{ $row['scheduled_time'] ?: '-' }}</td>
                            <td>{{ $row['punch_in_time'] ?: '-' }}</td>
                            <td>{{ $row['punch_out_time'] ?: '-' }}</td>
                            <td>{{ $row['worked_time'] ?: '-' }}</td>
                            <td><span class="report-status {{ $row['status'] }}">{{ $row['status_label'] }}</span></td>
                            <td>
                                <div class="photo-links">
                                    @if($row['punch_in_image'])
                                        <a href="{{ url('public' . $row['punch_in_image']) }}" target="_blank">IN</a>
                                    @endif
                                    @if($row['punch_out_image'])
                                        <a href="{{ url('public' . $row['punch_out_image']) }}" target="_blank">OUT</a>
                                    @endif
                                    @if(!$row['punch_in_image'] && !$row['punch_out_image'])
                                        -
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
