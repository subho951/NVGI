@extends('front.layouts.afterlogin')
@section('content')
<?php
$controllerRoute = $module['controller_route'];
$isEdit = !empty($row);
$leaveTenureFrom = old('leave_tenure_from', $isEdit ? optional($row->leave_tenure_from)->format('Y-m-d') : '');
$leaveTenureTo = old('leave_tenure_to', $isEdit ? optional($row->leave_tenure_to)->format('Y-m-d') : '');
$leaveTypeId = (int) old('leave_type_id', $isEdit ? $row->leave_type_id : 0);
$frontDeskLeaveCount = old('front_desk_leave_count', $isEdit ? $row->front_desk_leave_count : 0);
$groupDLeaveCount = old('group_d_leave_count', $isEdit ? $row->group_d_leave_count : 0);
$vhsTeacherLeaveCount = old('vhs_teacher_leave_count', $isEdit ? $row->vhs_teacher_leave_count : 0);
$isCarryForward = (int) old('is_carry_forward', $isEdit && $row->is_carry_forward ? 1 : 0);
?>
<style>
    .leave-allotment-form {
        --ink: #173145;
        --muted: #63798d;
        --line: #dbe5ee;
        --brand: #245b68;
    }

    .leave-form-header,
    .leave-form-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .leave-form-header {
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    .leave-form-header h2 {
        color: var(--ink);
        font-size: 1.45rem;
        font-weight: 700;
        margin: 0;
    }

    .leave-form-header p {
        color: var(--muted);
        font-size: .9rem;
        margin: 5px 0 0;
    }

    .leave-form-card .card-body {
        padding: 20px;
    }

    .leave-allotment-form .form-label {
        color: var(--ink);
        font-size: .82rem;
        font-weight: 700;
    }

    .leave-allotment-form .form-control,
    .leave-allotment-form .form-select {
        border-color: var(--line);
        border-radius: 6px;
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

    .carry-forward-switch {
        min-height: 38px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
</style>

<div class="leave-allotment-form">
    <div class="leave-form-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2>{{ $action }} Leave Allotment</h2>
                <p>Define tenure, leave type, category-wise counts, and carry-forward behavior.</p>
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

    <div class="card leave-form-card">
        <div class="card-body">
            <form method="POST" action="" class="row g-3">
                @csrf

                <div class="col-md-3">
                    <label for="leave_tenure_from" class="form-label">Leave Tenure From <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="leave_tenure_from" id="leave_tenure_from" value="{{ $leaveTenureFrom }}" required>
                    @error('leave_tenure_from') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-3">
                    <label for="leave_tenure_to" class="form-label">Leave Tenure To <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="leave_tenure_to" id="leave_tenure_to" value="{{ $leaveTenureTo }}" required>
                    @error('leave_tenure_to') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-3">
                    <label for="leave_type_id" class="form-label">Leave Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="leave_type_id" id="leave_type_id" required>
                        <option value="">Select Leave Type</option>
                        @foreach($leaveTypes as $leaveType)
                            <option value="{{ $leaveType->id }}" {{ ((int) $leaveTypeId === (int) $leaveType->id) ? 'selected' : '' }}>
                                {{ $leaveType->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('leave_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label">Carry Forward To Next Year</label>
                    <div class="carry-forward-switch">
                        <input type="hidden" name="is_carry_forward" value="0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" name="is_carry_forward" id="is_carry_forward" value="1" {{ $isCarryForward === 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_carry_forward">Yes</label>
                        </div>
                    </div>
                    @error('is_carry_forward') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-3">
                    <label for="front_desk_leave_count" class="form-label">FRONT-DESK Count</label>
                    <input type="number" class="form-control" name="front_desk_leave_count" id="front_desk_leave_count" value="{{ $frontDeskLeaveCount }}" min="0" max="365" step="0.5">
                    @error('front_desk_leave_count') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-3">
                    <label for="group_d_leave_count" class="form-label">GROUP-D Count</label>
                    <input type="number" class="form-control" name="group_d_leave_count" id="group_d_leave_count" value="{{ $groupDLeaveCount }}" min="0" max="365" step="0.5">
                    @error('group_d_leave_count') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <input type="hidden" name="tsa_teacher_leave_count" value="0">

                <div class="col-md-3">
                    <label for="vhs_teacher_leave_count" class="form-label">VHS TEACHER Count</label>
                    <input type="number" class="form-control" name="vhs_teacher_leave_count" id="vhs_teacher_leave_count" value="{{ $vhsTeacherLeaveCount }}" min="0" max="365" step="0.5">
                    @error('vhs_teacher_leave_count') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
                    <a href="{{ url($controllerRoute . '/list') }}" class="btn btn-outline-secondary px-4">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-leave-primary px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> {{ $isEdit ? 'Update' : 'Save' }} Leave Allotment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
