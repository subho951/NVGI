<?php
$leaveCodes = collect($leaveTypes ?? [])->map(function ($leaveType) {
    return strtoupper(trim((string) $leaveType->name));
})->filter()->unique()->values();

if ($leaveCodes->isEmpty()) {
    $leaveCodes = collect(['CL', 'ML']);
}

$earningSalaryHeads = collect($salaryHeads)->where('type', \App\Models\SalaryHead::TYPE_EARNING)->values();
$deductionSalaryHeads = collect($salaryHeads)->where('type', \App\Models\SalaryHead::TYPE_DEDUCTION)->values();

$formatMoney = function ($value) {
    return $value === null ? '' : number_format((float) $value, 2, '.', '');
};
$formatRoundedMoney = function ($value) {
    return $value === null ? '' : number_format(round((float) $value), 0, '.', '');
};
$formatCount = function ($value) {
    $formatted = number_format((float) $value, 2, '.', '');
    return rtrim(rtrim($formatted, '0'), '.');
};
$formatDate = function ($value) {
    return $value ? date('d-m-Y', strtotime($value)) : '';
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

        .earning {
            color: #1f7a3d;
            font-weight: bold;
        }

        .deduction {
            color: #b42318;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th colspan="{{ 21 + ($leaveCodes->count() * 2) }}">
                    Salary Generation - {{ $monthOptions[(int) $filters['month']] ?? $filters['month'] }} {{ $filters['year'] }} - {{ $filters['branch_name'] }} - {{ $filters['category'] }}
                </th>
            </tr>
            <tr>
                <th>Sl No.</th>
                <th>Month</th>
                <th>Year</th>
                <th>Branch</th>
                <th>Category</th>
                <th>Employee Code</th>
                <th>Name</th>
                <th>DOJ</th>
                <th>Gross Salary</th>
                <th>Earning Total</th>
                <th>Deduction Total</th>
                <th>Net</th>
                <th>Assigned Hour</th>
                <th>Attendance Hour</th>
                <th>Late Count</th>
                <th>Late Penalty</th>
                <th>Absent Count</th>
                <th>Unpaid Absent Days</th>
                <th>Absent Amount</th>
                @foreach($leaveCodes as $leaveCode)
                    <th>{{ $leaveCode }} Alloted</th>
                    <th>{{ $leaveCode }} Balance</th>
                @endforeach
                <th>Generated At</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
                @php
                    $headDetails = json_decode((string) $row->salary_head_details, true);
                    $headMap = collect(is_array($headDetails) ? $headDetails : [])->keyBy('salary_head_id');
                    $leaveDetails = json_decode((string) $row->leave_details, true);

                    if (! is_array($leaveDetails) || empty($leaveDetails)) {
                        $leaveDetails = [
                            'CL' => ['alloted' => $row->cl_alloted, 'balance' => $row->cl_balance],
                            'ML' => ['alloted' => $row->ml_alloted, 'balance' => $row->ml_balance],
                        ];
                    }
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $monthOptions[(int) $row->salary_month] ?? $row->salary_month }}</td>
                    <td>{{ $row->salary_year }}</td>
                    <td>{{ $row->branch_name }}</td>
                    <td>{{ $row->employee_category }}</td>
                    <td>{{ $row->employee_no }}</td>
                    <td>{{ $row->employee_name }}</td>
                    <td>{{ $formatDate($row->doj) }}</td>
                    <td>{{ $formatMoney($row->gross_salary) }}</td>
                    <td class="earning">
                        @foreach($earningSalaryHeads as $salaryHead)
                            @php
                                $headDetail = $headMap->get($salaryHead->id);
                                $headAmount = is_array($headDetail) ? ($headDetail['calculated_amount'] ?? null) : null;
                            @endphp
                            <div>{{ $salaryHead->name }}: {{ $formatMoney($headAmount) }}</div>
                        @endforeach
                        <div>Total: {{ $formatMoney($row->earning_total) }}</div>
                    </td>
                    <td class="deduction">
                        @foreach($deductionSalaryHeads as $salaryHead)
                            @php
                                $headDetail = $headMap->get($salaryHead->id);
                                $headAmount = is_array($headDetail) ? ($headDetail['calculated_amount'] ?? null) : null;
                            @endphp
                            <div>{{ $salaryHead->name }}: {{ $formatMoney($headAmount) }}</div>
                        @endforeach
                        <div>Total: {{ $formatMoney($row->deduction_total) }}</div>
                    </td>
                    <td>{{ $formatRoundedMoney($row->net_salary) }}</td>
                    <td>{{ $formatCount($row->assigned_hours) }}</td>
                    <td>{{ $formatCount($row->attendance_hours) }}</td>
                    <td>{{ $formatCount($row->late_count ?? 0) }}</td>
                    <td>{{ $formatCount($row->late_penalty_units ?? 0) }}</td>
                    <td>{{ $formatCount($row->absent_days) }}</td>
                    <td>{{ $formatCount($row->unpaid_absent_days) }}</td>
                    <td>{{ $formatMoney($row->absent_amount) }}</td>
                    @foreach($leaveCodes as $leaveCode)
                        @php
                            $leave = $leaveDetails[$leaveCode] ?? ['alloted' => 0, 'balance' => 0];
                        @endphp
                        <td>{{ $formatCount($leave['alloted'] ?? 0) }}</td>
                        <td>{{ $formatCount($leave['balance'] ?? 0) }}</td>
                    @endforeach
                    <td>{{ $formatDateTime($row->updated_at) }}</td>
                    <td>Generated</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
