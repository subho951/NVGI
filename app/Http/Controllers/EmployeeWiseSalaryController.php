<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\SalaryHead;
use App\Services\SiteAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeWiseSalaryController extends Controller
{
    private const BASIC_GROSS_PERCENTAGE = 40.0;
    private const HRA_BASIC_PERCENTAGE = 50.0;

    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = [
            'title' => 'Employee Wise Salary',
            'controller' => 'EmployeeWiseSalaryController',
            'controller_route' => 'payroll-leave/employee-wise-salary',
            'primary_key' => 'id',
        ];

        $this->siteAuthService = new SiteAuthService();
    }

    public function list(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'].' List';
        $pageName = 'payroll-leave.employee-wise-salary.list';
        $employees = Employee::where('status', '=', 1)
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('last_name')
            ->orderBy('employee_no')
            ->get();

        $salaryRows = EmployeeSalary::where('status', '!=', 3)
            ->whereIn('employee_id', $employees->pluck('id')->all())
            ->orderByRaw("FIELD(salary_head_type, 'EARNING', 'DEDUCTION')")
            ->orderBy('salary_head_name')
            ->get()
            ->groupBy('employee_id');

        $data['rows'] = $employees->flatMap(function ($employee) use ($salaryRows) {
            $employeeSalaryRows = $salaryRows->get($employee->id, collect());
            $categories = $this->employeeCategoryValues($employee->category);

            if (empty($categories)) {
                $categories = [''];
            }

            return collect($categories)->map(function ($category) use ($employee, $employeeSalaryRows) {
                $categorySalaryRows = $this->salaryRowsForCategory($employeeSalaryRows, $category);
                $earningTotal = (float) $categorySalaryRows->where('salary_head_type', SalaryHead::TYPE_EARNING)->sum('calculated_amount');
                $deductionTotal = (float) $categorySalaryRows->where('salary_head_type', SalaryHead::TYPE_DEDUCTION)->sum('calculated_amount');

                return (object) [
                    'employee' => $employee,
                    'employee_name' => $this->employeeName($employee),
                    'employee_category' => $category,
                    'gross_salary' => $this->employeeCategorySalary($employee, $category),
                    'salary_rows' => $categorySalaryRows,
                    'earning_total' => $earningTotal,
                    'deduction_total' => $deductionTotal,
                    'net_salary' => $earningTotal - $deductionTotal,
                    'last_calculated_at' => $categorySalaryRows->max('updated_at'),
                ];
            });
        })->values();

        $data['activeSalaryHeadCount'] = SalaryHead::where('status', '=', 1)->count();
        $data['action'] = 'List';

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function calculate(Request $request, $employee)
    {
        $employeeId = (int) Helper::decoded($employee);
        $employee = Employee::where('id', '=', $employeeId)
            ->where('status', '=', 1)
            ->first();

        if (! $employee) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Employee not found !!!');
        }

        $employeeCategories = $this->employeeCategoryValues($employee->category);
        $selectedCategory = trim((string) $request->input('employee_category', ''));

        if ($selectedCategory !== '' && ! in_array($selectedCategory, $employeeCategories, true)) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Invalid employee category selected !!!');
        }

        if ($selectedCategory === '' && ! empty($employeeCategories)) {
            $selectedCategory = $employeeCategories[0];
        }

        $salaryHeads = SalaryHead::where('status', '=', 1)->orderBy('id')->get();

        if ($salaryHeads->isEmpty()) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'No active salary head found !!!');
        }

        $savedCount = DB::transaction(function () use ($employee, $salaryHeads, $selectedCategory) {
            return $this->storeEmployeeCategorySalary($employee, $selectedCategory, $salaryHeads, $this->currentUserId());
        });

        return redirect($this->data['controller_route'].'/list')->with(
            'success_message',
            'Salary calculated for '.$this->employeeName($employee).($selectedCategory !== '' ? ' - '.$selectedCategory : '').' with '.$savedCount.' salary head(s).'
        );
    }

    public function calculateAll(Request $request)
    {
        $salaryHeads = SalaryHead::where('status', '=', 1)->orderBy('id')->get();

        if ($salaryHeads->isEmpty()) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'No active salary head found !!!');
        }

        $employees = Employee::where('status', '=', 1)
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('last_name')
            ->orderBy('employee_no')
            ->get();

        if ($employees->isEmpty()) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'No active employee found !!!');
        }

        $employeeCount = 0;
        $categoryRowCount = 0;
        $savedCount = 0;
        $userId = $this->currentUserId();

        DB::transaction(function () use ($employees, $salaryHeads, $userId, &$employeeCount, &$categoryRowCount, &$savedCount) {
            foreach ($employees as $employee) {
                $employeeCount++;
                $categories = $this->employeeCategoryValues($employee->category);

                if (empty($categories)) {
                    $categories = [''];
                }

                foreach ($categories as $category) {
                    $savedCount += $this->storeEmployeeCategorySalary($employee, $category, $salaryHeads, $userId);
                    $categoryRowCount++;
                }
            }
        });

        return redirect($this->data['controller_route'].'/list')->with(
            'success_message',
            'Salary calculated for '.$employeeCount.' employee(s), '.$categoryRowCount.' category row(s), and '.$savedCount.' salary head row(s).'
        );
    }

    private function storeEmployeeCategorySalary(Employee $employee, string $selectedCategory, $salaryHeads, int $userId): int
    {
        $savedCount = 0;
        $grossSalary = $this->employeeCategorySalary($employee, $selectedCategory);
        $basicHead = $salaryHeads->first(fn ($salaryHead) => $this->isBasicHead($salaryHead));
        $basicValue = $basicHead ? $this->calculateHeadValue($basicHead, $grossSalary, 0.0, 0.0) : 0.0;
        $hraHead = $salaryHeads->first(fn ($salaryHead) => $this->isHraHead($salaryHead));
        $hraValue = $hraHead ? $this->calculateHeadValue($hraHead, $grossSalary, $basicValue, 0.0) : 0.0;
        $activeSalaryHeadIds = $salaryHeads->pluck('id')->map(fn ($id) => (int) $id)->all();

        foreach ($salaryHeads as $salaryHead) {
            if ($basicHead && (int) $salaryHead->id === (int) $basicHead->id) {
                $calculatedAmount = $basicValue;
            } elseif ($hraHead && (int) $salaryHead->id === (int) $hraHead->id) {
                $calculatedAmount = $hraValue;
            } else {
                $calculatedAmount = $this->calculateHeadValue($salaryHead, $grossSalary, $basicValue, $hraValue);
            }

            EmployeeSalary::updateOrCreate(
                [
                    'employee_id' => (int) $employee->id,
                    'employee_category' => $selectedCategory,
                    'salary_head_id' => (int) $salaryHead->id,
                ],
                [
                    'employee_no' => $employee->employee_no,
                    'employee_name' => $this->employeeName($employee),
                    'employee_category' => $selectedCategory,
                    'gross_salary' => $grossSalary,
                    'salary_head_name' => $salaryHead->name,
                    'salary_head_type' => $salaryHead->type,
                    'calculation_type' => $salaryHead->calculation_type,
                    'calculation_base' => $salaryHead->calculation_base,
                    'calculation_amount' => (float) $salaryHead->calculation_amount,
                    'formula_label' => $this->salaryHeadFormulaLabel($salaryHead),
                    'is_payslip_show' => $salaryHead->is_payslip_show ? 1 : 0,
                    'calculated_amount' => round($calculatedAmount, 2),
                    'status' => 1,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'calculated_by' => $userId,
                ]
            );

            $savedCount++;
        }

        EmployeeSalary::where('employee_id', '=', (int) $employee->id)
            ->where('employee_category', '=', $selectedCategory)
            ->whereNotIn('salary_head_id', $activeSalaryHeadIds)
            ->update([
                'status' => 3,
                'updated_by' => $userId,
            ]);

        return $savedCount;
    }

    private function calculateHeadValue(SalaryHead $salaryHead, float $grossSalary, float $basicValue, float $hraValue): float
    {
        $amount = (float) $salaryHead->calculation_amount;

        if ($this->isBasicHead($salaryHead)) {
            return round(($grossSalary * self::BASIC_GROSS_PERCENTAGE) / 100, 2);
        }

        if ($this->isHraHead($salaryHead)) {
            $hraPercentage = $amount > 0 ? $amount : self::HRA_BASIC_PERCENTAGE;

            return round(($basicValue * $hraPercentage) / 100, 2);
        }

        if ($salaryHead->calculation_base === SalaryHead::BASE_FIXED_AMOUNT) {
            return round($amount, 2);
        }

        if ($salaryHead->calculation_base === SalaryHead::BASE_BASIC) {
            return round(($basicValue * $amount) / 100, 2);
        }

        if ($salaryHead->calculation_base === SalaryHead::BASE_GROSS_SALARY) {
            return round(($grossSalary * $amount) / 100, 2);
        }

        if ($salaryHead->calculation_base === SalaryHead::BASE_GROSS_BALANCE_AFTER_BASIC_HRA_DIVISOR) {
            if ($amount <= 0) {
                return 0.0;
            }

            return round(($grossSalary - ($basicValue + $hraValue)) / $amount, 2);
        }

        return 0.0;
    }

    private function salaryHeadFormulaLabel(SalaryHead $salaryHead): string
    {
        if ($this->isBasicHead($salaryHead)) {
            return 'Gross Salary x '.SalaryHead::formatAmount(self::BASIC_GROSS_PERCENTAGE).'%';
        }

        if ($this->isHraHead($salaryHead)) {
            $hraPercentage = (float) $salaryHead->calculation_amount;
            $hraPercentage = $hraPercentage > 0 ? $hraPercentage : self::HRA_BASIC_PERCENTAGE;

            return 'Basic x '.SalaryHead::formatAmount($hraPercentage).'%';
        }

        return $salaryHead->formula_label;
    }

    private function isBasicHead(SalaryHead $salaryHead): bool
    {
        return $this->normalizedHeadName($salaryHead->name) === 'basic';
    }

    private function isHraHead(SalaryHead $salaryHead): bool
    {
        $normalizedName = $this->normalizedHeadName($salaryHead->name);

        return $normalizedName === 'hra' || str_contains($normalizedName, 'houserentallowance');
    }

    private function normalizedHeadName(string $name): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower($name));
    }

    private function employeeName($employee): string
    {
        $name = collect([
            $employee->first_name,
            $employee->middle_name,
            $employee->last_name,
        ])
            ->map(fn ($namePart) => trim((string) $namePart))
            ->filter()
            ->implode(' ');

        return $name !== '' ? $name : (string) $employee->employee_no;
    }

    private function employeeCategoryLabel($category): string
    {
        return implode(', ', $this->employeeCategoryValues($category));
    }

    private function salaryRowsForCategory($salaryRows, string $category)
    {
        return $salaryRows
            ->filter(function ($salaryRow) use ($category) {
                return trim((string) $salaryRow->employee_category) === $category;
            })
            ->values();
    }

    private function employeeCategorySalary($employee, string $category): float
    {
        $fallbackSalary = round((float) $employee->salary, 2);
        $categorySalaryMap = $this->employeeCategorySalaryMap($employee->category_salaries ?? null, $fallbackSalary);

        if ($category !== '' && array_key_exists($category, $categorySalaryMap)) {
            return $categorySalaryMap[$category];
        }

        if ($category === '' && ! empty($categorySalaryMap)) {
            return (float) reset($categorySalaryMap);
        }

        return $fallbackSalary;
    }

    private function employeeCategorySalaryMap($categorySalaries, float $fallbackSalary): array
    {
        if (! is_array($categorySalaries)) {
            $decodedSalaryValues = json_decode((string) $categorySalaries, true);
            $categorySalaries = is_array($decodedSalaryValues) ? $decodedSalaryValues : [];
        }

        return collect($categorySalaries)
            ->mapWithKeys(function ($salary, $category) use ($fallbackSalary) {
                $category = trim((string) $category);

                if ($category === '') {
                    return [];
                }

                return [$category => round((float) ($salary === '' ? $fallbackSalary : $salary), 2)];
            })
            ->all();
    }

    private function employeeCategoryValues($category): array
    {
        $category = trim((string) $category);

        $decoded = json_decode($category, true);
        $categories = is_array($decoded) ? $decoded : preg_split('/\s*,\s*/', $category);

        return collect($categories)
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function currentUserId(): int
    {
        if (session()->has('user_data') && array_key_exists('user_id', session('user_data'))) {
            return (int) session('user_data')['user_id'];
        }

        return Auth::check() ? (int) Auth::id() : 0;
    }
}
