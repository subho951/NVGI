<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Due Student Report</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #000000;
            padding: 6px;
            vertical-align: middle;
        }
        th {
            background: #dbeaf7;
            font-weight: 700;
            text-align: center;
        }
        .title {
            background: #133d63;
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
        }
        .meta-key {
            background: #edf5ff;
            font-weight: 700;
            width: 150px;
        }
        .number {
            text-align: right;
            white-space: nowrap;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td class="title" colspan="{{ 8 + count($month_columns) }}">NVGI Due Student Report</td>
        </tr>
        <tr>
            <td class="meta-key">Unit</td>
            <td>{{ $unit_name }}</td>
            <td class="meta-key">Branch</td>
            <td>{{ $branch_name }}</td>
            <td class="meta-key">Class</td>
            <td>{{ $class_name }}</td>
            <td class="meta-key">Collection Session</td>
            <td>{{ $collection_session ?? '-' }}</td>
        </tr>
        <tr>
            <td class="meta-key">Generated At</td>
            <td colspan="7">{{ $generated_at }}</td>
        </tr>
    </table>
    <br>

    <table>
        <thead>
            <tr>
                <th>Sl</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Father Mobile</th>
                <th>Unit</th>
                <th>Branch</th>
                <th>Class</th>
                @foreach($month_columns as $monthColumn)
                    <th>{{ $monthColumn['name'] }} Due</th>
                @endforeach
                <th>Total Due</th>
            </tr>
        </thead>
        <tbody>
            @if(count($rows) > 0)
                @php $sl = 1; @endphp
                @foreach($rows as $row)
                    <tr>
                        <td class="text-center">{{ $sl++ }}</td>
                        <td>{{ $row->student_id_serial }}</td>
                        <td>{{ $row->full_name }}</td>
                        <td>{{ $row->father_mobile }}</td>
                        <td>{{ $row->unit_name }}</td>
                        <td>{{ $row->branch_name }}</td>
                        <td>{{ $row->class_name }}</td>
                        @foreach($month_columns as $monthColumn)
                            @php
                                $monthAlias = $monthColumn['alias'];
                                $monthDue = (float)(($row->$monthAlias) ? $row->$monthAlias : 0);
                            @endphp
                            <td class="number">{{ number_format($monthDue, 2, '.', '') }}</td>
                        @endforeach
                        <td class="number">{{ number_format((float)$row->total_due, 2, '.', '') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ 8 + count($month_columns) }}" class="text-center">No due students found for selected filters.</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>
