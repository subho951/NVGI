@extends('front.layouts.afterlogin')
@section('content')
<?php
$controllerRoute = $module['controller_route'];
$isEdit = ! empty($row);
$defaultDate = $selectedMonth === date('Y-m') ? date('Y-m-d') : $selectedMonth . '-01';
$holidayDate = old('holiday_date', $isEdit ? $row->holiday_date->format('Y-m-d') : $defaultDate);
$holidayName = old('name', $isEdit ? $row->name : '');
$branchName = old('branch_name', $isEdit ? ($row->branch_name ?? '') : '');
$category = old('category', $isEdit ? ($row->category ?? '') : '');
$returnMonth = substr($holidayDate, 0, 7) ?: $selectedMonth;
?>
<style>
    .holiday-form-space {
        --ink: #173145;
        --muted: #63798d;
        --line: #dbe5ee;
        --brand: #245b68;
    }

    .holiday-form-header,
    .holiday-form-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 10px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .holiday-form-header {
        background: linear-gradient(135deg, #173145 0%, #245b68 100%);
        color: #fff;
        margin-bottom: 16px;
        padding: 20px;
    }

    .holiday-form-header h2 {
        font-size: 1.45rem;
        font-weight: 800;
        margin: 0;
    }

    .holiday-form-header p {
        margin: 5px 0 0;
        opacity: .88;
    }

    .holiday-form-card .card-body {
        padding: 20px;
    }

    .holiday-form-space .form-label {
        color: var(--ink);
        font-size: .82rem;
        font-weight: 700;
    }

    .holiday-form-space .form-control,
    .holiday-form-space .form-select {
        border-color: var(--line);
        border-radius: 7px;
        min-height: 42px;
    }

    .holiday-form-btn {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        font-weight: 700;
    }

    .holiday-form-btn:hover {
        background: #183f49;
        border-color: #183f49;
        color: #fff;
    }

    .scope-note {
        background: #f3f8fa;
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--muted);
        font-size: .84rem;
        padding: 12px;
    }
</style>

<div class="holiday-form-space">
    <div class="holiday-form-header">
        <h2>{{ $action }} Holiday</h2>
        <p>Configure a global holiday or limit it to one branch, one roster category, or both.</p>
    </div>

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

    <div class="card holiday-form-card">
        <div class="card-body">
            <form method="POST" action="" class="row g-3">
                @csrf

                <div class="col-md-4">
                    <label for="holiday_date" class="form-label">Holiday Date <span class="text-danger">*</span></label>
                    <input type="date"
                           class="form-control"
                           name="holiday_date"
                           id="holiday_date"
                           value="{{ $holidayDate }}"
                           required>
                </div>

                <div class="col-md-8">
                    <label for="name" class="form-label">Holiday Name <span class="text-danger">*</span></label>
                    <input type="text"
                           class="form-control"
                           name="name"
                           id="name"
                           maxlength="160"
                           placeholder="Enter holiday name"
                           value="{{ $holidayName }}"
                           required>
                </div>

                <div class="col-md-6">
                    <label for="branch_name" class="form-label">Branch Scope</label>
                    <select class="form-select" name="branch_name" id="branch_name">
                        <option value="">All Branches</option>
                        @foreach($branchOptions as $branchOption)
                            <option value="{{ $branchOption }}" {{ $branchName === $branchOption ? 'selected' : '' }}>
                                {{ $branchOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="category" class="form-label">Roster Category Scope</label>
                    <select class="form-select" name="category" id="category">
                        <option value="">All Categories</option>
                        @foreach($categoryOptions as $categoryOption)
                            <option value="{{ $categoryOption }}" {{ $category === $categoryOption ? 'selected' : '' }}>
                                {{ $categoryOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <div class="scope-note">
                        Leaving both scope fields as “All” makes the date a paid holiday for every roster category and branch. Holiday dates are excluded from absence and late calculations.
                    </div>
                </div>

                <div class="col-12 d-flex flex-wrap justify-content-end gap-2">
                    <a href="{{ url($controllerRoute . '/list') }}?month={{ $returnMonth }}" class="btn btn-outline-secondary px-4">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back
                    </a>
                    <button type="submit" class="btn holiday-form-btn px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i>
                        {{ $isEdit ? 'Update Holiday' : 'Save Holiday' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
