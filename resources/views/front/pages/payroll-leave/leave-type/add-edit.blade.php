@extends('front.layouts.afterlogin')
@section('content')
<?php
$controllerRoute = $module['controller_route'];
$isEdit = !empty($row);
$leaveTypeName = old('leave_type_name', $isEdit ? $row->name : '');
$leaveTypeDescription = old('leave_type_description', $isEdit ? $row->description : '');
?>
<style>
    .leave-type-form {
        --ink: #183247;
        --muted: #66788a;
        --line: #dbe5ee;
        --brand: #1f5f73;
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

    .leave-type-form .form-label {
        color: var(--ink);
        font-size: .82rem;
        font-weight: 700;
    }

    .leave-type-form .form-control {
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
        background: #174b5b;
        border-color: #174b5b;
        color: #fff;
    }
</style>

<div class="leave-type-form">
    <div class="leave-form-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2>{{ $action }} Leave Type</h2>
                <p>Set the leave type name and description used in payroll and leave workflows.</p>
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
                <div class="col-md-6">
                    <label for="leave_type_name" class="form-label">Leave Type Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="leave_type_name" id="leave_type_name" value="{{ $leaveTypeName }}" maxlength="255" required>
                    @error('leave_type_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <label for="leave_type_description" class="form-label">Leave Type Description</label>
                    <textarea class="form-control" name="leave_type_description" id="leave_type_description" rows="4" maxlength="1000">{{ $leaveTypeDescription }}</textarea>
                    @error('leave_type_description') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
                    <a href="{{ url($controllerRoute . '/list') }}" class="btn btn-outline-secondary px-4">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn btn-leave-primary px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> {{ $isEdit ? 'Update' : 'Save' }} Leave Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
