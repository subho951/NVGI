@php
    $formatDate = function ($value) {
        return $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : '--';
    };
    $formatCount = function ($value) {
        $formatted = number_format((float) $value, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    };
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Application Submitted</title>
</head>
<body style="margin:0; padding:0; background:#eef4f8; font-family:Arial, Helvetica, sans-serif; color:#173145;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef4f8; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="680" cellpadding="0" cellspacing="0" style="max-width:680px; width:100%; background:#ffffff; border-radius:18px; overflow:hidden; border:1px solid #dce7ef; box-shadow:0 18px 45px rgba(23,49,69,.12);">
                    <tr>
                        <td style="background:#173145; padding:28px 34px;">
                            <img src="{{ $logoUrl }}" alt="NVGI" style="height:56px; display:block; margin-bottom:18px;">
                            <div style="color:#d6b86a; font-size:13px; font-weight:700; letter-spacing:2px; text-transform:uppercase;">Leave Desk</div>
                            <h1 style="margin:8px 0 0; color:#ffffff; font-size:28px; line-height:1.2;">Application Submitted</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 34px;">
                            <p style="margin:0 0 14px; font-size:16px; line-height:1.65;">Dear {{ $application->employee_name ?: 'Employee' }},</p>
                            <p style="margin:0 0 22px; color:#526a7f; font-size:15px; line-height:1.7;">Your leave application has been submitted successfully and is currently awaiting review.</p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #dce7ef; border-radius:14px; overflow:hidden;">
                                <tr>
                                    <td style="padding:14px 16px; background:#f7fafc; color:#63798d; font-size:12px; font-weight:700; text-transform:uppercase;">Employee</td>
                                    <td style="padding:14px 16px; background:#f7fafc; font-size:14px; font-weight:700;">{{ $application->employee_no }} - {{ $application->employee_name }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px; color:#63798d; font-size:12px; font-weight:700; text-transform:uppercase;">Leave Type</td>
                                    <td style="padding:14px 16px; font-size:14px;">{{ $application->leave_type_name ?: 'CL' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px; background:#f7fafc; color:#63798d; font-size:12px; font-weight:700; text-transform:uppercase;">Leave Period</td>
                                    <td style="padding:14px 16px; background:#f7fafc; font-size:14px;">{{ $formatDate($application->leave_from_date) }} to {{ $formatDate($application->leave_to_date) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px; color:#63798d; font-size:12px; font-weight:700; text-transform:uppercase;">No Of Days</td>
                                    <td style="padding:14px 16px; font-size:14px;">{{ $formatCount($application->no_of_days) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px; background:#f7fafc; color:#63798d; font-size:12px; font-weight:700; text-transform:uppercase;">Apply Date</td>
                                    <td style="padding:14px 16px; background:#f7fafc; font-size:14px;">{{ $formatDate($application->apply_date) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px; color:#63798d; font-size:12px; font-weight:700; text-transform:uppercase;">Status</td>
                                    <td style="padding:14px 16px; font-size:14px;"><span style="display:inline-block; padding:7px 12px; border-radius:999px; background:#fff4d6; color:#8a6500; font-weight:700;">Pending</span></td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px; background:#f7fafc; color:#63798d; font-size:12px; font-weight:700; text-transform:uppercase;">Remarks</td>
                                    <td style="padding:14px 16px; background:#f7fafc; font-size:14px;">{{ $application->remarks ?: '--' }}</td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0; color:#526a7f; font-size:14px; line-height:1.7;">You will receive another email once the application is approved or rejected.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f7fafc; padding:18px 34px; color:#7a8fa1; font-size:12px; line-height:1.6;">
                            This is an automated notification from New Vedant Group of Institutions.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
