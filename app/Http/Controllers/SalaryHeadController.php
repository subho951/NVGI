<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\SalaryHead;
use App\Services\SiteAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SalaryHeadController extends Controller
{
    protected $siteAuthService;
    protected $data;

    public function __construct()
    {
        $this->data = [
            'title' => 'Salary Head',
            'controller' => 'SalaryHeadController',
            'controller_route' => 'payroll-leave/salary-head',
            'primary_key' => 'id',
        ];

        $this->siteAuthService = new SiteAuthService();
    }

    public function list(Request $request)
    {
        $data['module'] = $this->data;
        $title = $this->data['title'].' List';
        $pageName = 'payroll-leave.salary-head.list';
        $data['rows'] = SalaryHead::where('status', '!=', 3)->orderBy('id', 'DESC')->get();
        $data['action'] = 'List';

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function add(Request $request)
    {
        $data = $this->formData('Add');
        $title = $this->data['title'].' Add';
        $pageName = 'payroll-leave.salary-head.add-edit';

        if ($request->isMethod('post')) {
            $this->validateSalaryHead($request);

            SalaryHead::create($this->payload($request) + [
                'status' => 1,
                'created_by' => $this->currentUserId(),
                'updated_by' => $this->currentUserId(),
            ]);

            return redirect($this->data['controller_route'].'/list')->with('success_message', $this->data['title'].' added successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function edit(Request $request, $id)
    {
        $id = Helper::decoded($id);
        $row = SalaryHead::where($this->data['primary_key'], '=', $id)
            ->where('status', '!=', 3)
            ->first();

        if (! $row) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Salary head not found !!!');
        }

        $data = $this->formData('Edit', $row);
        $title = $this->data['title'].' Edit';
        $pageName = 'payroll-leave.salary-head.add-edit';

        if ($request->isMethod('post')) {
            $this->validateSalaryHead($request, (int) $row->id);

            $row->update($this->payload($request) + [
                'updated_by' => $this->currentUserId(),
            ]);

            return redirect($this->data['controller_route'].'/list')->with('success_message', $this->data['title'].' updated successfully !!!');
        }

        $data = $this->siteAuthService->admin_after_login_layout($title, $pageName, $data);

        return view('front.pages.'.$pageName, $data);
    }

    public function change_status(Request $request, $id)
    {
        $id = Helper::decoded($id);
        $model = SalaryHead::where($this->data['primary_key'], '=', $id)
            ->where('status', '!=', 3)
            ->first();

        if (! $model) {
            return redirect($this->data['controller_route'].'/list')->with('error_message', 'Salary head not found !!!');
        }

        if ((int) $model->status === 1) {
            $model->status = 0;
            $msg = 'deactivated';
        } else {
            $model->status = 1;
            $msg = 'activated';
        }

        $model->updated_by = $this->currentUserId();
        $model->save();

        return redirect($this->data['controller_route'].'/list')->with('success_message', $this->data['title'].' '.$msg.' successfully !!!');
    }

    private function formData(string $action, $row = null): array
    {
        return [
            'module' => $this->data,
            'row' => $row,
            'action' => $action,
            'typeOptions' => SalaryHead::typeOptions(),
            'calculationTypeOptions' => SalaryHead::calculationTypeOptions(),
            'calculationBaseOptions' => SalaryHead::calculationBaseOptions(),
            'flatCalculationBases' => SalaryHead::flatCalculationBases(),
            'percentageCalculationBases' => SalaryHead::percentageCalculationBases(),
        ];
    }

    private function validateSalaryHead(Request $request, $ignoreId = null): void
    {
        $validator = Validator::make($request->all(), $this->validationRules($ignoreId));

        $validator->after(function ($validator) use ($request) {
            $calculationType = (string) $request->calculation_type;
            $calculationBase = (string) $request->calculation_base;
            $calculationAmount = (float) $request->calculation_amount;

            if ($calculationType === SalaryHead::CALCULATION_TYPE_FLAT && ! in_array($calculationBase, SalaryHead::flatCalculationBases(), true)) {
                $validator->errors()->add('calculation_base', 'Flat salary heads can use fixed amount or gross balance formula only.');
            }

            if ($calculationType === SalaryHead::CALCULATION_TYPE_PERCENTAGE && ! in_array($calculationBase, SalaryHead::percentageCalculationBases(), true)) {
                $validator->errors()->add('calculation_base', 'Percentage salary heads can use Basic or Gross Salary only.');
            }

            if ($calculationType === SalaryHead::CALCULATION_TYPE_PERCENTAGE && $calculationAmount > 100) {
                $validator->errors()->add('calculation_amount', 'Percentage amount may not be greater than 100.');
            }

            if ($calculationBase === SalaryHead::BASE_GROSS_BALANCE_AFTER_BASIC_HRA_DIVISOR && $calculationAmount <= 0) {
                $validator->errors()->add('calculation_amount', 'Calculation amount must be greater than 0 for the gross balance formula.');
            }
        });

        $validator->validate();
    }

    private function validationRules($ignoreId = null): array
    {
        $nameRule = Rule::unique('salary_heads', 'name')->whereNull('deleted_at');

        if ($ignoreId !== null) {
            $nameRule->ignore($ignoreId);
        }

        return [
            'type' => ['required', Rule::in(array_keys(SalaryHead::typeOptions()))],
            'salary_head_name' => ['required', 'string', 'max:255', $nameRule],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'calculation_type' => ['required', Rule::in(array_keys(SalaryHead::calculationTypeOptions()))],
            'calculation_base' => ['required', Rule::in(array_keys(SalaryHead::calculationBaseOptions()))],
            'calculation_amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'is_payslip_show' => ['nullable', 'boolean'],
        ];
    }

    private function payload(Request $request): array
    {
        return [
            'type' => (string) $request->type,
            'name' => trim((string) $request->salary_head_name),
            'short_description' => $this->nullableText($request->short_description),
            'calculation_type' => (string) $request->calculation_type,
            'calculation_base' => (string) $request->calculation_base,
            'calculation_amount' => round((float) $request->calculation_amount, 2),
            'is_payslip_show' => $request->boolean('is_payslip_show') ? 1 : 0,
        ];
    }

    private function nullableText($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function currentUserId(): int
    {
        if (session()->has('user_data') && array_key_exists('user_id', session('user_data'))) {
            return (int) session('user_data')['user_id'];
        }

        return Auth::check() ? (int) Auth::id() : 0;
    }
}
