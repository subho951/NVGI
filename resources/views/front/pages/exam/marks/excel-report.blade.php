<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1f2937;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #9ca3af;
            padding: 7px;
            vertical-align: middle;
        }

        .report-title {
            background: #143b5f;
            color: #ffffff;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .report-context {
            background: #e9f3fb;
            font-weight: bold;
        }

        .column-title {
            background: #dcebf7;
            font-weight: bold;
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .text-cell {
            mso-number-format: "\@";
        }
    </style>
</head>
<body>
    @php
        $subjectColumnCount = $exams->sum(function ($exam) {
            return ($exam->fullMarks) ? $exam->fullMarks->count() : 0;
        });
        $totalColumns = $subjectColumnCount + 9;
    @endphp
    <table>
        <tr>
            <th class="report-title" colspan="{{ $totalColumns }}">Exam Subject Marks Report</th>
        </tr>
        <tr>
            <td class="report-context" colspan="2">Unit</td>
            <td colspan="2">{{ $context['unit_name'] !== '' ? $context['unit_name'] : '-' }}</td>
            <td class="report-context" colspan="2">Branch</td>
            <td colspan="{{ max(3, $totalColumns - 6) }}">{{ $context['branch_name'] !== '' ? $context['branch_name'] : '-' }}</td>
        </tr>
        <tr>
            <td class="report-context" colspan="2">Class</td>
            <td colspan="2">{{ $context['class_name'] !== '' ? $context['class_name'] : '-' }}</td>
            <td class="report-context" colspan="2">Session</td>
            <td colspan="{{ max(3, $totalColumns - 6) }}">{{ $context['session_name'] !== '' ? $context['session_name'] : '-' }}</td>
        </tr>
        <tr>
            <td class="report-context" colspan="2">Generated At</td>
            <td colspan="{{ $totalColumns - 2 }}">{{ $generated_at }}</td>
        </tr>
        <tr>
            <th class="column-title">Sl.</th>
            <th class="column-title">Student ID</th>
            <th class="column-title">Student Name</th>
            <th class="column-title">Father Mobile</th>
            @foreach($exams as $exam)
                @foreach($exam->fullMarks as $fullMark)
                    <th class="column-title">
                        {{ $exam->name }}
                        <br>
                        {{ ($fullMark->subject) ? $fullMark->subject->name : 'Subject' }}
                        <br>
                        Full Marks: {{ $fullMark ? $fullMark->full_marks : 0 }}
                    </th>
                @endforeach
            @endforeach
            <th class="column-title">Total Obtained</th>
            <th class="column-title">Assigned Full Marks</th>
            <th class="column-title">Entered Full Marks</th>
            <th class="column-title">Overall %</th>
            <th class="column-title">Status</th>
        </tr>
        @forelse($rows as $row)
            @php
                $student = $row['student'];
                $studentName = trim((string) $student->full_name);
                $fatherMobile = trim((string) $student->father_mobile);
            @endphp
            <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td class="text-cell">{{ $student->student_id_serial }}</td>
                <td>{{ $studentName !== '' && strcasecmp($studentName, 'No Name') !== 0 ? $studentName : '-' }}</td>
                <td class="text-cell">{{ $fatherMobile !== '' ? $fatherMobile : '-' }}</td>
                @foreach($row['exam_rows'] as $examRow)
                    @foreach($examRow['subject_rows'] as $subjectRow)
                        <td class="center">
                            {{ $subjectRow['obtain_marks_label'] }} / {{ $subjectRow['full_marks_label'] }}
                            @if($subjectRow['has_value'])
                                <br>{{ $subjectRow['percentage_label'] }}%
                            @else
                                <br>Pending
                            @endif
                        </td>
                    @endforeach
                @endforeach
                <td class="center">{{ $row['total_obtained_label'] }}</td>
                <td class="center">{{ $row['configured_full_marks_label'] }}</td>
                <td class="center">{{ $row['entered_full_marks_label'] }}</td>
                <td class="center">{{ $row['entered_count'] > 0 ? $row['overall_percentage_label'] . '%' : '-' }}</td>
                <td class="center">{{ $row['status'] }}</td>
            </tr>
        @empty
            <tr>
                <td class="center" colspan="{{ $totalColumns }}">No active students found for the selected report filters.</td>
            </tr>
        @endforelse
    </table>
</body>
</html>
