<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        :root {
            --ink: #172033;
            --muted: #7a8493;
            --line: #e4e8ee;
            --surface: #ffffff;
            --page: #f4f6fa;
            --green: #2fb23a;
            --green-soft: #eaf8ec;
            --red: #ef3128;
            --red-soft: #fff0ef;
            --amber: #e99412;
            --amber-soft: #fff6df;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background: var(--page);
            color: var(--ink);
            font-family: Arial, sans-serif;
        }

        button,
        input {
            font: inherit;
        }

        .mobile-shell {
            width: min(100%, 680px);
            min-height: 100vh;
            margin: 0 auto;
            background: var(--page);
        }

        .topbar {
            position: sticky;
            z-index: 20;
            top: 0;
            padding: max(14px, env(safe-area-inset-top)) 16px 12px;
            border-bottom: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
        }

        .topbar-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .eyebrow {
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .page-title {
            margin: 3px 0 0;
            font-size: 22px;
            line-height: 1.15;
        }

        .logout-button {
            width: 42px;
            height: 42px;
            border: 1px solid #f0d7d5;
            border-radius: 12px;
            background: #fff6f5;
            color: #c8322b;
        }

        .portal-nav {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 7px;
            margin-top: 12px;
        }

        .portal-nav a {
            padding: 9px 7px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #ffffff;
            color: #526071;
            font-size: 12px;
            font-weight: 800;
            text-align: center;
            text-decoration: none;
        }

        .portal-nav a.active {
            border-color: #bfe8c5;
            background: var(--green-soft);
            color: #21882c;
        }

        .content {
            padding: 16px 14px calc(30px + env(safe-area-inset-bottom));
        }

        .date-panel {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 15px;
            border-radius: 16px;
            background: linear-gradient(135deg, #17283b, #245f68);
            color: #ffffff;
            box-shadow: 0 14px 30px rgba(25, 50, 71, 0.18);
        }

        .date-panel h2 {
            margin: 0;
            font-size: 18px;
        }

        .date-panel p {
            margin: 5px 0 0;
            color: rgba(255, 255, 255, 0.78);
            font-size: 12px;
        }

        .date-icon {
            display: grid;
            width: 48px;
            height: 48px;
            place-items: center;
            flex: 0 0 auto;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 20px;
        }

        .flash {
            margin-bottom: 12px;
            padding: 13px 14px;
            border-radius: 13px;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.45;
        }

        .flash.success {
            border: 1px solid #bce7c2;
            background: var(--green-soft);
            color: #1c7a27;
        }

        .flash.error {
            border: 1px solid #f2c6c3;
            background: var(--red-soft);
            color: #b62f29;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 7px;
            margin: 13px 0;
        }

        .stat {
            padding: 10px 5px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--surface);
            text-align: center;
        }

        .stat strong {
            display: block;
            font-size: 18px;
        }

        .stat span {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .search-wrap {
            position: sticky;
            z-index: 15;
            top: 124px;
            padding: 8px 0;
            background: var(--page);
        }

        .search-box {
            position: relative;
        }

        .search-box i {
            position: absolute;
            top: 50%;
            left: 15px;
            color: #929aa7;
            transform: translateY(-50%);
        }

        .search-box input {
            width: 100%;
            min-height: 50px;
            padding: 12px 44px;
            border: 1px solid #dce2e9;
            border-radius: 15px;
            outline: none;
            background: #ffffff;
            color: var(--ink);
            box-shadow: 0 8px 18px rgba(24, 40, 60, 0.05);
        }

        .search-box input:focus {
            border-color: #77c680;
            box-shadow: 0 0 0 4px rgba(47, 178, 58, 0.1);
        }

        .clear-search {
            position: absolute;
            top: 50%;
            right: 8px;
            display: none;
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 50%;
            background: #f0f2f5;
            color: #727c89;
            transform: translateY(-50%);
        }

        .section-label {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin: 9px 2px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .attendance-list {
            display: grid;
            gap: 10px;
        }

        .attendance-card {
            position: relative;
            display: grid;
            grid-template-columns: 56px minmax(0, 1fr) auto;
            align-items: center;
            gap: 11px;
            min-height: 92px;
            padding: 13px;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--surface);
            color: inherit;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(22, 37, 55, 0.05);
        }

        .attendance-card.punched_in {
            border-color: #f0d392;
            background: #fffdf8;
        }

        .attendance-card.completed {
            opacity: 0.74;
        }

        .avatar,
        .avatar-fallback {
            width: 56px;
            height: 56px;
            border: 2px solid #dce5ee;
            border-radius: 50%;
            object-fit: cover;
        }

        .avatar-fallback {
            display: grid;
            place-items: center;
            background: #edf3f8;
            color: #557086;
            font-size: 21px;
        }

        .avatar-fallback.female {
            border-color: #f0ccdc;
            background: #fff1f7;
            color: #b85381;
        }

        .avatar-fallback.male {
            border-color: #c9e1f3;
            background: #eef8ff;
            color: #3178aa;
        }

        .employee-name {
            margin: 0;
            font-size: 15px;
            line-height: 1.25;
        }

        .employee-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 5px 8px;
            margin-top: 6px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
        }

        .shift-line {
            margin-top: 7px;
            color: #344357;
            font-size: 12px;
            font-weight: 800;
        }

        .card-side {
            display: flex;
            min-width: 82px;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        .status-pill {
            display: inline-flex;
            padding: 5px 7px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 900;
            text-align: center;
            text-transform: uppercase;
        }

        .status-pill.pending {
            background: var(--green-soft);
            color: #238b2d;
        }

        .status-pill.punched_in {
            background: var(--amber-soft);
            color: #a96700;
        }

        .status-pill.completed {
            background: #edf0f3;
            color: #697585;
        }

        .action-hint {
            color: var(--green);
            font-size: 11px;
            font-weight: 900;
        }

        .punched_in .action-hint {
            color: var(--red);
        }

        .time-stamp {
            color: #5d6978;
            font-size: 11px;
            font-weight: 800;
        }

        .empty-state {
            padding: 38px 20px;
            border: 1px dashed #ccd4dd;
            border-radius: 16px;
            background: #ffffff;
            color: var(--muted);
            text-align: center;
        }

        .empty-state i {
            margin-bottom: 11px;
            color: #a6b0bc;
            font-size: 30px;
        }

        .desktop-blocker {
            width: min(92%, 500px);
            margin: 12vh auto;
            padding: 34px 24px;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: #ffffff;
            text-align: center;
            box-shadow: 0 18px 50px rgba(22, 37, 55, 0.1);
        }

        .desktop-blocker i {
            color: var(--green);
            font-size: 44px;
        }

        .desktop-blocker h1 {
            margin: 18px 0 8px;
            font-size: 22px;
        }

        .desktop-blocker p {
            margin: 0;
            color: var(--muted);
            line-height: 1.5;
        }

        @media (max-width: 390px) {
            .attendance-card {
                grid-template-columns: 50px minmax(0, 1fr);
            }

            .avatar,
            .avatar-fallback {
                width: 50px;
                height: 50px;
            }

            .card-side {
                grid-column: 2;
                min-width: 0;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
@if(!$is_mobile_device)
    <section class="desktop-blocker">
        <i class="fa-solid fa-mobile-screen-button"></i>
        <h1>Open Attendance on Mobile</h1>
        <p>This module uses the phone camera and is available only on a mobile device.</p>
    </section>
@else
    <div class="mobile-shell">
        <header class="topbar">
            <div class="topbar-row">
                <div>
                    <div class="eyebrow">{{ $branch->name }} Branch</div>
                    <h1 class="page-title">Today Attendance</h1>
                </div>
                <form method="POST" action="{{ route('branch.portal.logout') }}">
                    @csrf
                    <button type="submit" class="logout-button" aria-label="Logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
            <nav class="portal-nav">
                <a href="{{ route('branch.portal.employees') }}">Employees</a>
                <a href="{{ route('branch.portal.rosters') }}">Roster</a>
                <a href="{{ route('branch.portal.attendance.index') }}" class="active">Attendance</a>
            </nav>
        </header>

        <main class="content">
            @if(session('success_message'))
                <div class="flash success">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ session('success_message') }}
                </div>
            @endif

            @if(session('error_message'))
                <div class="flash error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    {{ session('error_message') }}
                </div>
            @endif

            <section class="date-panel">
                <div>
                    <h2>{{ $attendance_date->format('l, d M Y') }}</h2>
                    <p>Only employees scheduled at this centre today are shown.</p>
                </div>
                <div class="date-icon">
                    <i class="fa-regular fa-calendar-check"></i>
                </div>
            </section>

            <section class="stats">
                <div class="stat">
                    <strong>{{ $stats['scheduled'] }}</strong>
                    <span>Scheduled</span>
                </div>
                <div class="stat">
                    <strong>{{ $stats['pending'] }}</strong>
                    <span>Need In</span>
                </div>
                <div class="stat">
                    <strong>{{ $stats['punched_in'] }}</strong>
                    <span>Need Out</span>
                </div>
                <div class="stat">
                    <strong>{{ $stats['completed'] }}</strong>
                    <span>Done</span>
                </div>
            </section>

            <div class="search-wrap">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" id="attendanceSearch" placeholder="Search employee name or number" autocomplete="off">
                    <button type="button" class="clear-search" id="clearSearch" aria-label="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <div class="section-label">
                <span>Today's Classes</span>
                <span id="visibleCount">{{ $rows->count() }} shown</span>
            </div>

            @if($rows->isNotEmpty())
                <section class="attendance-list" id="attendanceList">
                    @foreach($rows as $row)
                        @php
                            $employeeImage = trim((string) $row['employee_image']);
                            $hasImage = $employeeImage !== '' && file_exists(public_path(ltrim($employeeImage, '/\\')));
                            $gender = strtolower(trim((string) $row['gender']));
                            $avatarClass = $gender === 'female' ? 'female' : ($gender === 'male' ? 'male' : '');
                            $avatarIcon = $gender === 'female' ? 'fa-person-dress' : ($gender === 'male' ? 'fa-person' : 'fa-user');
                            $searchText = strtolower(implode(' ', [
                                $row['employee_name'],
                                $row['employee_no'],
                                $row['category'],
                                $row['branch_name'],
                                $row['branch_code'],
                                $row['scheduled_time'],
                            ]));
                        @endphp

                        @if($row['mark_url'] !== '')
                            <a href="{{ $row['mark_url'] }}"
                               class="attendance-card {{ $row['state'] }}"
                               data-attendance-row
                               data-search="{{ $searchText }}">
                        @else
                            <div class="attendance-card {{ $row['state'] }}"
                                 data-attendance-row
                                 data-search="{{ $searchText }}">
                        @endif

                            <div>
                                @if($hasImage)
                                    <img src="{{ url('public' . $employeeImage) }}"
                                         alt="{{ $row['employee_name'] }}"
                                         class="avatar"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">
                                @endif
                                <span class="avatar-fallback {{ $avatarClass }}" style="{{ $hasImage ? 'display:none;' : '' }}">
                                    <i class="fa-solid {{ $avatarIcon }}"></i>
                                </span>
                            </div>

                            <div>
                                <h3 class="employee-name">{{ $row['employee_name'] ?: 'Employee' }}</h3>
                                <div class="employee-meta">
                                    <span>{{ $row['employee_no'] ?: '-' }}</span>
                                    <span>{{ $row['category'] }}</span>
                                    <span>{{ $row['branch_code'] ?: $row['branch_name'] }}</span>
                                </div>
                                <div class="shift-line">
                                    <i class="fa-regular fa-clock"></i>
                                    {{ $row['scheduled_time'] ?: 'Time not set' }}
                                </div>
                            </div>

                            <div class="card-side">
                                <span class="status-pill {{ $row['state'] }}">{{ $row['state_label'] }}</span>
                                @if($row['state'] === 'completed')
                                    <span class="time-stamp">{{ $row['punch_in_time'] }} - {{ $row['punch_out_time'] }}</span>
                                @else
                                    <span class="action-hint">{{ $row['action_label'] }} <i class="fa-solid fa-chevron-right"></i></span>
                                @endif
                            </div>

                        @if($row['mark_url'] !== '')
                            </a>
                        @else
                            </div>
                        @endif
                    @endforeach
                </section>

                <section class="empty-state" id="searchEmptyState" hidden>
                    <i class="fa-solid fa-user-slash"></i>
                    <div>No scheduled employee matches your search.</div>
                </section>
            @else
                <section class="empty-state">
                    <i class="fa-regular fa-calendar-xmark"></i>
                    <div>No employee class is scheduled at this centre today.</div>
                </section>
            @endif
        </main>
    </div>

    <script>
        const attendanceSearch = document.getElementById('attendanceSearch');
        const clearSearch = document.getElementById('clearSearch');
        const attendanceRows = Array.from(document.querySelectorAll('[data-attendance-row]'));
        const visibleCount = document.getElementById('visibleCount');
        const searchEmptyState = document.getElementById('searchEmptyState');

        function filterAttendanceRows() {
            const query = attendanceSearch.value.trim().toLowerCase();
            let shown = 0;

            attendanceRows.forEach(function(row) {
                const matches = row.dataset.search.includes(query);
                row.hidden = !matches;
                if (matches) {
                    shown++;
                }
            });

            clearSearch.style.display = query ? 'block' : 'none';
            visibleCount.textContent = shown + ' shown';

            if (searchEmptyState) {
                searchEmptyState.hidden = shown !== 0;
            }
        }

        if (attendanceSearch) {
            attendanceSearch.addEventListener('input', filterAttendanceRows);
        }

        if (clearSearch) {
            clearSearch.addEventListener('click', function() {
                attendanceSearch.value = '';
                filterAttendanceRows();
                attendanceSearch.focus();
            });
        }
    </script>
@endif
</body>
</html>
