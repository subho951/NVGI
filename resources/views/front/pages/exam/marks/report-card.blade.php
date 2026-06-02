@php
    use App\Helpers\Helper;

    $student = $report_row['student'];
    $siteName = trim((string) Helper::getSettingValue('site_name'));
    $siteLogo = trim((string) Helper::getSettingValue('site_logo'));
    $logoUrl = $siteLogo !== ''
        ? config('constants.app_url') . config('constants.uploads_url_path') . $siteLogo
        : config('constants.no_image');
    $studentPhoto = trim((string) $student->photo) !== ''
        ? config('constants.app_url') . config('constants.uploads_url_path') . $student->photo
        : config('constants.no_image_avatar');
    $studentName = trim((string) $student->full_name);
    $fatherName = trim((string) $student->father_name);
    $fatherMobile = trim((string) $student->father_mobile);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 24px;
            background: #eef3f7;
            color: #183047;
            font-family: Arial, sans-serif;
        }

        .report-toolbar {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 16px;
        }

        .report-toolbar button {
            border: 0;
            border-radius: 8px;
            padding: 10px 16px;
            background: #143b5f;
            color: #ffffff;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
        }

        .report-toolbar .secondary {
            background: #64748b;
        }

        .report-sheet {
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid #cbd8e4;
            background: #ffffff;
            box-shadow: 0 16px 36px rgba(17, 34, 52, 0.12);
        }

        .report-head {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 22px 24px;
            border-bottom: 4px solid #1a8d76;
            background: linear-gradient(135deg, #102d49 0%, #145b69 100%);
            color: #ffffff;
        }

        .site-logo {
            width: 78px;
            height: 78px;
            border-radius: 12px;
            background: #ffffff;
            object-fit: contain;
            padding: 5px;
        }

        .report-head h1 {
            margin: 0;
            font-size: 25px;
        }

        .report-head p {
            margin: 6px 0 0;
            color: rgba(255, 255, 255, 0.86);
            font-size: 14px;
        }

        .report-body {
            padding: 22px 24px 26px;
        }

        .student-block {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 15px;
            border: 1px solid #d9e4ed;
            border-radius: 10px;
            background: #f8fbfd;
        }

        .student-photo {
            width: 92px;
            height: 104px;
            border: 2px solid #d4e0eb;
            border-radius: 9px;
            background: #ffffff;
            object-fit: cover;
        }

        .student-info {
            display: grid;
            flex: 1;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px 18px;
        }

        .info-item span {
            display: block;
            margin-bottom: 3px;
            color: #667b8e;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .info-item strong {
            color: #183047;
            font-size: 14px;
        }

        .section-title {
            margin: 20px 0 9px;
            color: #143b5f;
            font-size: 15px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #d5e0e9;
            padding: 9px 8px;
            font-size: 13px;
        }

        th {
            background: #143b5f;
            color: #ffffff;
            font-size: 11px;
            letter-spacing: 0.04em;
            text-align: center;
            text-transform: uppercase;
        }

        td.center {
            text-align: center;
        }

        .status {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-entered,
        .status-completed {
            background: #d9f4e6;
            color: #14663e;
        }

        .status-pending {
            background: #edf1f5;
            color: #64748b;
        }

        .status-partial {
            background: #fff1cc;
            color: #8a5a00;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 14px;
        }

        .summary-item {
            border: 1px solid #d9e4ed;
            border-radius: 9px;
            background: #f8fbfd;
            padding: 11px;
        }

        .summary-item span {
            display: block;
            color: #667b8e;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .summary-item strong {
            display: block;
            margin-top: 5px;
            color: #143b5f;
            font-size: 17px;
        }

        .report-note {
            margin: 16px 0 0;
            color: #65798c;
            font-size: 12px;
            line-height: 1.5;
        }

        .report-footer {
            margin-top: 28px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            color: #65798c;
            font-size: 12px;
        }

        .signature {
            min-width: 180px;
            padding-top: 9px;
            border-top: 1px solid #94a3b8;
            text-align: center;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }

            body {
                padding: 0;
                background: #ffffff;
            }

            .report-toolbar {
                display: none;
            }

            .report-sheet {
                max-width: none;
                border: 0;
                box-shadow: none;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 10px;
            }

            .report-body {
                padding: 15px;
            }

            .student-block {
                flex-direction: column;
            }

            .student-info,
            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="report-toolbar">
        <button type="button" class="secondary" onclick="window.close()">Close</button>
        <button type="button" onclick="window.print()">Print Report Card</button>
    </div>

    <div class="report-sheet">
        <div class="report-head">
            <img src="{{ $logoUrl }}" alt="{{ $siteName !== '' ? $siteName : 'Site logo' }}" class="site-logo">
            <div>
                <h1>{{ $siteName !== '' ? $siteName : 'Student Report Card' }}</h1>
                <p>Student Exam Wise Marks Report Card</p>
            </div>
        </div>

        <div class="report-body">
            <div class="student-block">
                <img src="{{ $studentPhoto }}" alt="{{ $studentName !== '' ? $studentName : 'Student' }}" class="student-photo">
                <div class="student-info">
                    <div class="info-item">
                        <span>Student Name</span>
                        <strong>{{ $studentName !== '' && strcasecmp($studentName, 'No Name') !== 0 ? $studentName : '-' }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Student ID</span>
                        <strong>{{ $student->student_id_serial }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Father Name</span>
                        <strong>{{ $fatherName !== '' && strcasecmp($fatherName, 'No Name') !== 0 ? $fatherName : '-' }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Father Mobile</span>
                        <strong>{{ $fatherMobile !== '' ? $fatherMobile : '-' }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Unit / Branch</span>
                        <strong>{{ $context['unit_name'] !== '' ? $context['unit_name'] : '-' }} / {{ $context['branch_name'] !== '' ? $context['branch_name'] : '-' }}</strong>
                    </div>
                    <div class="info-item">
                        <span>Class / Session</span>
                        <strong>{{ $context['class_name'] !== '' ? $context['class_name'] : '-' }} / {{ $context['session_name'] !== '' ? $context['session_name'] : '-' }}</strong>
                    </div>
                </div>
            </div>

            <h2 class="section-title">Exam Wise Marks</h2>
            <table>
                <thead>
                    <tr>
                        <th style="width: 48px;">Sl.</th>
                        <th>Exam</th>
                        <th style="width: 110px;">Full Marks</th>
                        <th style="width: 120px;">Obtained Marks</th>
                        <th style="width: 95px;">Marks %</th>
                        <th style="width: 95px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report_row['exam_rows'] as $examRow)
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $examRow['exam']->name }}</td>
                            <td class="center">{{ $examRow['full_marks_label'] }}</td>
                            <td class="center">{{ $examRow['obtain_marks_label'] }}</td>
                            <td class="center">{{ $examRow['has_value'] ? $examRow['percentage_label'] . '%' : '-' }}</td>
                            <td class="center">
                                <span class="status {{ $examRow['has_value'] ? 'status-entered' : 'status-pending' }}">
                                    {{ $examRow['has_value'] ? 'Entered' : 'Pending' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="center">No exam marks have been entered for this student.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="summary-grid">
                <div class="summary-item">
                    <span>Exams With Entered Marks</span>
                    <strong>{{ $report_row['exam_count'] }}</strong>
                </div>
                <div class="summary-item">
                    <span>Entered Marks Records</span>
                    <strong>{{ $report_row['entered_count'] }}</strong>
                </div>
                <div class="summary-item">
                    <span>Report Status</span>
                    <strong><span class="status status-{{ strtolower($report_row['status']) }}">{{ $report_row['status'] }}</span></strong>
                </div>
                <div class="summary-item">
                    <span>Total Obtained Marks</span>
                    <strong>{{ $report_row['total_obtained_label'] }}</strong>
                </div>
                <div class="summary-item">
                    <span>Total Full Marks</span>
                    <strong>{{ $report_row['entered_full_marks_label'] }}</strong>
                </div>
                <div class="summary-item">
                    <span>Overall Percentage</span>
                    <strong>{{ $report_row['entered_count'] > 0 ? $report_row['overall_percentage_label'] . '%' : '-' }}</strong>
                </div>
            </div>

            <p class="report-note">
                Only exams with entered marks are included in this report card.
            </p>

            <div class="report-footer">
                <span>Generated: {{ $generated_at }}</span>
                <span class="signature">Authorized Signature</span>
            </div>
        </div>
    </div>
</body>
</html>
