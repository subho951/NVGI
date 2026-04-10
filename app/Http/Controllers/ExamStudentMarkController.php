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

    private function getExamFullMarkValue(Exam $exam, int $unitId, int $classId): float
    {
        $fullMarkRow = $exam->fullMarks->first(function ($markRow) use ($unitId, $classId) {
            return ((int) $markRow->unit_id === (int) $unitId && (int) $markRow->class_id === (int) $classId);
        });

        return (($fullMarkRow) ? (float) $fullMarkRow->full_marks : 0);
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
        $existingMarksByExam = $existingMarks->groupBy('exam_id')->map(function ($group) {
            return $group->keyBy('student_id');
        });

        foreach ($exams as $exam) {
            $fullMarks = $this->getExamFullMarkValue($exam, $unitId, $classId);
            $marksForExam = $existingMarksByExam->get($exam->id, collect());
            $rows = [];
            $enteredCount = 0;

            foreach ($students as $student) {
                $mark = $marksForExam->get($student->id);
                $obtainMarks = '';
                $hasValue = false;

                if ($mark && $mark->obtain_marks !== null && $mark->obtain_marks !== '') {
                    $obtainMarks = (string) $mark->obtain_marks;
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
                }

                $rows[] = [
                    'student'         => $student,
                    'mark'            => $mark,
                    'mark_id'         => (($mark) ? (int) $mark->id : 0),
                    'obtain_marks'    => $obtainMarks,
                    'percentage'      => $percentage,
                    'has_value'       => $hasValue,
                    'full_marks'      => $fullMarks,
                    'full_marks_label' => $this->formatMarksDisplay($fullMarks),
                ];
            }

            $studentCount = count($rows);

            $examSections[] = [
                'exam'             => $exam,
                'full_marks'       => $fullMarks,
                'full_marks_label' => $this->formatMarksDisplay($fullMarks),
                'rows'             => $rows,
                'student_count'    => $studentCount,
                'entered_count'    => $enteredCount,
                'pending_count'    => max($studentCount - $enteredCount, 0),
            ];
        }

        return $examSections;
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
                                    $query->where('status', '=', 1);
                                }])
                                ->whereIn('id', $selectedExamIds)
                                ->where('status', '=', 1)
                                ->get();

                    if ($exams->count() !== count($selectedExamIds)) {
                        $validator->errors()->add('exam_ids', 'One or more selected exams are not available.');
                        return;
                    }

                    foreach ($exams as $exam) {
                        $matchingFullMark = $exam->fullMarks->first(function ($fullMarkRow) use ($unitId, $classId) {
                            return ((int) $fullMarkRow->unit_id === (int) $unitId && (int) $fullMarkRow->class_id === (int) $classId);
                        });

                        if (!$matchingFullMark) {
                            $validator->errors()->add('exam_ids', 'Exam "' . $exam->name . '" does not have a full marks setup for the selected unit and class.');
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
                            $query->where('status', '=', 1);
                        }])
                        ->whereIn('id', $selectedExamIds)
                        ->where('status', '=', 1)
                        ->orderBy('name', 'ASC')
                        ->get();

            $existingMarks = ExamStudentMark::select(
                                        'id',
                                        'exam_id',
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
            $data['entered_count'] = $examSections[0]['entered_count'] ?? 0;
            $data['pending_count'] = $examSections[0]['pending_count'] ?? 0;
            $data['exam_sections'] = $examSections;
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
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
            'obtain_marks' => ['required', 'integer', 'min:0'],
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
        $obtainMarks = (int) $request->input('obtain_marks');

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
                        $query->where('status', '=', 1);
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

        $fullMarkRow = $exam->fullMarks->first(function ($markRow) use ($unitId, $classId) {
            return ((int) $markRow->unit_id === (int) $unitId && (int) $markRow->class_id === (int) $classId);
        });

        if (!$fullMarkRow) {
            return response()->json([
                'status'  => false,
                'message' => 'Full marks setup not found for the selected exam, unit and class.',
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
        $enteredCount = ExamStudentMark::where('exam_id', '=', $examId)
                                        ->where('unit_id', '=', $unitId)
                                        ->where('branch_id', '=', $branchId)
                                        ->where('class_id', '=', $classId)
                                        ->where('session_id', '=', $sessionId)
                                        ->where('status', '=', 1)
                                        ->whereNull('deleted_at')
                                        ->whereIn('student_id', $filteredStudentIds)
                                        ->whereNotNull('obtain_marks')
                                        ->count();
        $pendingCount = max($studentCount - $enteredCount, 0);

        return response()->json([
            'status'  => true,
            'message' => 'Saved ' . $student->full_name . ' (' . $student->student_id_serial . ') - ' . $obtainMarks . ' out of ' . $this->formatMarksDisplay($fullMarks) . ' in ' . $exam->name . '.',
            'data'    => [
                'id'               => $mark->id,
                'student_id'       => $studentId,
                'exam_id'          => $examId,
                'student_name'     => $student->full_name,
                'student_serial'   => $student->student_id_serial,
                'obtain_marks'     => (string) $obtainMarks,
                'marks_percentage' => number_format($percentage, 2, '.', ''),
                'full_marks'       => $this->formatMarksDisplay($fullMarks),
                'updated_by'       => $updatedBy,
                'student_count'    => $studentCount,
                'entered_count'    => $enteredCount,
                'pending_count'    => $pendingCount,
            ],
        ]);
    }
}
