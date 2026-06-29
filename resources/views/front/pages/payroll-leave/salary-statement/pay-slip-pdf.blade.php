<?php
$monthName = $monthOptions[(int) $salaryGeneration->salary_month] ?? $salaryGeneration->salary_month;
$employeeCategory = strtoupper(trim((string) $salaryGeneration->employee_category));
$isTsaTeacher = $employeeCategory === 'TSA TEACHER';
$headDetails = json_decode((string) $salaryGeneration->salary_head_details, true);
$headDetails = collect(is_array($headDetails) ? $headDetails : [])
    ->filter(function ($head) {
        return (int) ($head['is_payslip_show'] ?? 1) === 1;
    })
    ->values();
$earnings = $headDetails->filter(function ($head) {
    return strtoupper((string) ($head['salary_head_type'] ?? '')) === \App\Models\SalaryHead::TYPE_EARNING;
})->values();
$deductions = $headDetails->filter(function ($head) {
    return strtoupper((string) ($head['salary_head_type'] ?? '')) === \App\Models\SalaryHead::TYPE_DEDUCTION;
})->values();
$leaveDetails = json_decode((string) $salaryGeneration->leave_details, true);
if (! is_array($leaveDetails) || empty($leaveDetails)) {
    $leaveDetails = [
        'CL' => ['alloted' => $salaryGeneration->cl_alloted, 'balance' => $salaryGeneration->cl_balance],
        'ML' => ['alloted' => $salaryGeneration->ml_alloted, 'balance' => $salaryGeneration->ml_balance],
    ];
}
$formatMoney = function ($value) {
    return $value === null ? '0' : number_format(round((float) $value), 0);
};
$formatDecimal = function ($value) {
    $formatted = number_format((float) $value, 2, '.', '');
    return rtrim(rtrim($formatted, '0'), '.');
};
$formatDate = function ($value) {
    return $value ? date('d-m-Y', strtotime($value)) : '--';
};
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 28px;
        }

        body {
            color: #173145;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
        }

        .shell {
            border: 1px solid #b8c7d3;
            padding: 18px;
        }

        h1 {
            font-size: 22px;
            margin: 0;
            text-align: center;
            text-transform: uppercase;
        }

        .period {
            color: #52677a;
            margin: 4px 0 16px;
            text-align: center;
        }

        .meta {
            border-collapse: collapse;
            margin-bottom: 14px;
            width: 100%;
        }

        .meta td {
            border: 1px solid #d7e1e8;
            padding: 7px;
            vertical-align: top;
            width: 25%;
        }

        .label {
            color: #52677a;
            display: block;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .value {
            display: block;
            font-size: 12px;
            font-weight: bold;
            margin-top: 2px;
        }

        .amount-table {
            border-collapse: collapse;
            width: 100%;
        }

        .amount-table th,
        .amount-table td {
            border: 1px solid #b8c7d3;
            padding: 7px;
            vertical-align: top;
        }

        .amount-table th {
            background: #e9f4f6;
            text-align: left;
        }

        .amount {
            font-weight: bold;
            text-align: right;
            white-space: nowrap;
        }

        .summary {
            background: #f6fafc;
            font-weight: bold;
        }

        .net {
            background: #e9f8ef;
            color: #1f7a3d;
            font-size: 14px;
        }

        .notes {
            color: #52677a;
            font-size: 10px;
            margin-top: 14px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="shell">
        <h1>New Vendant Group Of Institution</h1>
        <h1>Salary Pay Slip</h1>
        <div class="period">{{ $monthName }} {{ $salaryGeneration->salary_year }} 
            <!-- | {{ $salaryGeneration->branch_name }} | {{ $salaryGeneration->employee_category }} -->
        </div>

        <table class="meta">
            <tr>
                <td><span class="label">Employee Code</span><span class="value">{{ $salaryGeneration->employee_no ?: '--' }}</span></td>
                <td><span class="label">Employee Name</span><span class="value">{{ $salaryGeneration->employee_name ?: '--' }}</span></td>
                <td><span class="label">DOJ</span><span class="value">{{ $formatDate($salaryGeneration->doj) }}</span></td>
                <td><span class="label">Gross Salary</span><span class="value">{{ $formatMoney($salaryGeneration->gross_salary) }}</span></td>
            </tr>
            <tr>
                <td><span class="label">Bank Name</span><span class="value">{{ $statement->bank_name ?: '--' }}</span></td>
                <td><span class="label">Account No.</span><span class="value">{{ $statement->account_no ?: '--' }}</span></td>
                <td><span class="label">IFSC Code</span><span class="value">{{ $statement->ifsc_code ?: '--' }}</span></td>
                <td><span class="label">Account Type</span><span class="value">{{ $statement->account_type ?: '--' }}</span></td>
            </tr>
        </table>

        <table class="amount-table">
            <thead>
                <tr>
                    <th>Earnings</th>
                    <th>Amount</th>
                    <th>Deductions</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $maxRows = max($earnings->count(), $deductions->count(), 1);
                @endphp
                @for($index = 0; $index < $maxRows; $index++)
                    @php
                        $earning = $earnings->get($index);
                        $deduction = $deductions->get($index);
                    @endphp
                    <tr>
                        <td>{{ is_array($earning) ? ($earning['salary_head_name'] ?? '') : '' }}</td>
                        <td class="amount">{{ is_array($earning) ? $formatMoney($earning['calculated_amount'] ?? 0) : '' }}</td>
                        <td>{{ is_array($deduction) ? ($deduction['salary_head_name'] ?? '') : '' }}</td>
                        <td class="amount">{{ is_array($deduction) ? $formatMoney($deduction['calculated_amount'] ?? 0) : '' }}</td>
                    </tr>
                @endfor
                <tr class="summary">
                    <td>Earning Total</td>
                    <td class="amount">{{ $formatMoney($salaryGeneration->earning_total) }}</td>
                    <td>Deduction Total</td>
                    <td class="amount">{{ $formatMoney($salaryGeneration->deduction_total) }}</td>
                </tr>
                <tr class="net">
                    <td colspan="3">Net Salary</td>
                    <td class="amount">{{ $formatMoney($salaryGeneration->net_salary) }}</td>
                </tr>
            </tbody>
        </table>

        <table class="meta" style="margin-top:14px;">
            <tr>
                <td><span class="label">Absent Count</span><span class="value">{{ $formatDecimal($salaryGeneration->absent_days) }}</span></td>
                <td><span class="label">Unpaid Absent</span><span class="value">{{ $formatDecimal($salaryGeneration->unpaid_absent_days) }}</span></td>
                <td><span class="label">Late Count</span><span class="value">{{ $formatDecimal($salaryGeneration->late_count ?? 0) }}</span></td>
                <td><span class="label">Absent Amount</span><span class="value">{{ $formatMoney($salaryGeneration->absent_amount) }}</span></td>
            </tr>
            <tr>
                @if($isTsaTeacher)
                    <td colspan="2"><span class="label">Assigned Hour</span><span class="value">{{ $formatDecimal($salaryGeneration->assigned_hours) }}</span></td>
                    <td colspan="2"><span class="label">Attendance Hour</span><span class="value">{{ $formatDecimal($salaryGeneration->attendance_hours) }}</span></td>
                @else
                    <td colspan="2"><span class="label">CL Balance</span><span class="value">{{ $formatDecimal($leaveDetails['CL']['balance'] ?? $salaryGeneration->cl_balance) }}</span></td>
                    <td colspan="2"><span class="label">ML Balance</span><span class="value">{{ $formatDecimal($leaveDetails['ML']['balance'] ?? $salaryGeneration->ml_balance) }}</span></td>
                @endif
            </tr>
        </table>

        <div class="notes">This is a system generated pay slip.</div>
    </div>
</body>
</html>
