@extends('front.layouts.afterlogin')
@section('content')
<?php
$controllerRoute = $module['controller_route'];
$selectedEmployeeId = old('employee_id', $row->employee_id ?? '');
$leaveFromDate = old('leave_from_date', $row && $row->leave_from_date ? $row->leave_from_date->format('Y-m-d') : '');
$leaveToDate = old('leave_to_date', $row && $row->leave_to_date ? $row->leave_to_date->format('Y-m-d') : '');
$noOfDays = old('no_of_days', $row->no_of_days ?? '');
$todayDate = date('Y-m-d');
$applyDate = old('apply_date', $row && $row->apply_date ? $row->apply_date->format('Y-m-d') : date('Y-m-d'));
$remarks = old('remarks', $row->remarks ?? '');
$formatCount = function ($value) {
    $formatted = number_format((float) $value, 2, '.', '');

    return rtrim(rtrim($formatted, '0'), '.');
};
?>
<style>
    .leave-application-form {
        --ink: #173145;
        --muted: #63798d;
        --line: #dbe5ee;
        --brand: #245b68;
        --soft: #e9f4f6;
    }

    .leave-application-header,
    .leave-application-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .leave-application-header {
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    .leave-application-title {
        color: var(--ink);
        font-size: 1.45rem;
        font-weight: 700;
        margin: 0;
    }

    .leave-application-subtitle {
        color: var(--muted);
        font-size: .9rem;
        margin: 5px 0 0;
    }

    .leave-application-card .card-body {
        padding: 20px;
    }

    .leave-application-form .form-label {
        color: var(--ink);
        font-size: .82rem;
        font-weight: 700;
    }

    .leave-application-form .form-control,
    .leave-application-form .form-select {
        border-color: var(--line);
        border-radius: 7px;
        min-height: 40px;
    }

    .btn-leave-primary {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        border-radius: 6px;
        font-weight: 600;
    }

    .btn-leave-primary:hover {
        background: #1a4651;
        border-color: #1a4651;
        color: #fff;
    }

    .cl-balance-preview {
        align-items: center;
        background: #f7fafc;
        border: 1px solid var(--line);
        border-radius: 7px;
        display: flex;
        justify-content: space-between;
        margin-top: 8px;
        min-height: 40px;
        padding: 8px 12px;
    }

    .cl-balance-label {
        color: var(--muted);
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .cl-balance-value {
        color: var(--brand);
        font-size: 1rem;
        font-weight: 900;
    }
</style>

<div class="leave-application-form">
    <div class="leave-application-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="leave-application-title">{{ $action }} Leave Application</h2>
                <p class="leave-application-subtitle">Create employee leave application against CL leave type.</p>
            </div>
            <a href="{{ url($controllerRoute . '/list') }}" class="btn btn-leave-primary px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back
            </a>
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

    <div class="card leave-application-card">
        <div class="card-body">
            <form method="POST" action="">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="employee_id" class="form-label">Select Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" id="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                @php
                                    $employeeName = trim(collect([$employee->first_name, $employee->middle_name, $employee->last_name])->filter()->implode(' '));
                                    $employeeClBalance = $formatCount($employee->cl_balance ?? 0);
                                @endphp
                                <option value="{{ $employee->id }}"
                                        data-cl-balance="{{ $employeeClBalance }}"
                                        {{ (string) $selectedEmployeeId === (string) $employee->id ? 'selected' : '' }}>
                                    {{ $employee->employee_no }} - {{ $employeeName }}
                                </option>
                            @endforeach
                        </select>
                        <div class="cl-balance-preview" id="clBalancePreview">
                            <span class="cl-balance-label">Available CL Balance</span>
                            <span class="cl-balance-value" id="clBalanceValue">--</span>
                        </div>
                        @error('employee_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Leave Type</label>
                        <input type="text" class="form-control" value="{{ $clLeaveType->name }}" readonly>
                    </div>

                    <div class="col-md-4">
                        <label for="leave_from_date" class="form-label">Leave From Date <span class="text-danger">*</span></label>
                        <input type="date" name="leave_from_date" id="leave_from_date" class="form-control" value="{{ $leaveFromDate }}" required>
                        @error('leave_from_date') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="leave_to_date" class="form-label">Leave To Date <span class="text-danger">*</span></label>
                        <input type="date" name="leave_to_date" id="leave_to_date" class="form-control" value="{{ $leaveToDate }}" required>
                        @error('leave_to_date') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="no_of_days" class="form-label">No Of Days Leave <span class="text-danger">*</span></label>
                        <input type="number" step="0.25" min="0.25" max="365" name="no_of_days" id="no_of_days" class="form-control" value="{{ $noOfDays }}" required>
                        @error('no_of_days') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="apply_date" class="form-label">Leave Apply Date <span class="text-danger">*</span></label>
                        <input type="date" name="apply_date" id="apply_date" class="form-control" value="{{ $applyDate }}" max="{{ $todayDate }}" required>
                        @error('apply_date') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-8">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea name="remarks" id="remarks" rows="3" class="form-control">{{ $remarks }}</textarea>
                        @error('remarks') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-leave-primary px-4">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fromInput = document.getElementById('leave_from_date');
        const toInput = document.getElementById('leave_to_date');
        const daysInput = document.getElementById('no_of_days');
        const employeeInput = document.getElementById('employee_id');
        const clBalanceValue = document.getElementById('clBalanceValue');

        const calculateDays = function () {
            if (!fromInput.value || !toInput.value) {
                return;
            }

            const fromDate = new Date(fromInput.value + 'T00:00:00');
            const toDate = new Date(toInput.value + 'T00:00:00');

            if (Number.isNaN(fromDate.getTime()) || Number.isNaN(toDate.getTime()) || toDate < fromDate) {
                return;
            }

            const diffDays = Math.floor((toDate - fromDate) / 86400000) + 1;
            daysInput.value = diffDays;
        };

        const showClBalance = function () {
            const selectedOption = employeeInput.options[employeeInput.selectedIndex];
            const balance = selectedOption ? selectedOption.getAttribute('data-cl-balance') : '';

            clBalanceValue.textContent = employeeInput.value && balance !== null && balance !== '' ? balance : '--';
        };

        fromInput.addEventListener('change', calculateDays);
        toInput.addEventListener('change', calculateDays);
        employeeInput.addEventListener('change', showClBalance);
        showClBalance();
    });
</script>
@endsection
