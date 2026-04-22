<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Classes;
use App\Models\Exam;
use App\Models\ExamFullMark;
use App\Models\Unit;
use App\Services\SiteAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = [
            'title'             => 'Exam',
            'controller'        => 'ExamController',
            'controller_route'  => 'exam',
            'primary_key'       => 'id',
        ];
        $this->siteAuthService = new SiteAuthService();
    }

    private function getUnitOptions(array $selectedIds = [])
    {
        $units = Unit::select('id', 'name')
                    ->where('status', '=', 1)
                    ->orderBy('name', 'ASC')
                    ->get();

        if (!empty($selectedIds)) {
            $selectedUnits = Unit::withTrashed()
                                ->select('id', 'name')
                                ->whereIn('id', $selectedIds)
                                ->orderBy('name', 'ASC')
                                ->get();

            $units = $selectedUnits->merge($units)->unique('id')->sortBy('name')->values();
        }

        return $units;
    }

    private function getClassOptions(array $selectedIds = [])
    {
        $classes = Classes::select('id', 'name', 'unit_id')
                        ->where('status', '=', 1)
                        ->orderBy('unit_id', 'ASC')
                        ->orderBy('name', 'ASC')
                        ->get();

        if (!empty($selectedIds)) {
            $selectedClasses = Classes::withTrashed()
                                    ->select('id', 'name', 'unit_id')
                                    ->whereIn('id', $selectedIds)
                                    ->orderBy('unit_id', 'ASC')
                                    ->orderBy('name', 'ASC')
                                    ->get();

            $classes = $selectedClasses->merge($classes)
                                ->unique('id')
                                ->sortBy(function ($class) {
                                    return sprintf('%05d-%s', $class->unit_id, $class->name);
                                })
                                ->values();
        }

        return $classes;
    }

    private function validateExamPayload(Request $request, ?Exam $exam = null)
    {
        $nameRule = Rule::unique('exams', 'name')->whereNull('deleted_at');
        if ($exam) {
            $nameRule = $nameRule->ignore($exam->id);
        }

        $validator = Validator::make($request->all(), [
            'name'              => ['required', 'string', 'max:255', $nameRule],
            'description'       => ['nullable', 'string', 'max:5000'],
            'unit_id'           => ['required', 'array', 'min:1'],
            'unit_id.*'         => ['required', 'integer', 'exists:units,id'],
            'class_id'          => ['required', 'array', 'min:1'],
            'class_id.*'        => ['required', 'integer', 'exists:classes,id'],
            'full_marks'        => ['required', 'array', 'min:1'],
            'full_marks.*'      => ['required', 'numeric', 'min:0'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $unitIds = (array) $request->input('unit_id', []);
            $classIds = (array) $request->input('class_id', []);
            $fullMarks = (array) $request->input('full_marks', []);
            $rowCount = max(count($unitIds), count($classIds), count($fullMarks));

            if ($rowCount < 1) {
                $validator->errors()->add('unit_id', 'Please add at least one unit wise class wise full marks row.');
                return;
            }

            $seenPairs = [];
            for ($i = 0; $i < $rowCount; $i++) {
                $unitId = isset($unitIds[$i]) ? (int) $unitIds[$i] : 0;
                $classId = isset($classIds[$i]) ? (int) $classIds[$i] : 0;

                if ($unitId > 0 && $classId > 0) {
                    $classExists = Classes::where('id', '=', $classId)
                                        ->where('unit_id', '=', $unitId)
                                        ->exists();

                    if (!$classExists) {
                        $validator->errors()->add('class_id.' . $i, 'Selected class does not belong to the selected unit for row ' . ($i + 1) . '.');
                    }

                    $pairKey = $unitId . '-' . $classId;
                    if (isset($seenPairs[$pairKey])) {
                        $validator->errors()->add('class_id.' . $i, 'Duplicate unit and class combination is not allowed for row ' . ($i + 1) . '.');
                    }
                    $seenPairs[$pairKey] = true;
                }
            }
        });

        return $validator;
    }

    private function persistExamMarks(Exam $exam, Request $request): void
    {
        $unitIds = array_values((array) $request->input('unit_id', []));
        $classIds = array_values((array) $request->input('class_id', []));
        $fullMarks = array_values((array) $request->input('full_marks', []));

        ExamFullMark::where('exam_id', '=', $exam->id)->delete();

        foreach ($unitIds as $index => $unitId) {
            $unitId = (int) $unitId;
            $classId = isset($classIds[$index]) ? (int) $classIds[$index] : 0;
            $fullMark = isset($fullMarks[$index]) ? $fullMarks[$index] : null;

            if (!$unitId || !$classId || $fullMark === null || $fullMark === '') {
                continue;
            }

            ExamFullMark::create([
                'exam_id'    => $exam->id,
                'unit_id'    => $unitId,
                'class_id'   => $classId,
                'full_marks' => $fullMark,
                'status'     => 1,
            ]);
        }
    }

    /* list */
    public function list(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'] . ' List';
        $page_name = 'exam.list';
        $data['rows'] = Exam::with(['fullMarks.unit', 'fullMarks.examClass'])
                        ->where('status', '!=', 3)
                        ->orderBy('id', 'DESC')
                        ->get();
        $data['action'] = 'Add';
        $data['single_row'] = null;
        $data['units'] = $this->getUnitOptions();
        $data['classes'] = $this->getClassOptions();

        if ($request->isMethod('post')) {
            $validator = $this->validateExamPayload($request);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::transaction(function () use ($request) {
                $exam = Exam::create([
                    'name'        => trim($request->name),
                    'description' => (($request->description !== null && trim($request->description) !== '') ? trim($request->description) : null),
                    'status'      => 1,
                ]);

                $this->persistExamMarks($exam, $request);
            });

            return redirect($this->data['controller_route'] . '/list')->with('success_message', $this->data['title'] . ' added successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    /* list */

    /* edit */
    public function edit(Request $request, $id)
    {
        $data['module'] = $this->data;
        $id = Helper::decoded($id);
        $title = $this->data['title'] . ' Update';
        $page_name = 'exam.edit';
        $data['single_row'] = Exam::with(['fullMarks.unit', 'fullMarks.examClass'])
                                ->where($this->data['primary_key'], '=', $id)
                                ->firstOrFail();
        $data['action'] = 'Edit';
        $data['rows'] = Exam::with(['fullMarks.unit', 'fullMarks.examClass'])
                        ->where('status', '!=', 3)
                        ->orderBy('id', 'DESC')
                        ->get();

        $selectedUnitIds = $data['single_row']->fullMarks->pluck('unit_id')->filter()->unique()->values()->all();
        $selectedClassIds = $data['single_row']->fullMarks->pluck('class_id')->filter()->unique()->values()->all();
        $data['units'] = $this->getUnitOptions($selectedUnitIds);
        $data['classes'] = $this->getClassOptions($selectedClassIds);

        if ($request->isMethod('post')) {
            $validator = $this->validateExamPayload($request, $data['single_row']);
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            DB::transaction(function () use ($request, $data) {
                $exam = $data['single_row'];
                $exam->update([
                    'name'        => trim($request->name),
                    'description' => (($request->description !== null && trim($request->description) !== '') ? trim($request->description) : null),
                ]);

                $this->persistExamMarks($exam, $request);
            });

            return redirect($this->data['controller_route'] . '/list')->with('success_message', $this->data['title'] . ' updated successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    /* edit */

    /* change status */
    public function change_status(Request $request, $id)
    {
        $id = Helper::decoded($id);
        $model = Exam::find($id);

        if (!$model) {
            return redirect($this->data['controller_route'] . '/list')->with('error_message', $this->data['title'] . ' not found.');
        }

        if ($model->status == 1) {
            $model->status = 0;
            $msg = 'blocked';
        } else {
            $model->status = 1;
            $msg = 'activated';
        }

        $model->save();

        return redirect($this->data['controller_route'] . '/list')->with('success_message', $this->data['title'] . ' ' . $msg . ' successfully !!!');
    }
    /* change status */
}
