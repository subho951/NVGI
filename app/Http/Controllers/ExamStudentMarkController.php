<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Branch;
use App\Models\Classes;
use App\Models\Exam;
use App\Models\ExamFullMark;
use App\Models\ExamStudentMark;
use App\Models\Session;
use App\Models\Student;
use App\Models\Unit;
use App\Services\SiteAuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ExamStudentMarkController extends Controller
{
    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = [
            'title'            => 'Exam Marks',
            'controller'       => 'ExamStudentMarkController',
            'controller_route' => 'exam/marks',
            'primary_key'      => 'id',
        ];

        $this->siteAuthService = new SiteAuthService();
    }

    private function getFinancialSessionData($sessionId = null)
    {
        $session = null;

        if ($sessionId !== null && $sessionId !== '') {
            $session = Session::select('id', 'name')
                                ->where('id', '=', (int) $sessionId)
                                ->first();
        }

        $currentYear  = (int) Carbon::now()->year;
        $currentMonth = (int) Carbon::now()->month;
        $startYear    = (($currentMonth >= 4) ? $currentYear : ($currentYear - 1));
        $endYear      = $startYear + 1;

        if (!$session) {
            $activeSessions = Session::select('id', 'name')
                                        ->where('status', '=', 1)
                                        ->orderBy('name', 'ASC')
                                        ->get();

            foreach ($activeSessions as $sessionItem) {
                $sessionName = trim((string) $sessionItem->name);
                if ($sessionName !== '' && preg_match('/(\d{4})\D+(\d{4})/', $sessionName, $matches)) {
                    if ((int) $matches[1] === $startYear && (int) $matches[2] === $endYear) {
                        $session = $sessionItem;
                        break;
                    }
                }
            }

            if (!$session && $activeSessions->count() > 0) {
                $session = $activeSessions->first();
            }
        }

        if ($session) {
            $sessionName = trim((string) $session->name);
            if ($sessionName !== '' && preg_match('/(\d{4})\D+(\d{4})/', $sessionName, $matches)) {
                $startYear = (int) $matches[1];
                $endYear   = (int) $matches[2];

                return [
                    'session_id'   => (int) $session->id,
                    'session_name' => $sessionName,
                    'start_year'   => $startYear,
                    'end_year'     => $endYear,
                ];
            }
        }

        return [
            'session_id'   => (($session) ? (int) $session->id : 0),
            'session_name' => (($session && trim((string) $session->name) != '') ? trim((string) $session->name) : ($startYear . '-' . $endYear)),
            'start_year'   => $startYear,
            'end_year'     => $endYear,
        ];
    }

    private function getUnitOptions()
    {
        return Unit::select('id', 'name')
                    ->where('status', '=', 1)
                    ->orderBy('name', 'ASC')
                    ->get();
    }

    private function getBranchOptions()
    {
        return Branch::select('id', 'name', 'unit_id')
                    ->where('status', '=', 1)
                    ->orderBy('name', 'ASC')
                    ->get();
    }

    private function getClassOptions()
    {
        return Classes::select('id', 'name', 'unit_id')
                    ->where('status', '=', 1)
                    ->orderBy('unit_id', 'ASC')
                    ->orderBy('name', 'ASC')
                    ->get();
    }

    private function getExamOptions(array $selectedIds = [])
    {
        $exams = Exam::select('id', 'name')
                    ->where('status', '=', 1)
                    ->orderBy('name', 'ASC')
                    ->get();

        if (!empty($selectedIds)) {
            $selectedExams = Exam::withTrashed()
                                ->select('id', 'name')
                                ->whereIn('id', $selectedIds)
                                ->orderBy('name', 'ASC')
                                ->get();

            $exams = $selectedExams->merge($exams)
                                ->unique('id')
                                ->sortBy('name')
                                ->values();
        }

        return $exams;
    }

    private function getStudentClassFieldByUnit($unitId)
    {
        if ((int) $unitId === 1) {
            return 'vhs_class_id';
        }

        if ((int) $unitId === 2) {
            return 'tsa_class_id';
        }

        return null;
    }

    private function getExamSubjectRows(Exam $exam, int $unitId, int $classId)
    {
        return $exam->fullMarks->filter(function ($markRow) use ($unitId, $classId) {
            return ((int) $markRow->unit_id === (int) $unitId
                && (int) $markRow->class_id === (int) $classId
                && (int) $markRow->subject_id > 0
                && (int) $markRow->status === 1);
        })->values();
    }

    private function formatMarksDisplay($value): string
    {
        $formatted = number_format((float) $value, 2, '.', '');
        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function buildStudentQuery(int $unitId, int $branchId, int $classId, int $sessionId)
    {
        $query = Student::select(
                        'students.id',
                        'students.student_id_serial',
                        'students.full_name',
                        'students.photo',
                        'students.father_name',
                        'students.father_mobile',
                        'students.unit_id',
                        'students.branch_id',
                        'students.session_id',
                        'students.vhs_class_id',
                        'students.tsa_class_id',
                        'units.name as unit_name',
                        'branches.name as branch_name',
                        DB::raw('COALESCE(vhs_classes.name, tsa_classes.name) as class_name')
                    )
                    ->leftJoin('units', 'units.id', '=', 'students.unit_id')
                    ->leftJoin('branches', 'branches.id', '=', 'students.branch_id')
                    ->leftJoin('classes as vhs_classes', 'vhs_classes.id', '=', 'students.vhs_class_id')
                    ->leftJoin('classes as tsa_classes', 'tsa_classes.id', '=', 'students.tsa_class_id')
                    ->where('students.status', '=', 1)
                    ->where('students.unit_id', '=', $unitId)
                    ->where('students.branch_id', '=', $branchId)
                    ->where('students.session_id', '=', $sessionId);

        $classField = $this->getStudentClassFieldByUnit($unitId);
        if ($classField !== null) {
            $query->where('students.' . $classField, '=', $classId);
        } else {
            $query->where(function ($classQuery) use ($classId) {
                $classQuery->where('students.vhs_class_id', '=', $classId)
                            ->orWhere('students.tsa_class_id', '=', $classId);
            });
        }

        return $query;
    }

    private function buildExamSections($students, $exams, $existingMarks, int $unitId, int $classId)
    {
        $examSections = [];
        $existingMarksByExam = $existingMarks->groupBy('exam_id')->map(function ($examGroup) {
            return $examGroup->groupBy('subject_id')->map(function ($subjectGroup) {
                return $subjectGroup->keyBy('student_id');
            });
        });

        foreach ($exams as $exam) {
            $subjectRows = $this->getExamSubjectRows($exam, $unitId, $classId);
            $marksForExam = $existingMarksByExam->get($exam->id, collect());
            $rows = [];
            $enteredCount = 0;
            $configuredFullMarks = 0;

            foreach ($subjectRows as $subjectRow) {
                $configuredFullMarks += (float) $subjectRow->full_marks;
            }

            foreach ($students as $student) {
                $subjectMarks = [];
                $studentEnteredCount = 0;

                foreach ($subjectRows as $subjectRow) {
                    $subjectId = (int) $subjectRow->subject_id;
                    $subject = $subjectRow->subject;
                    $fullMarks = (float) $subjectRow->full_marks;
                    $marksForSubject = $marksForExam->get($subjectId, collect());
                    $mark = $marksForSubject->get($student->id);
                    $obtainMarks = '';
                    $hasValue = false;

                    if ($mark && $mark->obtain_marks !== null && $mark->obtain_marks !== '') {
                        $obtainMarks = $this->formatMarksDisplay($mark->obtain_marks);
                        $hasValue = true;
                    }

                    $percentage = '';
                    if ($hasValue) {
                        $percentage = (($fullMarks > 0)
                            ? number_format(((float) $obtainMarks / $fullMarks) * 100, 2, '.', '')
                            : '0.00');
                    }

                    if ($hasValue) {
                        $enteredCount++;
                        $studentEnteredCount++;
                    }

                    $subjectMarks[] = [
                        'subject'          => $subject,
                        'subject_id'       => $subjectId,
                        'subject_name'     => (($subject) ? $subject->name : 'Subject'),
                        'mark'             => $mark,
                        'mark_id'          => (($mark) ? (int) $mark->id : 0),
                        'obtain_marks'     => $obtainMarks,
                        'percentage'       => $percentage,
                        'has_value'        => $hasValue,
                        'full_marks'       => $fullMarks,
                        'full_marks_label' => $this->formatMarksDisplay($fullMarks),
                    ];
                }

                $rows[] = [
                    'student'        => $student,
                    'subject_marks'  => $subjectMarks,
                    'entered_count'  => $studentEnteredCount,
                    'pending_count'  => max($subjectRows->count() - $studentEnteredCount, 0),
                ];
            }

            $entryCount = $students->count() * $subjectRows->count();

            $examSections[] = [
                'exam'                    => $exam,
                'subject_rows'            => $subjectRows,
                'subject_count'           => $subjectRows->count(),
                'configured_full_marks'   => $configuredFullMarks,
                'full_marks_label'        => $this->formatMarksDisplay($configuredFullMarks),
                'rows'                    => $rows,
                'student_count'           => $students->count(),
                'entry_count'             => $entryCount,
                'entered_count'           => $enteredCount,
                'pending_count'           => max($entryCount - $enteredCount, 0),
            ];
        }

        return $examSections;
    }

    private function validateReportRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_id'    => ['required', 'integer', Rule::exists('units', 'id')->where(function ($query) {
                $query->where('status', '=', 1)
                    ->whereNull('deleted_at');
            })],
            'branch_id'  => ['required', 'integer', Rule::exists('branches', 'id')->where(function ($query) {
                $query->where('status', '=', 1)
                    ->whereNull('deleted_at');
            })],
            'class_id'   => ['required', 'integer', Rule::exists('classes', 'id')->where(function ($query) {
                $query->where('status', '=', 1)
                    ->whereNull('deleted_at');
            })],
            'session_id' => ['required', 'integer', Rule::exists('sessions', 'id')->where(function ($query) {
                $query->where('status', '=', 1)
                    ->whereNull('deleted_at');
            })],
        ]);

        $validator->after(function ($validator) use ($request) {
            $unitId = (int) $request->input('unit_id');
            $branchId = (int) $request->input('branch_id');
            $classId = (int) $request->input('class_id');

            if ($unitId <= 0) {
                return;
            }

            if ($branchId > 0) {
                $branch = Branch::select('unit_id')
                                ->where('id', '=', $branchId)
                                ->where('status', '=', 1)
                                ->first();

                if ($branch && (int) $branch->unit_id !== $unitId) {
                    $validator->errors()->add('branch_id', 'Selected branch does not belong to the selected unit.');
                }
            }

            if ($classId > 0) {
                $class = Classes::select('unit_id')
                                ->where('id', '=', $classId)
                                ->where('status', '=', 1)
                                ->first();

                if ($class && (int) $class->unit_id !== $unitId) {
                    $validator->errors()->add('class_id', 'Selected class does not belong to the selected unit.');
                }
            }
        });

        return $validator;
    }

    private function getReportContext(int $unitId, int $branchId, int $classId, int $sessionId): array
    {
        $unit = Unit::select('id', 'name')->where('id', '=', $unitId)->first();
        $branch = Branch::select('id', 'name')->where('id', '=', $branchId)->first();
        $class = Classes::select('id', 'name')->where('id', '=', $classId)->first();
        $session = Session::select('id', 'name')->where('id', '=', $sessionId)->first();

        return [
            'unit_id'      => $unitId,
            'unit_name'    => (($unit) ? trim((string) $unit->name) : ''),
            'branch_id'    => $branchId,
            'branch_name'  => (($branch) ? trim((string) $branch->name) : ''),
            'class_id'     => $classId,
            'class_name'   => (($class) ? trim((string) $class->name) : ''),
            'session_id'   => $sessionId,
            'session_name' => (($session) ? trim((string) $session->name) : ''),
        ];
    }

    private function getReportExams(int $unitId, int $classId)
    {
        return Exam::with(['fullMarks' => function ($query) use ($unitId, $classId) {
                        $query->where('status', '=', 1)
                            ->where('unit_id', '=', $unitId)
                            ->where('class_id', '=', $classId)
                            ->where('subject_id', '>', 0)
                            ->with(['subject' => function ($subjectQuery) {
                                $subjectQuery->select('id', 'name');
                            }]);
                    }])
                    ->where('status', '=', 1)
                    ->whereNull('deleted_at')
                    ->whereHas('fullMarks', function ($query) use ($unitId, $classId) {
                        $query->where('status', '=', 1)
                            ->where('unit_id', '=', $unitId)
                            ->where('class_id', '=', $classId)
                            ->where('subject_id', '>', 0);
                    })
                    ->orderBy('id', 'ASC')
                    ->get();
    }

    private function getReportMarks(int $unitId, int $branchId, int $classId, int $sessionId, array $studentIds, array $examIds)
    {
        if (empty($studentIds) || empty($examIds)) {
            return collect();
        }

        return ExamStudentMark::select(
                                'id',
                                'exam_id',
                                'subject_id',
                                'student_id',
                                'full_marks',
                                'obtain_marks',
                                'marks_percentage'
                            )
                            ->where('unit_id', '=', $unitId)
                            ->where('branch_id', '=', $branchId)
                            ->where('class_id', '=', $classId)
                            ->where('session_id', '=', $sessionId)
                            ->where('status', '=', 1)
                            ->whereNull('deleted_at')
                            ->whereNotNull('obtain_marks')
                            ->whereIn('student_id', $studentIds)
                            ->whereIn('exam_id', $examIds)
                            ->get();
    }

    private function buildReportStudentRows($students, $exams, $existingMarks, int $unitId, int $classId)
    {
        $marksByStudent = $existingMarks->groupBy('student_id')->map(function ($group) {
            return $group->groupBy('exam_id')->map(function ($examGroup) {
                return $examGroup->keyBy('subject_id');
            });
        });

        return $students->map(function ($student) use ($exams, $marksByStudent, $unitId, $classId) {
            $studentMarks = $marksByStudent->get($student->id, collect());
            $totalObtained = 0;
            $enteredFullMarks = 0;
            $configuredFullMarks = 0;
            $enteredCount = 0;
            $subjectCount = 0;

            $examRows = $exams->map(function ($exam) use ($studentMarks, $unitId, $classId, &$totalObtained, &$enteredFullMarks, &$configuredFullMarks, &$enteredCount) {
                $examMarks = $studentMarks->get($exam->id, collect());
                $examFullMarks = 0;
                $examObtained = 0;
                $examEnteredCount = 0;

                $subjectRows = $this->getExamSubjectRows($exam, $unitId, $classId)->map(function ($subjectRow) use ($examMarks, &$totalObtained, &$enteredFullMarks, &$configuredFullMarks, &$enteredCount, &$examFullMarks, &$examObtained, &$examEnteredCount) {
                    $subjectId = (int) $subjectRow->subject_id;
                    $subject = $subjectRow->subject;
                    $fullMarks = (float) $subjectRow->full_marks;
                    $mark = $examMarks->get($subjectId);
                    $hasValue = ($mark && $mark->obtain_marks !== null && $mark->obtain_marks !== '');
                    $obtainMarks = (($hasValue) ? (float) $mark->obtain_marks : null);
                    $percentage = (($hasValue && $fullMarks > 0) ? (($obtainMarks / $fullMarks) * 100) : null);

                    $configuredFullMarks += $fullMarks;
                    $examFullMarks += $fullMarks;

                    if ($hasValue) {
                        $totalObtained += $obtainMarks;
                        $enteredFullMarks += $fullMarks;
                        $examObtained += $obtainMarks;
                        $examEnteredCount++;
                        $enteredCount++;
                    }

                    return [
                        'subject_id'          => $subjectId,
                        'subject'             => $subject,
                        'subject_name'        => (($subject) ? $subject->name : 'Subject'),
                        'full_marks'          => $fullMarks,
                        'full_marks_label'    => $this->formatMarksDisplay($fullMarks),
                        'obtain_marks'        => $obtainMarks,
                        'obtain_marks_label'  => (($hasValue) ? $this->formatMarksDisplay($obtainMarks) : '-'),
                        'percentage'          => $percentage,
                        'percentage_label'    => (($hasValue) ? number_format((float) $percentage, 2, '.', '') : '-'),
                        'has_value'           => $hasValue,
                    ];
                })->values();

                $examSubjectCount = $subjectRows->count();
                $examPendingCount = max($examSubjectCount - $examEnteredCount, 0);
                $examPercentage = (($examFullMarks > 0) ? (($examObtained / $examFullMarks) * 100) : 0);

                return [
                    'exam'                     => $exam,
                    'subject_rows'             => $subjectRows,
                    'subject_count'            => $examSubjectCount,
                    'entered_count'            => $examEnteredCount,
                    'pending_count'            => $examPendingCount,
                    'configured_full_marks'    => $examFullMarks,
                    'configured_full_marks_label' => $this->formatMarksDisplay($examFullMarks),
                    'total_obtained'           => $examObtained,
                    'total_obtained_label'     => $this->formatMarksDisplay($examObtained),
                    'overall_percentage'       => $examPercentage,
                    'overall_percentage_label' => number_format($examPercentage, 2, '.', ''),
                    'status'                   => (($examEnteredCount === 0) ? 'Pending' : (($examPendingCount === 0) ? 'Completed' : 'Partial')),
                ];
            })->values();

            $examCount = $examRows->count();
            $subjectCount = $examRows->sum('subject_count');
            $pendingCount = max($subjectCount - $enteredCount, 0);
            $overallPercentage = (($configuredFullMarks > 0) ? (($totalObtained / $configuredFullMarks) * 100) : 0);
            $status = (($enteredCount === 0) ? 'Pending' : (($pendingCount === 0) ? 'Completed' : 'Partial'));

            return [
                'student'                    => $student,
                'exam_rows'                  => $examRows,
                'exam_count'                 => $examCount,
                'subject_count'              => $subjectCount,
                'entered_count'              => $enteredCount,
                'pending_count'              => $pendingCount,
                'configured_full_marks'      => $configuredFullMarks,
                'configured_full_marks_label' => $this->formatMarksDisplay($configuredFullMarks),
                'entered_full_marks'         => $enteredFullMarks,
                'entered_full_marks_label'   => $this->formatMarksDisplay($enteredFullMarks),
                'total_obtained'             => $totalObtained,
                'total_obtained_label'       => $this->formatMarksDisplay($totalObtained),
                'overall_percentage'         => $overallPercentage,
                'overall_percentage_label'   => number_format($overallPercentage, 2, '.', ''),
                'status'                     => $status,
            ];
        })->values();
    }

    public function index(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'] . ' Entry';
        $page_name = 'exam.marks.index';

        $defaultSessionData = $this->getFinancialSessionData();

        $data['units'] = $this->getUnitOptions();
        $data['branches'] = $this->getBranchOptions();
        $data['classes'] = $this->getClassOptions();
        $data['sessions'] = Session::select('id', 'name')
                                    ->where('status', '=', 1)
                                    ->orderBy('name', 'ASC')
                                    ->get();
        $data['exams'] = $this->getExamOptions();

        $data['generated'] = false;
        $data['exam_sections'] = [];
        $data['selected_unit_id'] = '';
        $data['selected_branch_id'] = '';
        $data['selected_class_id'] = '';
        $data['selected_session_id'] = $defaultSessionData['session_id'];
        $data['selected_session_name'] = $defaultSessionData['session_name'];
        $data['selected_exam_ids'] = [];
        $data['selected_exam_names'] = [];
        $data['selected_exam_count'] = 0;
        $data['selected_unit_name'] = '';
        $data['selected_branch_name'] = '';
        $data['selected_class_name'] = '';
        $data['student_count'] = 0;
        $data['entered_count'] = 0;
        $data['pending_count'] = 0;

        if ($request->isMethod('post')) {
            $selectedExamIds = array_values(array_unique(array_map('intval', (array) $request->input('exam_ids', []))));
            $unitId = (int) $request->input('unit_id');
            $branchId = (int) $request->input('branch_id');
            $classId = (int) $request->input('class_id');
            $sessionId = (int) $request->input('session_id');

            $validator = Validator::make($request->all(), [
                'unit_id'     => ['required', 'integer', Rule::exists('units', 'id')->where(function ($query) {
                    $query->where('status', '=', 1)
                        ->whereNull('deleted_at');
                })],
                'branch_id'   => ['required', 'integer', Rule::exists('branches', 'id')->where(function ($query) {
                    $query->where('status', '=', 1)
                        ->whereNull('deleted_at');
                })],
                'class_id'    => ['required', 'integer', Rule::exists('classes', 'id')->where(function ($query) {
                    $query->where('status', '=', 1)
                        ->whereNull('deleted_at');
                })],
                'session_id'  => ['required', 'integer', Rule::exists('sessions', 'id')->where(function ($query) {
                    $query->where('status', '=', 1)
                        ->whereNull('deleted_at');
                })],
                'exam_ids'    => ['required', 'array', 'min:1'],
                'exam_ids.*'  => ['required', 'integer', Rule::exists('exams', 'id')->where(function ($query) {
                    $query->where('status', '=', 1)
                        ->whereNull('deleted_at');
                })],
            ]);

            $validator->after(function ($validator) use ($selectedExamIds, $unitId, $branchId, $classId) {
                if ($unitId > 0 && $branchId > 0) {
                    $branch = Branch::select('id', 'unit_id')
                                    ->where('id', '=', $branchId)
                                    ->where('status', '=', 1)
                                    ->first();

                    if (!$branch) {
                        $validator->errors()->add('branch_id', 'Selected branch is not valid.');
                    } elseif ((int) $branch->unit_id !== (int) $unitId) {
                        $validator->errors()->add('branch_id', 'Selected branch does not belong to the selected unit.');
                    }
                }

                if ($unitId > 0 && $classId > 0) {
                    $class = Classes::select('id', 'unit_id')
                                    ->where('id', '=', $classId)
                                    ->where('status', '=', 1)
                                    ->first();

                    if (!$class) {
                        $validator->errors()->add('class_id', 'Selected class is not valid.');
                    } elseif ((int) $class->unit_id !== (int) $unitId) {
                        $validator->errors()->add('class_id', 'Selected class does not belong to the selected unit.');
                    }
                }

                if (count($selectedExamIds) > 0) {
                    $exams = Exam::with(['fullMarks' => function ($query) {
                                    $query->where('status', '=', 1)
                                        ->where('subject_id', '>', 0);
                                }])
                                ->whereIn('id', $selectedExamIds)
                                ->where('status', '=', 1)
                                ->get();

                    if ($exams->count() !== count($selectedExamIds)) {
                        $validator->errors()->add('exam_ids', 'One or more selected exams are not available.');
                        return;
                    }

                    foreach ($exams as $exam) {
                        $matchingSubjectRows = $exam->fullMarks->filter(function ($fullMarkRow) use ($unitId, $classId) {
                            return ((int) $fullMarkRow->unit_id === (int) $unitId
                                && (int) $fullMarkRow->class_id === (int) $classId
                                && (int) $fullMarkRow->subject_id > 0);
                        });

                        if ($matchingSubjectRows->count() === 0) {
                            $validator->errors()->add('exam_ids', 'Exam "' . $exam->name . '" does not have subject-wise full marks for the selected unit and class.');
                        }
                    }
                }
            });

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $sessionData = $this->getFinancialSessionData($sessionId);
            $unit = Unit::select('id', 'name')->where('id', '=', $unitId)->first();
            $branch = Branch::select('id', 'name')->where('id', '=', $branchId)->first();
            $class = Classes::select('id', 'name')->where('id', '=', $classId)->first();

            $students = $this->buildStudentQuery($unitId, $branchId, $classId, $sessionId)
                            ->orderBy('students.full_name', 'ASC')
                            ->orderBy('students.id', 'ASC')
                            ->get();

            $studentIds = $students->pluck('id')->values()->all();
            $exams = Exam::with(['fullMarks' => function ($query) {
                            $query->where('status', '=', 1)
                                ->where('subject_id', '>', 0)
                                ->with(['subject' => function ($subjectQuery) {
                                    $subjectQuery->select('id', 'name');
                                }]);
                        }])
                        ->whereIn('id', $selectedExamIds)
                        ->where('status', '=', 1)
                        ->orderBy('name', 'ASC')
                        ->get();
            $subjectIds = $exams->flatMap(function ($exam) use ($unitId, $classId) {
                                return $this->getExamSubjectRows($exam, $unitId, $classId)->pluck('subject_id');
                            })
                            ->map(function ($subjectId) {
                                return (int) $subjectId;
                            })
                            ->unique()
                            ->values()
                            ->all();

            $existingMarks = ExamStudentMark::select(
                                        'id',
                                        'exam_id',
                                        'subject_id',
                                        'student_id',
                                        'obtain_marks',
                                        'marks_percentage',
                                        'full_marks',
                                        'status'
                                    )
                                    ->where('unit_id', '=', $unitId)
                                    ->where('branch_id', '=', $branchId)
                                    ->where('class_id', '=', $classId)
                                    ->where('session_id', '=', $sessionId)
                                    ->whereIn('exam_id', $selectedExamIds)
                                    ->whereIn('subject_id', $subjectIds)
                                    ->whereIn('student_id', $studentIds)
                                    ->get();

            $examSections = $this->buildExamSections($students, $exams, $existingMarks, $unitId, $classId);

            $data['generated'] = true;
            $data['selected_unit_id'] = $unitId;
            $data['selected_branch_id'] = $branchId;
            $data['selected_class_id'] = $classId;
            $data['selected_session_id'] = $sessionId;
            $data['selected_session_name'] = $sessionData['session_name'];
            $data['selected_exam_ids'] = $selectedExamIds;
            $data['selected_exam_names'] = $exams->pluck('name')->values()->all();
            $data['selected_exam_count'] = count($selectedExamIds);
            $data['selected_unit_name'] = (($unit) ? $unit->name : '');
            $data['selected_branch_name'] = (($branch) ? $branch->name : '');
            $data['selected_class_name'] = (($class) ? $class->name : '');
            $data['student_count'] = $students->count();
            $data['entered_count'] = collect($examSections)->sum('entered_count');
            $data['pending_count'] = collect($examSections)->sum('pending_count');
            $data['exam_sections'] = $examSections;
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }

    public function exportExcel(Request $request)
    {
        $validator = $this->validateReportRequest($request);
        if ($validator->fails()) {
            return redirect()->route('exam.marks.index')->withErrors($validator)->withInput();
        }

        $unitId = (int) $request->input('unit_id');
        $branchId = (int) $request->input('branch_id');
        $classId = (int) $request->input('class_id');
        $sessionId = (int) $request->input('session_id');

        $context = $this->getReportContext($unitId, $branchId, $classId, $sessionId);
        $students = $this->buildStudentQuery($unitId, $branchId, $classId, $sessionId)
                        ->orderBy('students.full_name', 'ASC')
                        ->orderBy('students.id', 'ASC')
                        ->get();
        $exams = $this->getReportExams($unitId, $classId);
        $marks = $this->getReportMarks(
            $unitId,
            $branchId,
            $classId,
            $sessionId,
            $students->pluck('id')->values()->all(),
            $exams->pluck('id')->values()->all()
        );
        $rows = $this->buildReportStudentRows($students, $exams, $marks, $unitId, $classId);

        $reportData = [
            'context'      => $context,
            'exams'        => $exams,
            'rows'         => $rows,
            'generated_at' => date('d-m-Y h:i A'),
        ];

        $safeParts = array_filter([
            $context['unit_name'],
            $context['branch_name'],
            $context['class_name'],
            $context['session_name'],
        ]);
        $safeName = preg_replace('/[^A-Za-z0-9]+/', '-', implode('-', $safeParts));
        $safeName = trim((string) $safeName, '-');
        $fileName = 'exam-marks-report-' . (($safeName !== '') ? $safeName : 'class') . '-' . date('YmdHis') . '.xls';
        $html = view('front.pages.exam.marks.excel-report', $reportData)->render();

        return response("\xEF\xBB\xBF" . $html)
                ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename=' . $fileName);
    }

    public function reportCard(Request $request, $id)
    {
        $validator = $this->validateReportRequest($request);
        if ($validator->fails()) {
            return redirect()->route('exam.marks.index')->withErrors($validator)->withInput();
        }

        $studentId = (int) Helper::decoded($id);
        $unitId = (int) $request->input('unit_id');
        $branchId = (int) $request->input('branch_id');
        $classId = (int) $request->input('class_id');
        $sessionId = (int) $request->input('session_id');

        $student = $this->buildStudentQuery($unitId, $branchId, $classId, $sessionId)
                        ->where('students.id', '=', $studentId)
                        ->first();

        if (!$student) {
            return redirect()->route('exam.marks.index')->with('error_message', 'Student not found for the selected report filters.');
        }

        $context = $this->getReportContext($unitId, $branchId, $classId, $sessionId);
        $exams = $this->getReportExams($unitId, $classId);
        $marks = $this->getReportMarks($unitId, $branchId, $classId, $sessionId, [$studentId], $exams->pluck('id')->values()->all());
        $reportRow = $this->buildReportStudentRows(collect([$student]), $exams, $marks, $unitId, $classId)->first();

        return view('front.pages.exam.marks.report-card', [
            'title'        => 'Student Report Card',
            'context'      => $context,
            'report_row'   => $reportRow,
            'generated_at' => date('d-m-Y h:i A'),
        ]);
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id'   => ['required', 'integer', Rule::exists('students', 'id')->where(function ($query) {
                $query->where('status', '=', 1)
                    ->whereNull('deleted_at');
            })],
            'exam_id'      => ['required', 'integer', Rule::exists('exams', 'id')->where(function ($query) {
                $query->where('status', '=', 1)
                    ->whereNull('deleted_at');
            })],
            'unit_id'      => ['required', 'integer', Rule::exists('units', 'id')->where(function ($query) {
                $query->where('status', '=', 1)
                    ->whereNull('deleted_at');
            })],
            'branch_id'    => ['required', 'integer', Rule::exists('branches', 'id')->where(function ($query) {
                $query->where('status', '=', 1)
                    ->whereNull('deleted_at');
            })],
            'class_id'     => ['required', 'integer', Rule::exists('classes', 'id')->where(function ($query) {
                $query->where('status', '=', 1)
                    ->whereNull('deleted_at');
            })],
            'session_id'   => ['required', 'integer', Rule::exists('sessions', 'id')->where(function ($query) {
                $query->where('status', '=', 1)
                    ->whereNull('deleted_at');
            })],
            'subject_id'   => ['required', 'integer', Rule::exists('subjects', 'id')->where(function ($query) {
                $query->where('status', '=', 1)
                    ->whereNull('deleted_at');
            })],
            'obtain_marks' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $unitId = (int) $request->input('unit_id');
        $branchId = (int) $request->input('branch_id');
        $classId = (int) $request->input('class_id');
        $sessionId = (int) $request->input('session_id');
        $studentId = (int) $request->input('student_id');
        $examId = (int) $request->input('exam_id');
        $subjectId = (int) $request->input('subject_id');
        $obtainMarks = (float) $request->input('obtain_marks');

        $student = $this->buildStudentQuery($unitId, $branchId, $classId, $sessionId)
                        ->where('students.id', '=', $studentId)
                        ->first();

        if (!$student) {
            return response()->json([
                'status'  => false,
                'message' => 'Selected student is not available for the selected filters.',
            ], 404);
        }

        $exam = Exam::with(['fullMarks' => function ($query) {
                        $query->where('status', '=', 1)
                            ->where('subject_id', '>', 0)
                            ->with(['subject' => function ($subjectQuery) {
                                $subjectQuery->select('id', 'name');
                            }]);
                    }])
                    ->where('id', '=', $examId)
                    ->where('status', '=', 1)
                    ->first();

        if (!$exam) {
            return response()->json([
                'status'  => false,
                'message' => 'Selected exam is not available.',
            ], 404);
        }

        $fullMarkRow = $exam->fullMarks->first(function ($markRow) use ($unitId, $classId, $subjectId) {
            return ((int) $markRow->unit_id === (int) $unitId
                && (int) $markRow->class_id === (int) $classId
                && (int) $markRow->subject_id === (int) $subjectId);
        });

        if (!$fullMarkRow) {
            return response()->json([
                'status'  => false,
                'message' => 'Subject-wise full marks setup not found for the selected exam, unit and class.',
            ], 422);
        }

        $fullMarks = (float) $fullMarkRow->full_marks;

        if ($obtainMarks > $fullMarks) {
            return response()->json([
                'status'  => false,
                'message' => 'Obtain marks cannot be greater than full marks.',
            ], 422);
        }

        $percentage = (($fullMarks > 0) ? round(($obtainMarks / $fullMarks) * 100, 2) : 0);
        $updatedBy = ((session()->has('user_data') && array_key_exists('user_id', session('user_data'))) ? session('user_data')['user_id'] : 0);

        $mark = ExamStudentMark::updateOrCreate(
            [
                'exam_id'    => $examId,
                'unit_id'    => $unitId,
                'branch_id'  => $branchId,
                'class_id'   => $classId,
                'session_id' => $sessionId,
                'student_id' => $studentId,
                'subject_id' => $subjectId,
            ],
            [
                'full_marks'       => $fullMarks,
                'obtain_marks'     => $obtainMarks,
                'marks_percentage' => $percentage,
                'status'           => 1,
            ]
        );

        $filteredStudentIds = $this->buildStudentQuery($unitId, $branchId, $classId, $sessionId)
                                    ->pluck('students.id')
                                    ->values()
                                    ->all();
        $studentCount = count($filteredStudentIds);
        $examSubjectIds = $this->getExamSubjectRows($exam, $unitId, $classId)->pluck('subject_id')
                                    ->map(function ($item) {
                                        return (int) $item;
                                    })
                                    ->unique()
                                    ->values()
                                    ->all();
        $subjectCount = count($examSubjectIds);
        $entryCount = $studentCount * $subjectCount;
        $enteredCount = ExamStudentMark::where('exam_id', '=', $examId)
                                        ->where('unit_id', '=', $unitId)
                                        ->where('branch_id', '=', $branchId)
                                        ->where('class_id', '=', $classId)
                                        ->where('session_id', '=', $sessionId)
                                        ->where('status', '=', 1)
                                        ->whereNull('deleted_at')
                                        ->whereIn('subject_id', $examSubjectIds)
                                        ->whereIn('student_id', $filteredStudentIds)
                                        ->whereNotNull('obtain_marks')
                                        ->count();
        $pendingCount = max($entryCount - $enteredCount, 0);
        $subjectName = (($fullMarkRow->subject) ? $fullMarkRow->subject->name : 'Subject');

        return response()->json([
            'status'  => true,
            'message' => 'Saved ' . $student->full_name . ' (' . $student->student_id_serial . ') - ' . $this->formatMarksDisplay($obtainMarks) . ' out of ' . $this->formatMarksDisplay($fullMarks) . ' in ' . $exam->name . ' / ' . $subjectName . '.',
            'data'    => [
                'id'               => $mark->id,
                'student_id'       => $studentId,
                'exam_id'          => $examId,
                'subject_id'       => $subjectId,
                'subject_name'     => $subjectName,
                'student_name'     => $student->full_name,
                'student_serial'   => $student->student_id_serial,
                'obtain_marks'     => $this->formatMarksDisplay($obtainMarks),
                'marks_percentage' => number_format($percentage, 2, '.', ''),
                'full_marks'       => $this->formatMarksDisplay($fullMarks),
                'updated_by'       => $updatedBy,
                'student_count'    => $studentCount,
                'subject_count'    => $subjectCount,
                'entry_count'      => $entryCount,
                'entered_count'    => $enteredCount,
                'pending_count'    => $pendingCount,
            ],
        ]);
    }
}
