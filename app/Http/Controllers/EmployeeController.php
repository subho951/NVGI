<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Branch;
use App\Models\Employee;
use App\Services\SiteAuthService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = array(
            'title'             => 'Employee',
            'controller'        => 'EmployeeController',
            'controller_route'  => 'employee',
            'primary_key'       => 'id',
        );
        $this->siteAuthService = new SiteAuthService();
    }

    /* list */
    public function list(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'] . ' List';
        $page_name = 'employee.list';

        $branchMap = $this->getBranchMap();
        $leaveBalanceMap = $this->getEmployeeLeaveBalanceMap();
        $data['branches'] = Branch::select('id', 'name')
                                ->where('status', '!=', 3)
                                ->orderBy('name', 'ASC')
                                ->get();
        $data['rows'] = Employee::where('status', '!=', 3)
                            ->orderBy('id', 'DESC')
                            ->get()
                            ->map(function ($row) use ($branchMap, $leaveBalanceMap) {
                                $row->employee_name = $this->buildEmployeeName($row);
                                $row->category_names = $this->employeeCategoryLabels($row->category);
                                $row->category_salary_map = $this->employeeCategorySalaryMap($row->category_salaries, (float)$row->salary, $row->category_names);
                                $row->leave_balances = $leaveBalanceMap->get($row->id, collect())->values();
                                $row->in_time_display = $this->formatTimeForDisplay($row->in_time);
                                $row->out_time_display = $this->formatTimeForDisplay($row->out_time);

                                $branchIds = json_decode((string)$row->branch, true);
                                if (!is_array($branchIds)) {
                                    $branchIds = [];
                                }
                                $branchIds = array_values(array_unique(array_filter(array_map('intval', $branchIds))));
                                $row->branch_ids = $branchIds;
                                $row->branch_names = collect($branchIds)
                                                    ->map(function ($branchId) use ($branchMap) {
                                                        return $branchMap[$branchId] ?? null;
                                                    })
                                                    ->filter()
                                                    ->values()
                                                    ->all();

                                return $row;
                            });

        $data['action'] = 'Add';
        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    /* list */

    /* add */
    public function add(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'] . ' Add';
        $page_name = 'employee.add-edit';
        $data['row'] = [];
        $data['action'] = 'Add';
        $data['branchOptions'] = Branch::select('id', 'name', 'unit_id')
                                    ->where('status', '!=', 3)
                                    ->orderBy('name', 'ASC')
                                    ->get();
        $data['categoryOptions'] = $this->employeeCategoryOptions();
        $data['employee_no_preview'] = $this->generateEmployeeNo();

        if ($request->isMethod('post')) {
            $request->validate($this->employeeValidationRules());

            $userId = $this->currentUserId();
            $nextSlNo = $this->getNextEmployeeSlNo();
            $employeeNo = $this->formatEmployeeNo($nextSlNo);
            $branchIds = $this->normalizeBranchIds($request->input('branch', []));
            $categoryValues = $this->normalizeCategoryValues($request->input('category', []));
            $categorySalaries = $this->normalizeCategorySalaries($request->input('category_salaries', []), $categoryValues, $request->salary);
            $hasVhsTeacherCategory = in_array('VHS TEACHER', $categoryValues, true);
            $age = $this->calculateAge($request->dob);
            $imagePath = null;

            try {
                if ($request->hasFile('image')) {
                    $imagePath = $this->storeEmployeeImage($request->file('image'));
                }

                Employee::create([
                    'sl_no'         => $nextSlNo,
                    'employee_no'   => $employeeNo,
                    'first_name'    => trim((string)$request->first_name),
                    'middle_name'   => $this->normalizeNullableString($request->middle_name),
                    'last_name'     => trim((string)$request->last_name),
                    'email'         => $this->normalizeNullableString($request->email),
                    'phone'         => $this->normalizeNullableString($request->phone),
                    'address'       => $this->normalizeNullableString($request->address),
                    'pincode'       => $this->normalizeNullableString($request->pincode),
                    'dob'           => $request->dob,
                    'age'           => $age,
                    'doj'           => $request->doj,
                    'image'         => $imagePath,
                    'salary'        => (float)$request->salary,
                    'category_salaries' => $this->serializeCategorySalaries($categorySalaries),
                    'branch'        => json_encode($branchIds),
                    'gender'        => $this->normalizeNullableString($request->gender),
                    'category'      => $this->serializeCategoryValues($categoryValues),
                    'in_time'       => $hasVhsTeacherCategory ? $this->normalizeTimeValue($request->in_time) : null,
                    'out_time'      => $hasVhsTeacherCategory ? $this->normalizeTimeValue($request->out_time) : null,
                    'aadhar_no'     => $this->normalizeNullableString($request->aadhar_no),
                    'bank_name'     => $this->normalizeNullableString($request->bank_name),
                    'bank_branch'   => $this->normalizeNullableString($request->bank_branch),
                    'account_no'    => $this->normalizeNullableString($request->account_no),
                    'ifsc_code'     => $this->normalizeNullableString($request->ifsc_code),
                    'account_type'  => $this->normalizeNullableString($request->account_type),
                    'status'        => 1,
                    'created_by'    => $userId,
                    'updated_by'    => $userId,
                ]);
            } catch (\Throwable $e) {
                report($e);
                if (!empty($imagePath)) {
                    $this->deleteEmployeeImage($imagePath);
                }

                return redirect()->back()->withInput()->with('error_message', 'Unable to save employee.');
            }

            return redirect($this->data['controller_route'] . '/list')->with('success_message', $this->data['title'] . ' added successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    /* add */

    /* edit */
    public function edit(Request $request, $id)
    {
        $data['module'] = $this->data;
        $id = Helper::decoded($id);
        $title = $this->data['title'] . ' Update';
        $page_name = 'employee.add-edit';
        $data['row'] = Employee::where($this->data['primary_key'], '=', $id)
                            ->where('status', '!=', 3)
                            ->first();
        $data['action'] = 'Edit';
        $data['branchOptions'] = Branch::select('id', 'name', 'unit_id')
                                    ->where('status', '!=', 3)
                                    ->orderBy('name', 'ASC')
                                    ->get();
        $data['categoryOptions'] = $this->employeeCategoryOptions();

        if (!$data['row']) {
            return redirect($this->data['controller_route'] . '/list')->with('error_message', 'Employee not found !!!');
        }

        if ($request->isMethod('post')) {
            $request->validate($this->employeeValidationRules($data['row']->id));

            $userId = $this->currentUserId();
            $branchIds = $this->normalizeBranchIds($request->input('branch', []));
            $categoryValues = $this->normalizeCategoryValues($request->input('category', []));
            $categorySalaries = $this->normalizeCategorySalaries($request->input('category_salaries', []), $categoryValues, $request->salary);
            $hasVhsTeacherCategory = in_array('VHS TEACHER', $categoryValues, true);
            $age = $this->calculateAge($request->dob);
            $oldImage = $data['row']->image;
            $newImagePath = $oldImage;

            try {
                if ($request->hasFile('image')) {
                    $newImagePath = $this->storeEmployeeImage($request->file('image'));
                }

                $data['row']->update([
                    'first_name'    => trim((string)$request->first_name),
                    'middle_name'   => $this->normalizeNullableString($request->middle_name),
                    'last_name'     => trim((string)$request->last_name),
                    'email'         => $this->normalizeNullableString($request->email),
                    'phone'         => $this->normalizeNullableString($request->phone),
                    'address'       => $this->normalizeNullableString($request->address),
                    'pincode'       => $this->normalizeNullableString($request->pincode),
                    'dob'           => $request->dob,
                    'age'           => $age,
                    'doj'           => $request->doj,
                    'image'         => $newImagePath,
                    'salary'        => (float)$request->salary,
                    'category_salaries' => $this->serializeCategorySalaries($categorySalaries),
                    'branch'        => json_encode($branchIds),
                    'gender'        => $this->normalizeNullableString($request->gender),
                    'category'      => $this->serializeCategoryValues($categoryValues),
                    'in_time'       => $hasVhsTeacherCategory ? $this->normalizeTimeValue($request->in_time) : null,
                    'out_time'      => $hasVhsTeacherCategory ? $this->normalizeTimeValue($request->out_time) : null,
                    'aadhar_no'     => $this->normalizeNullableString($request->aadhar_no),
                    'bank_name'     => $this->normalizeNullableString($request->bank_name),
                    'bank_branch'   => $this->normalizeNullableString($request->bank_branch),
                    'account_no'    => $this->normalizeNullableString($request->account_no),
                    'ifsc_code'     => $this->normalizeNullableString($request->ifsc_code),
                    'account_type'  => $this->normalizeNullableString($request->account_type),
                    'updated_by'    => $userId,
                ]);
            } catch (\Throwable $e) {
                report($e);
                if ($request->hasFile('image') && !empty($newImagePath) && $newImagePath !== $oldImage) {
                    $this->deleteEmployeeImage($newImagePath);
                }

                return redirect()->back()->withInput()->with('error_message', 'Unable to update employee.');
            }

            if ($request->hasFile('image') && !empty($oldImage) && $oldImage !== $newImagePath) {
                $this->deleteEmployeeImage($oldImage);
            }

            return redirect($this->data['controller_route'] . '/list')->with('success_message', $this->data['title'] . ' updated successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $page_name, $data);
        return view('front.pages.' . $page_name, $data);
    }
    /* edit */

    /* delete */
    public function delete(Request $request, $id)
    {
        $id = Helper::decoded($id);

        $employee = Employee::where($this->data['primary_key'], '=', $id)
                        ->where('status', '!=', 3)
                        ->first();

        if (!$employee) {
            return redirect($this->data['controller_route'] . '/list')->with('error_message', 'Employee not found !!!');
        }

        if (!empty($employee->image)) {
            $this->deleteEmployeeImage($employee->image);
        }

        $employee->update([
            'status'     => 3,
            'deleted_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->currentUserId(),
        ]);

        return redirect($this->data['controller_route'] . '/list')->with('success_message', $this->data['title'] . ' deleted successfully !!!');
    }
    /* delete */

    /* change status */
    public function change_status(Request $request, $id)
    {
        $id = Helper::decoded($id);

        $employee = Employee::where($this->data['primary_key'], '=', $id)
                        ->where('status', '!=', 3)
                        ->first();

        if (!$employee) {
            return redirect($this->data['controller_route'] . '/list')->with('error_message', 'Employee not found !!!');
        }

        if ((int)$employee->status === 1) {
            $employee->status = 0;
            $msg = 'deactivated';
        } else {
            $employee->status = 1;
            $msg = 'activated';
        }

        $employee->updated_by = $this->currentUserId();
        $employee->save();

        return redirect($this->data['controller_route'] . '/list')->with('success_message', $this->data['title'] . ' ' . $msg . ' successfully !!!');
    }
    /* change status */

    private function employeeValidationRules($employeeId = null)
    {
        $emailRule = Rule::unique('employees', 'email')->whereNull('deleted_at');
        $phoneRule = Rule::unique('employees', 'phone')->whereNull('deleted_at');

        if ($employeeId) {
            $emailRule->ignore($employeeId);
            $phoneRule->ignore($employeeId);
        }

        return [
            'first_name'    => 'required|string|max:255',
            'middle_name'   => 'nullable|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => ['nullable', 'email', 'max:255', $emailRule],
            'phone'         => ['nullable', 'digits:10', $phoneRule],
            'address'       => 'nullable|string|max:1000',
            'pincode'       => 'nullable|digits:6',
            'dob'           => 'required|date|before_or_equal:today',
            'doj'           => 'required|date|before_or_equal:today',
            'salary'        => 'required|numeric|min:0',
            'category_salaries' => 'nullable|array',
            'category_salaries.*' => 'nullable|numeric|min:0',
            'gender'        => 'nullable|in:Male,Female,Others',
            'category'      => 'nullable|array',
            'category.*'    => ['string', Rule::in($this->employeeCategoryOptions())],
            'in_time'       => 'nullable|date_format:H:i',
            'out_time'      => 'nullable|date_format:H:i',
            'branch'        => 'required|array|min:1',
            'branch.*'      => [
                'integer',
                Rule::exists('branches', 'id')->where(function ($query) {
                    $query->where('status', '!=', 3);
                }),
            ],
            'image'         => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,svg,ico,avif',
        ];
    }

    private function employeeCategoryOptions()
    {
        return [
            'VHS TEACHER',
            'TSA TEACHER',
            'FRONT-DESK',
            'GROUP-D',
        ];
    }

    private function normalizeCategoryValues($categories = [])
    {
        if (!is_array($categories)) {
            $decodedCategories = json_decode((string)$categories, true);
            $categories = is_array($decodedCategories) ? $decodedCategories : [$categories];
        }

        $validCategories = $this->employeeCategoryOptions();
        $categories = array_map(function ($category) {
            return trim((string)$category);
        }, $categories);
        $categories = array_filter($categories, function ($category) use ($validCategories) {
            return $category !== '' && in_array($category, $validCategories, true);
        });

        return array_values(array_unique($categories));
    }

    private function serializeCategoryValues(array $categories)
    {
        return !empty($categories) ? json_encode(array_values($categories)) : null;
    }

    private function employeeCategoryLabels($categories)
    {
        return $this->normalizeCategoryValues($categories);
    }

    private function normalizeCategorySalaries($salaryValues, array $categories, $defaultSalary = 0.0)
    {
        if (!is_array($salaryValues)) {
            $decodedSalaryValues = json_decode((string)$salaryValues, true);
            $salaryValues = is_array($decodedSalaryValues) ? $decodedSalaryValues : [];
        }

        $defaultSalary = round((float)$defaultSalary, 2);
        $categorySalaries = [];

        foreach ($categories as $category) {
            $value = $salaryValues[$category] ?? $defaultSalary;

            if (is_array($value)) {
                $value = $defaultSalary;
            }

            $value = trim((string)$value);
            $categorySalaries[$category] = round((float)($value === '' ? $defaultSalary : $value), 2);
        }

        return $categorySalaries;
    }

    private function serializeCategorySalaries(array $categorySalaries)
    {
        return !empty($categorySalaries) ? json_encode($categorySalaries) : null;
    }

    private function employeeCategorySalaryMap($categorySalaries, float $fallbackSalary, $categories = [])
    {
        if (!is_array($categories)) {
            $categories = $this->normalizeCategoryValues($categories);
        }

        if (!is_array($categorySalaries)) {
            $decodedSalaryValues = json_decode((string)$categorySalaries, true);
            $categorySalaries = is_array($decodedSalaryValues) ? $decodedSalaryValues : [];
        }

        if (empty($categories) && !empty($categorySalaries)) {
            $categories = array_keys($categorySalaries);
        }

        $salaryMap = [];

        foreach ($categories as $category) {
            $category = trim((string)$category);

            if ($category === '') {
                continue;
            }

            $salaryMap[$category] = round((float)($categorySalaries[$category] ?? $fallbackSalary), 2);
        }

        return $salaryMap;
    }

    private function normalizeTimeValue($value)
    {
        $value = trim((string)$value);

        if ($value === '') {
            return null;
        }

        return Carbon::createFromFormat('H:i', $value)->format('H:i');
    }

    private function formatTimeForDisplay($value)
    {
        $value = trim((string)$value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i', substr($value, 0, 5))->format('H:i');
        } catch (\Throwable $e) {
            return $value;
        }
    }

    private function generateEmployeeNo()
    {
        return $this->formatEmployeeNo($this->getNextEmployeeSlNo());
    }

    private function formatEmployeeNo($slNo)
    {
        return 'NVGI-' . str_pad((string)$slNo, 4, '0', STR_PAD_LEFT);
    }

    private function getNextEmployeeSlNo()
    {
        return ((int)Employee::max('sl_no')) + 1;
    }

    private function calculateAge($dob)
    {
        return Carbon::parse($dob)->age;
    }

    private function normalizeBranchIds($branches = [])
    {
        if (!is_array($branches)) {
            $branches = [];
        }

        $branchIds = array_map('intval', $branches);
        $branchIds = array_values(array_filter($branchIds));

        return array_values(array_unique($branchIds));
    }

    private function normalizeNullableString($value)
    {
        $value = trim((string)$value);
        return ($value === '') ? null : $value;
    }

    private function storeEmployeeImage($file)
    {
        $uploadPath = public_path('uploads/employee');
        if (!File::exists($uploadPath)) {
            File::makeDirectory($uploadPath, 0755, true, true);
        }

        $imageName = $file->getClientOriginalName();
        $uploadedFile = $this->upload_single_file('image', $imageName, 'employee', 'image');

        if (!$uploadedFile['status']) {
            throw new \RuntimeException($uploadedFile['message']);
        }

        return '/uploads/employee/' . $uploadedFile['newFilename'];
    }

    private function deleteEmployeeImage($imagePath)
    {
        $filePath = public_path(ltrim((string)$imagePath, '/\\'));

        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }

    private function getBranchMap()
    {
        return Branch::select('id', 'name')
                ->where('status', '!=', 3)
                ->orderBy('name', 'ASC')
                ->get()
                ->pluck('name', 'id')
                ->toArray();
    }

    private function getEmployeeLeaveBalanceMap()
    {
        return DB::table('employee_leave_allotments as ela')
            ->leftJoin('leave_types as lt', 'lt.id', '=', 'ela.leave_type_id')
            ->select([
                'ela.employee_id',
                'ela.leave_type_id',
                DB::raw('MAX(lt.name) as leave_type_name'),
                DB::raw('SUM(ela.balance_leave) as balance_leave'),
            ])
            ->where('ela.status', '!=', 3)
            ->groupBy('ela.employee_id', 'ela.leave_type_id')
            ->orderBy('leave_type_name')
            ->get()
            ->groupBy('employee_id')
            ->map(function ($leaveRows) {
                return $leaveRows
                    ->sortBy('leave_type_name')
                    ->map(function ($leaveRow) {
                        return (object) [
                            'leave_type_id' => $leaveRow->leave_type_id,
                            'leave_type_name' => $leaveRow->leave_type_name,
                            'balance_leave' => (float) $leaveRow->balance_leave,
                        ];
                    })
                    ->values();
            });
    }

    private function buildEmployeeName($row)
    {
        $parts = [
            trim((string)$row->first_name),
            trim((string)$row->middle_name),
            trim((string)$row->last_name),
        ];

        $parts = array_values(array_filter($parts, function ($value) {
            return $value !== '';
        }));

        return trim(implode(' ', $parts));
    }

    private function currentUserId()
    {
        if (session()->has('user_data') && array_key_exists('user_id', session('user_data'))) {
            return (int)session('user_data')['user_id'];
        }

        return ((Auth::check()) ? (int)Auth::id() : 0);
    }
}
