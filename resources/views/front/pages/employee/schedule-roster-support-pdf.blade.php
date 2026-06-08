<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Front Desk & Group D Schedule Roster' }}</title>
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

        .legend {
            margin-top: 6px;
        }

        .legend-item {
            display: inline-block;
            margin-right: 12px;
            color: #444;
            font-size: 7px;
            font-weight: bold;
        }

        .legend-swatch {
            display: inline-block;
            width: 10px;
            height: 10px;
            margin-right: 4px;
            border: 1px solid #999;
            vertical-align: middle;
        }

        .group-title {
            margin: 8px 0 4px;
            padding: 5px 6px;
            background: #f1f3f6;
            border: 1px solid #d9dee7;
            font-size: 9px;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
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

        .employee-head,
        .employee-cell {
            width: 95px;
            background: #fff;
            text-align: left;
        }

        .date-head {
            width: 25px;
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

        .date-head.skipped-head {
            width: 6px;
            min-width: 6px;
            max-width: 6px;
            padding: 1px 0;
            font-size: 3.8px;
            line-height: 1;
            overflow: hidden;
        }

        .employee-name {
            font-size: 6px;
            font-weight: bold;
            line-height: 1.12;
            text-align: left;
            word-break: break-word;
        }

        .employee-meta {
            margin-top: 2px;
            color: #666;
            font-size: 5.5px;
            text-align: left;
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

        .shift.yellow {
            background: #ffed9d;
        }

        .shift.light-green {
            background: #b7f3c8;
        }

        .shift.neutral {
            background: #dbe4ee;
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

        .blank {
            min-height: 16px;
        }

        .empty {
            padding: 20px 8px;
            border: 1px solid #d9dee7;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
<?php
$calendarDates = $calendarDates ?? [];
$calendarGroups = $calendarGroups ?? [];
$branchColorLegend = $branchColorLegend ?? [];
?>
    <div class="header">
        <h1 class="title">{{ $title ?? 'Front Desk & Group D Schedule Roster' }}</h1>
        <div class="subtitle">{{ $selectedMonthLabel }} | {{ $monthStartDate->format('d-m-Y') }} to {{ $monthEndDate->format('d-m-Y') }}</div>
        <div class="subtitle">
            Category: {{ $selectedCategory ?: 'All' }} | Branch: {{ $selectedBranchName ?: 'All' }}
            @if(!empty($selectedEmployeeLabel))
                | Employee: {{ $selectedEmployeeLabel }}
            @endif
        </div>
        <div class="legend">
            @foreach($branchColorLegend as $legend)
                <span class="legend-item">
                    <span class="legend-swatch" style="background: {{ $legend['color'] }};"></span>
                    {{ $legend['label'] }}
                </span>
            @endforeach
        </div>
    </div>

    @if(!empty($calendarGroups))
        @foreach($calendarGroups as $group)
            <div class="group-title">{{ $group['branch_name'] }} | {{ $group['category'] }}</div>
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
                        <th class="employee-head">Employee</th>
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
                    @foreach($group['employees'] as $employeeIndex => $employee)
                        <tr>
                            <td class="sl-cell">{{ $employeeIndex + 1 }}</td>
                            <td class="employee-cell">
                                <div class="employee-name">{{ $employee['employee_name'] ?: '-' }}</div>
                                <div class="employee-meta">{{ $employee['employee_no'] ?: '-' }}</div>
                            </td>
                            @foreach($calendarDates as $date)
                                <?php $cellShifts = $group['cells'][$employee['id']][$date['date']] ?? []; ?>
                                <td class="{{ $date['is_skipped_date'] ? 'skipped-cell' : '' }}">
                                    @if($date['is_skipped_date'])
                                        <div class="blank"></div>
                                    @elseif(!empty($cellShifts))
                                        @foreach($cellShifts as $shift)
                                            <div class="shift {{ $shift['shift_class'] }}">
                                                <span class="branch">{{ $shift['branch_code'] ?: '-' }}</span>
                                                <span class="time">{{ $shift['time_display'] ?: '-' }}</span>
                                            </div>
                                        @endforeach
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach
    @else
        <div class="empty">No roster found.</div>
    @endif
</body>
</html>
