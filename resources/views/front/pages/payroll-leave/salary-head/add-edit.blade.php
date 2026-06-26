@extends('front.layouts.afterlogin')
@section('content')
<?php
use App\Models\SalaryHead;

$controllerRoute = $module['controller_route'];
$isEdit = !empty($row);
$type = old('type', $isEdit ? $row->type : SalaryHead::TYPE_EARNING);
$salaryHeadName = old('salary_head_name', $isEdit ? $row->name : '');
$shortDescription = old('short_description', $isEdit ? $row->short_description : '');
$calculationType = old('calculation_type', $isEdit ? $row->calculation_type : SalaryHead::CALCULATION_TYPE_FLAT);
$calculationBase = old('calculation_base', $isEdit ? $row->calculation_base : SalaryHead::BASE_FIXED_AMOUNT);
$calculationAmount = old('calculation_amount', $isEdit ? SalaryHead::formatAmount($row->calculation_amount) : '0');
$isPayslipShow = (int) old('is_payslip_show', $isEdit ? (int) $row->is_payslip_show : 1);
?>
<style>
    .salary-head-form {
        --ink: #183247;
        --muted: #66788a;
        --line: #dbe5ee;
        --brand: #1f5f73;
        --soft: #f6f9fb;
    }

    .salary-form-header,
    .salary-form-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .salary-form-header {
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    .salary-form-header h2 {
        color: var(--ink);
        font-size: 1.45rem;
        font-weight: 700;
        margin: 0;
    }

    .salary-form-header p {
        color: var(--muted);
        font-size: .9rem;
        margin: 5px 0 0;
    }

    .salary-form-card .card-body {
        padding: 20px;
    }

    .salary-head-form .form-label {
        color: var(--ink);
        font-size: .82rem;
        font-weight: 700;
    }

    .salary-head-form .form-control,
    .salary-head-form .form-select {
        border-color: var(--line);
        border-radius: 6px;
    }

    .formula-preview {
        align-items: center;
        background: var(--soft);
        border: 1px solid #edf2f6;
        border-radius: 6px;
        color: var(--ink);
        display: flex;
        font-weight: 700;
        min-height: 38px;
        padding: 8px 12px;
    }

    .payslip-switch {
        align-items: center;
        display: flex;
        min-height: 38px;
    }

    .btn-salary-primary {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        border-radius: 6px;
        font-weight: 600;
    }

    .btn-salary-primary:hover {
        background: #174b5b;
        border-color: #174b5b;
        color: #fff;
    }
</style>

<div class="salary-head-form">
    <div class="salary-form-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2>{{ $action }} Salary Head</h2>
                <p>Define earning or deduction heads and the calculation formula used for payroll.</p>
            </div>
            @if($isEdit)
                <span class="badge bg-{{ ((int) $row->status === 1) ? 'success' : 'warning' }}">
                    {{ ((int) $row->status === 1) ? 'Active' : 'Inactive' }}
                </span>
            @endif
        </div>
    </div>

    @if(session('success_message'))
        <div class="alert alert-success bg-success text-light border-0 alert-dismissible fade show autohide" role="alert">
            {{ session('success_message') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error_message'))
        <div class="alert alert-danger bg-danger text-light border-0 alert-dismissible fade show autohide" role="alert">
            {{ session('error_message') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card salary-form-card">
        <div class="card-body">
            <form method="POST" action="" class="row g-3">
                @csrf

                <div class="col-md-3">
                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="type" id="type" required>
                        @foreach($typeOptions as $value => $label)
                            <option value="{{ $value }}" {{ $type === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-5">
                    <label for="salary_head_name" class="form-label">Salary Head Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="salary_head_name" id="salary_head_name" value="{{ $salaryHeadName }}" maxlength="255" required>
                    @error('salary_head_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label for="calculation_type" class="form-label">Calculation Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="calculation_type" id="calculation_type" required>
                        @foreach($calculationTypeOptions as $value => $label)
                            <option value="{{ $value }}" {{ $calculationType === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('calculation_type') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label for="calculation_base" class="form-label">Calculation Formula <span class="text-danger">*</span></label>
                    <select class="form-select" name="calculation_base" id="calculation_base" required>
                        @foreach($calculationBaseOptions as $value => $label)
                            <option value="{{ $value }}" {{ $calculationBase === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('calculation_base') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label for="calculation_amount" class="form-label">Calculation Amount <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="calculation_amount" id="calculation_amount" value="{{ $calculationAmount }}" min="0" step="0.01" required>
                    @error('calculation_amount') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Formula Preview</label>
                    <div class="formula-preview" id="formula_preview"></div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Salary Show In Payslip</label>
                    <div class="payslip-switch">
                        <input type="hidden" name="is_payslip_show" value="0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_payslip_show" id="is_payslip_show" value="1" {{ $isPayslipShow === 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_payslip_show">Yes</label>
                        </div>
                    </div>
                    @error('is_payslip_show') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <label for="short_description" class="form-label">Short Description</label>
                    <textarea class="form-control" name="short_description" id="short_description" rows="4" maxlength="1000">{{ $shortDescription }}</textarea>
                    @error('short_description') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
                    <a href="{{ url($controllerRoute . '/list') }}" class="btn btn-outline-secondary px-4">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-salary-primary px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> {{ $isEdit ? 'Update' : 'Save' }} Salary Head
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const calculationType = document.getElementById('calculation_type');
        const calculationBase = document.getElementById('calculation_base');
        const calculationAmount = document.getElementById('calculation_amount');
        const formulaPreview = document.getElementById('formula_preview');
        const flatBases = @json($flatCalculationBases);
        const percentageBases = @json($percentageCalculationBases);

        function readableAmount(value) {
            const amount = Number.parseFloat(value);

            if (Number.isNaN(amount)) {
                return '0';
            }

            return String(Math.round(amount * 100) / 100);
        }

        function allowedBases() {
            return calculationType.value === 'PERCENTAGE' ? percentageBases : flatBases;
        }

        function updateBaseOptions() {
            const allowed = allowedBases();
            let selectedAllowed = false;

            Array.from(calculationBase.options).forEach(function (option) {
                if (!option.value) {
                    return;
                }

                const isAllowed = allowed.indexOf(option.value) !== -1;
                option.disabled = !isAllowed;
                option.hidden = !isAllowed;

                if (option.selected && isAllowed) {
                    selectedAllowed = true;
                }
            });

            if (!selectedAllowed) {
                calculationBase.value = allowed[0] || '';
            }

            updateFormulaPreview();
        }

        function updateFormulaPreview() {
            const amount = readableAmount(calculationAmount.value);
            const base = calculationBase.value;
            let text = '';

            if (base === 'FIXED_AMOUNT') {
                text = 'Flat amount = ' + amount;
            } else if (base === 'BASIC') {
                text = 'Basic x ' + amount + '%';
            } else if (base === 'GROSS_SALARY') {
                text = 'Gross Salary x ' + amount + '%';
            } else if (base === 'GROSS_BALANCE_AFTER_BASIC_HRA_DIVISOR') {
                text = '(Gross Salary - (Basic + HRA)) / ' + amount;
            }

            formulaPreview.textContent = text;
        }

        calculationType.addEventListener('change', updateBaseOptions);
        calculationBase.addEventListener('change', updateFormulaPreview);
        calculationAmount.addEventListener('input', updateFormulaPreview);

        updateBaseOptions();
    })();
</script>
@endsection
