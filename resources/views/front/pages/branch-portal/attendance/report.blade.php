<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        :root {
            --ink: #172033;
            --muted: #738092;
            --line: #dfe5ec;
            --page: #f3f6fa;
            --green: #2eae3a;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--page);
            color: var(--ink);
            font-family: Arial, sans-serif;
        }

        .portal-topbar {
            position: sticky;
            z-index: 20;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 14px 20px;
            border-bottom: 1px solid var(--line);
            background: #ffffff;
        }

        .portal-title { margin: 0; font-size: 19px; }
        .portal-subtitle { margin: 3px 0 0; color: var(--muted); font-size: 12px; }
        .portal-actions, .portal-nav { display: flex; align-items: center; gap: 7px; flex-wrap: wrap; }

        .portal-nav a {
            padding: 8px 10px;
            border: 1px solid var(--line);
            border-radius: 9px;
            color: #405267;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        .portal-nav a.active {
            border-color: #bce4c1;
            background: #eaf8ec;
            color: #21812c;
        }

        .logout-button {
            padding: 9px 11px;
            border: 0;
            border-radius: 9px;
            background: #e73b34;
            color: #ffffff;
        }

        .report-main {
            width: min(100%, 1800px);
            margin: 0 auto;
            padding: 20px;
        }

        .report-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 19px;
            border-radius: 16px;
            background: linear-gradient(135deg, #16364f, #20716f);
            color: #ffffff;
        }

        .report-hero h1 { margin: 0; font-size: 24px; }
        .report-hero p { margin: 6px 0 0; color: rgba(255,255,255,.78); }

        .filter-panel,
        .stat {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 8px 22px rgba(22, 39, 58, .05);
        }

        .filter-panel { margin-top: 14px; padding: 14px; }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 2fr auto;
            gap: 10px;
            align-items: end;
        }

        .filter-grid label {
            display: block;
            margin-bottom: 5px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .filter-grid input,
        .filter-grid select,
        .filter-grid button {
            width: 100%;
            min-height: 43px;
            padding: 9px 11px;
            border: 1px solid #d6dde6;
            border-radius: 9px;
            background: #ffffff;
        }

        .filter-grid button {
            border-color: var(--green);
            background: var(--green);
            color: #ffffff;
            font-weight: 900;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 9px;
            margin: 14px 0;
        }

        .stat { padding: 12px; }
        .stat span { color: var(--muted); font-size: 9px; font-weight: 900; text-transform: uppercase; }
        .stat strong { display: block; margin-top: 4px; font-size: 21px; }
        .stat.late strong { color: #1265c4; }

        @media (max-width: 980px) {
            .filter-grid { grid-template-columns: 1fr 1fr; }
            .stats { grid-template-columns: repeat(4, 1fr); }
        }

        @media (max-width: 760px) {
            .portal-topbar {
                align-items: flex-start;
                padding: 13px;
            }

            .portal-actions {
                justify-content: flex-end;
            }

            .portal-nav {
                display: grid;
                width: 100%;
                grid-template-columns: repeat(2, 1fr);
            }

            .portal-nav a { text-align: center; }
            .report-main { padding: 13px; }
            .report-hero { align-items: flex-start; }
            .filter-grid { grid-template-columns: 1fr; }
            .stats { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <header class="portal-topbar">
        <div>
            <h2 class="portal-title">Branch Employee Management</h2>
            <p class="portal-subtitle">{{ $branch->name }} attendance report</p>
        </div>
        <div class="portal-actions">
            <nav class="portal-nav">
                <a href="{{ route('branch.portal.employees') }}">Employees</a>
                <a href="{{ route('branch.portal.rosters') }}">Roster</a>
                <a href="{{ route('branch.portal.attendance.index') }}">Attendance</a>
                <a href="{{ route('branch.portal.attendance.report') }}" class="active">Report</a>
            </nav>
            <form method="POST" action="{{ route('branch.portal.logout') }}">
                @csrf
                <button type="submit" class="logout-button" aria-label="Logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </header>

    <main class="report-main">
        <section class="report-hero">
            <div>
                <h1>Attendance Report</h1>
                <p>{{ $branch->name }} centre | {{ $selected_period_label }}</p>
            </div>
            <i class="fa-solid fa-chart-column fa-2x"></i>
        </section>

        <section class="filter-panel">
            <form method="GET" action="{{ route('branch.portal.attendance.report') }}" class="filter-grid">
                <div>
                    <label for="from_date">From Date</label>
                    <input type="date" name="from_date" id="from_date" value="{{ $selected_from_date }}" required>
                </div>
                <div>
                    <label for="to_date">To Date</label>
                    <input type="date" name="to_date" id="to_date" value="{{ $selected_to_date }}" required>
                </div>
                <div>
                    <label for="employee_id">Employee</label>
                    <select name="employee_id" id="employee_id">
                        <option value="">All Employees</option>
                        @foreach($employee_options as $employeeOption)
                            <option value="{{ $employeeOption->employee_id }}" {{ (int) $selected_employee_id === (int) $employeeOption->employee_id ? 'selected' : '' }}>
                                {{ $employeeOption->employee_no }} - {{ $employeeOption->employee_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                </div>
            </form>
        </section>

        <section class="stats">
            <div class="stat"><span>Employees</span><strong>{{ $stats['employees'] }}</strong></div>
            <div class="stat"><span>Scheduled</span><strong>{{ $stats['scheduled'] }}</strong></div>
            <div class="stat"><span>Completed</span><strong>{{ $stats['completed'] }}</strong></div>
            <div class="stat"><span>Punched In</span><strong>{{ $stats['working'] }}</strong></div>
            <div class="stat"><span>Absent</span><strong>{{ $stats['absent'] }}</strong></div>
            <div class="stat"><span>Not Marked</span><strong>{{ $stats['pending'] }}</strong></div>
            <div class="stat late"><span>Late Count</span><strong>{{ $stats['late'] }}</strong></div>
        </section>

        @include('front.pages.employee.partials.attendance-matrix')
    </main>
</body>
</html>
