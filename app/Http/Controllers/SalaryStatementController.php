<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\SalaryGeneration;
use App\Models\SalaryStatement;
use App\Services\SiteAuthService;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalaryStatementController extends Controller
{
    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = [
            'title' => 'Salary Statement',
            'controller' => 'SalaryStatementController',
            'controller_route' => 'payroll-leave/salary-statement',
            'primary_key' => 'id',
        ];

        $this->siteAuthService = new SiteAuthService();
    }

    public function list(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'];
        $pageName = 'payroll-leave.salary-statement.list';
        $hasSearched = $request->has('search');

        if ($hasSearched) {
            $request->validate($this->filterRules());
        }

        $filters = $this->filtersFromRequest($request);

        $data['rows'] = $hasSearched ? $this->searchRows($filters) : collect();
        $data['filters'] = $filters;
        $data['hasSearched'] = $hasSearched;
        $data['monthOptions'] = $this->monthOptions();
        $data['yearOptions'] = $this->yearOptions((int) $filters['year']);
        $data['branchOptions'] = $this->branchOptions();
        $data['categoryOptions'] = $this->categoryOptions();
        $data['action'] = 'List';

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function generate(Request $request)
    {
        $request->validate($this->filterRules() + [
            'salary_generation_ids' => ['required', 'array', 'min:1'],
            'salary_generation_ids.*' => ['integer'],
        ]);

        $filters = $this->filtersFromRequest($request);
        $salaryGenerationIds = array_values(array_unique(array_map('intval', $request->input('salary_generation_ids', []))));
        $salaryRows = $this->salaryGenerationRows($filters, $salaryGenerationIds);

        if ($salaryRows->isEmpty()) {
            return redirect($this->data['controller_route'].'/list?'.$this->queryString($filters))->with('error_message', 'No generated salary found for selected employee(s) !!!');
        }

        $employees = Employee::whereIn('id', $salaryRows->pluck('employee_id')->map(fn ($id) => (int) $id)->all())
            ->get()
            ->keyBy('id');
        $userId = $this->currentUserId();

        DB::transaction(function () use ($salaryRows, $employees, $userId) {
            foreach ($salaryRows as $salaryRow) {
                $employee = $employees->get((int) $salaryRow->employee_id);

                SalaryStatement::updateOrCreate(
                    [
                        'salary_month' => (int) $salaryRow->salary_month,
                        'salary_year' => (int) $salaryRow->salary_year,
                        'branch_name' => $salaryRow->branch_name,
                        'employee_id' => (int) $salaryRow->employee_id,
                        'employee_category' => $salaryRow->employee_category,
                    ],
                    [
                        'salary_generation_id' => (int) $salaryRow->id,
                        'employee_no' => $salaryRow->employee_no,
                        'employee_name' => $salaryRow->employee_name,
                        'salary_amount' => round((float) $salaryRow->net_salary),
                        'bank_name' => $this->nullableEmployeeValue($employee, 'bank_name'),
                        'bank_branch' => $this->nullableEmployeeValue($employee, 'bank_branch'),
                        'account_no' => $this->nullableEmployeeValue($employee, 'account_no'),
                        'ifsc_code' => $this->nullableEmployeeValue($employee, 'ifsc_code'),
                        'account_type' => $this->nullableEmployeeValue($employee, 'account_type'),
                        'status' => 1,
                        'generated_by' => $userId,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]
                );
            }
        });

        $skippedCount = count($salaryGenerationIds) - $salaryRows->count();
        $message = 'Salary statement generated for '.$salaryRows->count().' employee(s).';

        if ($skippedCount > 0) {
            $message .= ' Skipped '.$skippedCount.' employee(s) without generated salary.';
        }

        return redirect($this->data['controller_route'].'/generated-list')->with('success_message', $message);
    }

    public function generatedList(Request $request)
    {
        $data['module'] = $this->data;
        $title = 'Generated Salary Statement';
        $pageName = 'payroll-leave.salary-statement.generated-list';
        $data['rows'] = SalaryStatement::where('status', '!=', 3)
            ->select([
                'salary_month',
                'salary_year',
                'branch_name',
                'employee_category',
                DB::raw('COUNT(*) as employee_count'),
                DB::raw('MAX(updated_at) as last_generated_at'),
            ])
            ->groupBy('salary_month', 'salary_year', 'branch_name', 'employee_category')
            ->orderByDesc('salary_year')
            ->orderByDesc('salary_month')
            ->orderBy('branch_name')
            ->orderBy('employee_category')
            ->get();
        $data['monthOptions'] = $this->monthOptions();
        $data['action'] = 'Generated List';

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function employees(Request $request)
    {
        $request->validate($this->filterRules());

        $filters = $this->filtersFromRequest($request);
        $data['module'] = $this->data;
        $data['filters'] = $filters;
        $data['rows'] = $this->statementRows($filters);
        $data['monthOptions'] = $this->monthOptions();
        $data['action'] = 'Employees';

        $title = 'Salary Statement Employees';
        $pageName = 'payroll-leave.salary-statement.employees';
        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function downloadExcel(Request $request)
    {
        $request->validate($this->filterRules());
        $filters = $this->filtersFromRequest($request);
        $rows = $this->statementRows($filters);

        if ($rows->isEmpty()) {
            return redirect($this->data['controller_route'].'/generated-list')->with('error_message', 'No salary statement found for selected period !!!');
        }

        $html = view('front.pages.payroll-leave.salary-statement.excel', [
            'rows' => $rows,
            'filters' => $filters,
            'monthOptions' => $this->monthOptions(),
        ])->render();
        $fileName = 'salary-statement-'.$filters['year'].'-'.$filters['month'].'-'.$this->safeFilePart($filters['branch_name']).'-'.$this->safeFilePart($filters['category']).'.xls';

        return response("\xEF\xBB\xBF".$html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function downloadPdf(Request $request)
    {
        $request->validate($this->filterRules());
        $filters = $this->filtersFromRequest($request);
        $rows = $this->statementRows($filters);

        if ($rows->isEmpty()) {
            return redirect($this->data['controller_route'].'/generated-list')->with('error_message', 'No salary statement found for selected period !!!');
        }

        $html = view('front.pages.payroll-leave.salary-statement.statement-pdf', [
            'rows' => $rows,
            'filters' => $filters,
            'monthOptions' => $this->monthOptions(),
        ])->render();
        $fileName = 'salary-statement-'.$filters['year'].'-'.$filters['month'].'-'.$this->safeFilePart($filters['branch_name']).'-'.$this->safeFilePart($filters['category']).'.pdf';

        return $this->downloadPdfResponse($html, $fileName, 'landscape');
    }

    public function paySlip(Request $request, string $statement)
    {
        $statementId = (int) Helper::decoded($statement);
        $salaryStatement = SalaryStatement::where('status', '!=', 3)->findOrFail($statementId);
        $salaryGeneration = SalaryGeneration::where('status', '!=', 3)
            ->where('id', '=', (int) $salaryStatement->salary_generation_id)
            ->first();

        if (! $salaryGeneration) {
            $salaryGeneration = SalaryGeneration::where('status', '!=', 3)
                ->where('salary_month', '=', (int) $salaryStatement->salary_month)
                ->where('salary_year', '=', (int) $salaryStatement->salary_year)
                ->where('branch_name', '=', $salaryStatement->branch_name)
                ->where('employee_id', '=', (int) $salaryStatement->employee_id)
                ->where('employee_category', '=', $salaryStatement->employee_category)
                ->firstOrFail();
        }

        $html = view('front.pages.payroll-leave.salary-statement.pay-slip-pdf', [
            'statement' => $salaryStatement,
            'salaryGeneration' => $salaryGeneration,
            'monthOptions' => $this->monthOptions(),
        ])->render();
        $fileName = 'pay-slip-'.$this->safeFilePart((string) ($salaryStatement->employee_no ?: $salaryStatement->employee_id)).'-'.$salaryStatement->salary_year.'-'.$salaryStatement->salary_month.'.pdf';

        return $this->downloadPdfResponse($html, $fileName, 'portrait');
    }

    private function searchRows(array $filters)
    {
        $salaryRows = $this->salaryGenerationRows($filters);

        if ($salaryRows->isEmpty()) {
            return collect();
        }

        $employees = Employee::whereIn('id', $salaryRows->pluck('employee_id')->map(fn ($id) => (int) $id)->all())
            ->get()
            ->keyBy('id');
        $statements = SalaryStatement::where('status', '!=', 3)
            ->whereIn('salary_generation_id', $salaryRows->pluck('id')->map(fn ($id) => (int) $id)->all())
            ->get()
            ->keyBy('salary_generation_id');

        return $salaryRows->map(function ($salaryRow) use ($employees, $statements) {
            $employee = $employees->get((int) $salaryRow->employee_id);
            $statement = $statements->get((int) $salaryRow->id);

            return (object) [
                'salary_generation_id' => (int) $salaryRow->id,
                'statement_id' => $statement ? (int) $statement->id : 0,
                'employee_id' => (int) $salaryRow->employee_id,
                'employee_no' => $salaryRow->employee_no,
                'employee_name' => $salaryRow->employee_name,
                'salary_amount' => round((float) $salaryRow->net_salary),
                'bank_name' => $this->nullableEmployeeValue($employee, 'bank_name'),
                'bank_branch' => $this->nullableEmployeeValue($employee, 'bank_branch'),
                'account_no' => $this->nullableEmployeeValue($employee, 'account_no'),
                'ifsc_code' => $this->nullableEmployeeValue($employee, 'ifsc_code'),
                'account_type' => $this->nullableEmployeeValue($employee, 'account_type'),
                'statement_generated_at' => $statement ? $statement->updated_at : null,
            ];
        });
    }

    private function salaryGenerationRows(array $filters, ?array $salaryGenerationIds = null)
    {
        $query = SalaryGeneration::where('status', '!=', 3)
            ->where('salary_month', '=', (int) $filters['month'])
            ->where('salary_year', '=', (int) $filters['year'])
            ->where('branch_name', '=', $filters['branch_name'])
            ->where('employee_category', '=', $filters['category']);

        if (is_array($salaryGenerationIds)) {
            $query->whereIn('id', $salaryGenerationIds);
        }

        return $query
            ->orderBy('employee_name')
            ->orderBy('employee_no')
            ->get();
    }

    private function statementRows(array $filters)
    {
        return SalaryStatement::where('status', '!=', 3)
            ->where('salary_month', '=', (int) $filters['month'])
            ->where('salary_year', '=', (int) $filters['year'])
            ->where('branch_name', '=', $filters['branch_name'])
            ->where('employee_category', '=', $filters['category'])
            ->orderBy('employee_name')
            ->orderBy('employee_no')
            ->get();
    }

    private function filterRules(): array
    {
        return [
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2026,2030'],
            'branch_name' => ['required', 'string', Rule::in($this->branchOptions())],
            'category' => ['required', 'string', Rule::in($this->categoryOptions())],
        ];
    }

    private function filtersFromRequest(Request $request): array
    {
        return [
            'month' => (int) $request->input('month', now()->month),
            'year' => (int) $request->input('year', now()->year),
            'branch_name' => trim((string) $request->input('branch_name', '')),
            'category' => trim((string) $request->input('category', '')),
        ];
    }

    private function monthOptions(): array
    {
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[$month] = Carbon::create(2000, $month, 1)->format('F');
        }

        return $months;
    }

    private function yearOptions(int $selectedYear): array
    {
        return range(2026, 2030);
    }

    private function branchOptions(): array
    {
        $branchNames = Branch::where('status', '!=', 3)
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->values();
        $preferredBranchNames = collect(['Bibirhat', 'Mukundapur', 'Rajarhat'])
            ->filter(fn ($branchName) => $branchNames->contains($branchName))
            ->values();

        return ($preferredBranchNames->isNotEmpty() ? $preferredBranchNames : $branchNames)->all();
    }

    private function categoryOptions(): array
    {
        return [
            'VHS TEACHER',
            'TSA TEACHER',
            'FRONT-DESK',
            'GROUP-D',
        ];
    }

    private function queryString(array $filters): string
    {
        return http_build_query($filters + ['search' => 1]);
    }

    private function nullableEmployeeValue($employee, string $field): string
    {
        if (! $employee) {
            return '';
        }

        return trim((string) ($employee->{$field} ?? ''));
    }

    private function downloadPdfResponse(string $html, string $fileName, string $orientation)
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        return response($dompdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
    }

    private function safeFilePart(string $value): string
    {
        $value = preg_replace('/[^a-z0-9]+/i', '-', trim($value));

        return trim((string) $value, '-') ?: 'salary-statement';
    }

    private function currentUserId(): int
    {
        if (session()->has('user_data') && array_key_exists('user_id', session('user_data'))) {
            return (int) session('user_data')['user_id'];
        }

        return Auth::check() ? (int) Auth::id() : 0;
    }
}
