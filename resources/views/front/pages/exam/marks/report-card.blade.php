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
            border-bottom: 4px solid #6E260E;
            background: linear-gradient(135deg, #6E260E 0%, #7B3F00 100%);
            color: #ffffff;
        }

        .site-logo {
            width: 78px;
            height: 78px;
            border-radius: 4px;
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
			border: 1px solid #793c01;
			/*border-radius: 10px;*/
			background: #f7e2cd;
		}

        .student-photo {
            width: 92px;
            height: 104px;
            border: 2px solid #993300;
            /*border-radius: 9px;*/
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
            color: #7a3e01;
            font-size: 11px;
            font-weight: 700;
            /*letter-spacing: 0.05em;*/
            text-transform: uppercase;
        }

        .info-item strong {
            color: #702a0c;
            font-size: 11px;
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
            padding: 4px 8px;
            font-size: 13px;
        }

        th {
            background: #6e260e;
            color: #ffffff;
            font-size: 11px;
            letter-spacing: 0.04em;
            text-align: center;
            text-transform: uppercase;
        }

        td.center {
            text-align: center;
        }

        .exam-section {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .exam-title-row td {
            height: 34px;
            background: #f7e2cd;
            color: #993300;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
        }

        .total-row td {
            background: #fff7ef;
            color: #702a0c;
            font-weight: 700;
        }

        .handwritten-grade {
            min-width: 82px;
            height: 28px;
        }

        .exam-signature-cell {
            padding: 13px 18px 9px;
            border-bottom: 2px solid #793c01;
        }

        .exam-signatures {
            display: flex;
            justify-content: space-between;
            gap: 40px;
            padding-top: 34px;
        }

        .exam-signature-line {
            width: 210px;
            padding-top: 6px;
            border-top: 1px solid #702a0c;
            color: #702a0c;
            font-size: 11px;
            font-weight: 700;
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
            background: #f7e2cd;
            color: #993300;
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

            .exam-section {
                break-inside: avoid;
                page-break-inside: avoid;
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

            .exam-signatures {
                gap: 20px;
            }

            .exam-signature-line {
                width: 45%;
            }
        }
    </style>
</head>
<body>
    <div class="report-toolbar">
        <button type="button" class="secondary" onClick="window.close()">Close</button>
        <button type="button" onClick="window.print()">Print Report Card</button>
    </div>

    <div class="report-sheet">
        <div class="report-head">
            <img src="{{ $logoUrl }}" alt="{{ $siteName !== '' ? $siteName : 'Site logo' }}" class="site-logo">
            <div>
                <!--<h1>{{ $siteName !== '' ? $siteName : 'Student Report Card' }}</h1>-->
                <h1>PROGRESS REPORT CARD</h1>
                <p>VEDANT HERITAGE SCHOOL</p>
            </div>
        </div>

        <div class="report-body">
            <div class="student-block">
                <table>
                    <tr>
                        <td style="width:10%; border:none;"><img src="{{ $studentPhoto }}" alt="{{ $studentName !== '' ? $studentName : 'Student' }}" class="student-photo"></td>
                        <td style="width:90%; border:none;">
                            <div class="student-info">
                                <div class="info-item">
                                    <span>Student Name : <strong>{{ $studentName !== '' && strcasecmp($studentName, 'No Name') !== 0 ? $studentName : '-' }}</strong></span>
                                    <span>Student ID : <strong>{{ $student->student_id_serial }}</strong></span>
                                    <span>Father's Name : <strong>{{ $fatherName !== '' && strcasecmp($fatherName, 'No Name') !== 0 ? $fatherName : '-' }}</strong></span>
                                    <span>Contact No. : <strong>{{ $fatherMobile !== '' ? $fatherMobile : '-' }}</strong></span>
                                    <span>Branch : <strong>{{ $context['branch_name'] !== '' ? $context['branch_name'] : '-' }}</strong></span>
                                    <span>Class : <strong>{{ $context['class_name'] !== '' ? $context['class_name'] : '-' }}</strong></span>
                                    <span>Session : <strong>{{ $context['session_name'] !== '' ? $context['session_name'] : '-' }}</strong></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
               <!-- <img src="{{ $studentPhoto }}" alt="{{ $studentName !== '' ? $studentName : 'Student' }}" class="student-photo">
                <div class="student-info">
                    <div class="info-item">
                        <span>Student Name : <strong>{{ $studentName !== '' && strcasecmp($studentName, 'No Name') !== 0 ? $studentName : '-' }}</strong></span>
                        <span>Student ID : <strong>{{ $student->student_id_serial }}</strong></span>
                        <span>Father's Name : <strong>{{ $fatherName !== '' && strcasecmp($fatherName, 'No Name') !== 0 ? $fatherName : '-' }}</strong></span>
                        <span>Contact No. : <strong>{{ $fatherMobile !== '' ? $fatherMobile : '-' }}</strong></span>
                        <span>Branch : <strong>{{ $context['branch_name'] !== '' ? $context['branch_name'] : '-' }}</strong></span>
                        <span>Class : <strong>{{ $context['class_name'] !== '' ? $context['class_name'] : '-' }}</strong></span>
                        <span>Session : <strong>{{ $context['session_name'] !== '' ? $context['session_name'] : '-' }}</strong></span>
                    </div>
                </div>-->
            </div>

            <!--<h2 class="section-title">Exam And Subject Wise Marks</h2>-->
            <table>
                <thead>
                    <tr>
                        <th style="width: 48px;">Sl.</th>
                        <th>Subject</th>
                        <th style="width: 110px;">Full Marks</th>
                        <th style="width: 120px;">Obtained Marks</th>
                        <th style="width: 95px;">Marks %</th>
                        <th style="width: 95px;">Grade</th>
                    </tr>
                </thead>
                @forelse($report_row['exam_rows'] as $examRow)
                    <tbody class="exam-section">
                        <tr class="exam-title-row">
                            <td colspan="6">{{ $examRow['exam']->name }}</td>
                        </tr>
                        @foreach($examRow['subject_rows'] as $subjectRow)
                            <tr>
                                <td class="center">{{ $loop->iteration }}</td>
                                <td>{{ $subjectRow['subject_name'] }}</td>
                                <td class="center">{{ $subjectRow['full_marks_label'] }}</td>
                                <td class="center">{{ $subjectRow['obtain_marks_label'] }}</td>
                                <td class="center">{{ $subjectRow['has_value'] ? $subjectRow['percentage_label'] . '%' : '-' }}</td>
                                <td class="center handwritten-grade">&nbsp;</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="2" class="center">Total</td>
                            <td class="center">{{ $examRow['configured_full_marks_label'] }}</td>
                            <td class="center">{{ $examRow['total_obtained_label'] }}</td>
                            <td class="center">{{ $examRow['overall_percentage_label'] }}%</td>
                            <td class="center handwritten-grade">&nbsp;</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="exam-signature-cell">
                                <div class="exam-signatures">
                                    <div class="exam-signature-line">Guardian's Signature</div>
                                    <div class="exam-signature-line">HM Signature</div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @empty
                    <tbody>
                        <tr>
                            <td colspan="6" class="center">No subject-wise exam setup is available for this student.</td>
                        </tr>
                    </tbody>
                @endforelse
            </table>

<!--<div class="summary-grid">
                <div class="summary-item">
                    <span>Assigned Exams</span>
                    <strong>{{ $report_row['exam_count'] }}</strong>
                </div>
                <div class="summary-item">
                    <span>Assigned Subjects</span>
                    <strong>{{ $report_row['subject_count'] }}</strong>
                </div>
                <div class="summary-item">
                    <span>Entered Subject Marks</span>
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
                    <span>Assigned Full Marks</span>
                    <strong>{{ $report_row['configured_full_marks_label'] }}</strong>
                </div>
                <div class="summary-item">
                    <span>Entered Full Marks</span>
                    <strong>{{ $report_row['entered_full_marks_label'] }}</strong>
                </div>
                <div class="summary-item">
                    <span>Overall Percentage</span>
                    <strong>{{ $report_row['entered_count'] > 0 ? $report_row['overall_percentage_label'] . '%' : '-' }}</strong>
                </div>
            </div>-->

        </div>
    </div>
</body>
</html>
