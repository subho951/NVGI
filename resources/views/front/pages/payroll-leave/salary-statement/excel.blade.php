<?php
$formatMoney = function ($value) {
    return $value === null ? '' : number_format(round((float) $value), 0, '.', '');
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
        table {
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #888;
            padding: 6px;
            vertical-align: top;
            white-space: nowrap;
        }

        th {
            background: #e9f4f6;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="14">
                    Salary Statement - {{ $monthOptions[(int) $filters['month']] ?? $filters['month'] }} {{ $filters['year'] }} - {{ $filters['branch_name'] }} - {{ $filters['category'] }}
                </th>
            </tr>
            <tr>
                <th>Sl No.</th>
                <th>Month</th>
                <th>Year</th>
                <th>Branch</th>
                <th>Category</th>
                <th>Empl Code</th>
                <th>Name</th>
                <th>Salary</th>
                <th>Bank Name</th>
                <th>Bank Branch</th>
                <th>Account No.</th>
                <th>IFSC Code</th>
                <th>Account Type</th>
                <th>Generated At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $monthOptions[(int) $row->salary_month] ?? $row->salary_month }}</td>
                    <td>{{ $row->salary_year }}</td>
                    <td>{{ $row->branch_name }}</td>
                    <td>{{ $row->employee_category }}</td>
                    <td>{{ $row->employee_no }}</td>
                    <td>{{ $row->employee_name }}</td>
                    <td>{{ $formatMoney($row->salary_amount) }}</td>
                    <td>{{ $row->bank_name }}</td>
                    <td>{{ $row->bank_branch }}</td>
                    <td>{{ $row->account_no }}</td>
                    <td>{{ $row->ifsc_code }}</td>
                    <td>{{ $row->account_type }}</td>
                    <td>{{ $formatDateTime($row->updated_at) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
