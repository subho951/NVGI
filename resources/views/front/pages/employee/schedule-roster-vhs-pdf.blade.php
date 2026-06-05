<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>VHS Teacher Schedule Roster</title>
    <style>
        @page {
            margin: 14px;
        }

        body {
            margin: 0;
            color: #172033;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
        }

        .header {
            margin-bottom: 8px;
        }

        .title {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .subtitle {
            margin-top: 3px;
            color: #555;
            font-size: 9px;
        }

        .note {
            margin-top: 4px;
            color: #777;
            font-size: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #d9dee7;
            vertical-align: top;
        }

        th {
            background: #f1f3f6;
            color: #172033;
            font-size: 6px;
            font-weight: bold;
            text-align: center;
            padding: 4px 2px;
        }

        td {
            min-height: 34px;
            padding: 2px;
        }

        .sl-head,
        .sl-cell {
            width: 18px;
            text-align: center;
        }

        .team-head,
        .teacher-cell {
            width: 95px;
            background: #fff;
            text-align: left;
        }

        .skipped-head,
        .skipped-cell {
            width: 6px;
            min-width: 6px;
            max-width: 6px;
            padding-left: 0;
            padding-right: 0;
            background: #ffe8e8;
        }

        .teacher-name {
            font-size: 6px;
            font-weight: bold;
            line-height: 1.12;
            text-align: left;
            word-break: break-word;
        }

        .teacher-meta {
            margin-top: 2px;
            color: #666;
            font-size: 5.5px;
            text-align: left;
        }

        .date-head {
            width: 25px;
        }

        .date-head.skipped-head {
            width: 6px;
            min-width: 6px;
            max-width: 6px;
            padding: 1px 0;
            font-size: 3.8px;
            line-height: 1;
            overflow: hidden;
        }

        .shift {
            margin-bottom: 2px;
            padding: 4px 2px;
            border-radius: 2px;
            color: #111;
            text-align: center;
            line-height: 1.12;
        }

        .shift.blue {
            background: #7cc7f2;
        }

        .shift.mint {
            background: #96e4d7;
        }

        .shift.pink {
            background: #f5a0cf;
        }

        .shift.amber {
            background: #ffed9d;
        }

        .shift.violet {
            background: #c7b7ff;
        }

        .role {
            display: none;
            font-size: 5.8px;
            font-weight: bold;
        }

        .branch {
            display: block;
            margin-top: 1px;
            font-size: 6px;
            font-weight: bold;
        }

        .time {
            display: block;
            margin-top: 1px;
            font-size: 6.3px;
            font-weight: bold;
        }

        .empty {
            color: #888;
            font-size: 5.8px;
            text-align: center;
        }

        .blank {
            min-height: 16px;
        }
    </style>
</head>
<body>
<?php
$calendarDates = $calendarDates ?? [];
$calendarEmployees = $calendarEmployees ?? [];
$calendarCells = $calendarCells ?? [];
$selectedBranchLabel = $selectedBranchLabel ?? '';
$selectedEmployeeLabel = $selectedEmployeeLabel ?? '';
?>
    <div class="header">
        <h1 class="title">VHS Teacher Schedule Roster</h1>
        <div class="subtitle">{{ $selectedMonthLabel }} | {{ $monthStartDate->format('d-m-Y') }} to {{ $monthEndDate->format('d-m-Y') }}</div>
        <div class="subtitle">Branch: {{ $selectedBranchLabel ?: 'All' }} | Employee: {{ $selectedEmployeeLabel ?: 'All' }}</div>
        <div class="note">Sundays and 2nd/4th Saturdays are shown blank. Generated shift rows are locked.</div>
    </div>

    <table>
        <colgroup>
            <col style="width: 18px;">
            <col style="width: 95px;">
            @foreach($calendarDates as $date)
                <col style="width: {{ $date['is_skipped_date'] ? '6px' : '25px' }};">
            @endforeach
        </colgroup>
        <thead>
            <tr>
                <th class="sl-head">SL</th>
                <th class="team-head">Teacher</th>
                @foreach($calendarDates as $date)
                    <th class="date-head {{ $date['is_skipped_date'] ? 'skipped-head' : '' }}">
                        @if($date['is_skipped_date'])
                            {{ $date['date_label'] }}
                        @else
                            {{ $date['day_initial'] }}<br>{{ $date['date_label'] }}
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($calendarEmployees as $employeeIndex => $employee)
                <tr>
                    <td class="sl-cell">{{ $employeeIndex + 1 }}</td>
                    <td class="teacher-cell">
                        <div class="teacher-name">{{ $employee['employee_name'] ?: '-' }}</div>
                        <div class="teacher-meta">{{ $employee['employee_no'] ?: '-' }}</div>
                    </td>
                    @foreach($calendarDates as $date)
                        <?php
                            $cellShifts = $calendarCells[$employee['id']][$date['date']] ?? [];
                        ?>
                        <td class="{{ $date['is_skipped_date'] ? 'skipped-cell' : '' }}">
                            @if($date['is_skipped_date'])
                                <div class="blank"></div>
                            @elseif(!empty($cellShifts))
                                @foreach($cellShifts as $shift)
                                    <div class="shift {{ $shift['shift_class'] }}">
                                        <span class="role">VHS Teacher</span>
                                        <span class="branch">{{ $shift['branch_code'] ?: '-' }}</span>
                                        <span class="time">{{ $shift['time_display'] ?: '-' }}</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="empty">N/A</div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
