<?php
$formatMoney = function ($value) {
    return $value === null ? '' : number_format(round((float) $value), 0);
};
$formatDateTime = function ($value) {
    return $value ? date('d-m-Y h:i A', strtotime($value)) : '';
};
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 22px;
        }

        body {
            color: #173145;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
            text-align: center;
        }

        .subtitle {
            color: #52677a;
            font-size: 12px;
            margin-bottom: 14px;
            text-align: center;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #b8c7d3;
            padding: 5px;
            vertical-align: top;
        }

        th {
            background: #e9f4f6;
            font-weight: bold;
            text-align: left;
        }

        .amount {
            font-weight: bold;
            text-align: right;
            white-space: nowrap;
        }

        .mono {
            font-family: DejaVu Sans Mono, monospace;
        }
    </style>
</head>
<body>
    <h1>Salary Statement</h1>
    <div class="subtitle">
        {{ $monthOptions[(int) $filters['month']] ?? $filters['month'] }} {{ $filters['year'] }} | {{ $filters['branch_name'] }} | {{ $filters['category'] }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Sl</th>
                <th>Empl Code</th>
                <th>Name</th>
                <th>Salary</th>
                <th>Bank Name</th>
                <th>Bank Branch</th>
                <th>Account No.</th>
                <th>IFSC Code</th>
                <th>Account Type</th>
                <th>Generated</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="mono">{{ $row->employee_no }}</td>
                    <td>{{ $row->employee_name }}</td>
                    <td class="amount">{{ $formatMoney($row->salary_amount) }}</td>
                    <td>{{ $row->bank_name }}</td>
                    <td>{{ $row->bank_branch }}</td>
                    <td class="mono">{{ $row->account_no }}</td>
                    <td class="mono">{{ $row->ifsc_code }}</td>
                    <td>{{ $row->account_type }}</td>
                    <td>{{ $formatDateTime($row->updated_at) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
