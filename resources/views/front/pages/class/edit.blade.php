@extends('front.layouts.afterlogin')
@section('content')
@php
    use App\Helpers\Helper;

    $controllerRoute = $module['controller_route'];
    $selectedUnitId = (string) old('unit_id', (($single_row) ? $single_row->unit_id : ''));
    $selectedSubjectIds = array_map('strval', (array) ($selected_subject_ids ?? []));
@endphp

<style>
    .class-subject-page {
        color: #152537;
    }
    .class-subject-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }
    .class-subject-head h2 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 800;
        color: #102f4a;
    }
    .class-subject-card {
        border: 1px solid #dbe5ef;
        border-radius: 10px;
        background: #ffffff;
        box-shadow: 0 12px 28px rgba(16, 47, 74, 0.06);
    }
    .class-subject-card .card-body {
        padding: 16px;
    }
    .class-subject-title {
        margin: 0 0 12px;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #476176;
    }
    .class-subject-page .form-label {
        font-size: 0.76rem;
        font-weight: 800;
        color: #4c6175;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .class-subject-page .form-control,
    .class-subject-page .form-select {
        border-color: #d3dfeb;
        border-radius: 8px;
        font-size: 0.86rem;
    }
    .class-subject-table {
        margin-bottom: 0;
        font-size: 0.86rem;
    }
    .class-subject-table thead th {
        background: #102f4a;
        color: #ffffff;
        border-color: #102f4a;
        font-size: 0.72rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .subject-chip-list {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .subject-chip {
        display: inline-flex;
        align-items: center;
        border: 1px solid #cfddeb;
        border-radius: 999px;
        background: #f5f9fc;
        color: #173b5a;
        padding: 4px 8px;
        font-size: 0.76rem;
        font-weight: 700;
    }
    .status-chip {
        display: inline-flex;
        border-radius: 999px;
        padding: 4px 9px;
        font-size: 0.74rem;
        font-weight: 800;
    }
    .status-chip.active {
        background: #dff5e9;
        color: #11683e;
    }
    .status-chip.blocked {
        background: #fff0ca;
        color: #866000;
    }
</style>

<div class="class-subject-page">
    <div class="class-subject-head">
        <h2>Edit Class Subject Mapping</h2>
        <a href="{{ url($controllerRoute . '/list') }}" class="btn btn-outline-secondary btn-sm">Back to List</a>
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

    <div class="card class-subject-card mb-3">
        <div class="card-body">
            <h6 class="class-subject-title">{{ $action }} Class And Subjects</h6>
            <form method="POST" action="" class="row g-3 align-items-end">
                @csrf
                <div class="col-lg-2 col-md-4">
                    <label for="unit_id" class="form-label">Unit</label>
                    <select class="form-select form-select-sm" name="unit_id" id="unit_id" required>
                        <option value="">Select</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ ($selectedUnitId === (string) $unit->id ? 'selected' : '') }}>{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-4">
                    <label for="name" class="form-label">Class Name</label>
                    <input class="form-control form-control-sm" name="name" id="name" placeholder="Write class name" value="{{ old('name', (($single_row) ? $single_row->name : '')) }}" required>
                </div>
                <div class="col-lg-5 col-md-8">
                    <label for="subject_ids" class="form-label">Class Subjects</label>
                    <select class="form-select form-select-sm" name="subject_ids[]" id="subject_ids" multiple size="4" required>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ (in_array((string) $subject->id, $selectedSubjectIds, true) ? 'selected' : '') }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <button type="submit" class="btn btn-success btn-sm w-100">{{ $action }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card class-subject-card">
        <div class="card-body">
            <h6 class="class-subject-title">Class Register</h6>
            <div class="table-responsive">
                <table id="example" class="table table-hover table-bordered align-middle class-subject-table datatable">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Unit</th>
                            <th>Class</th>
                            <th>Subjects</th>
                            <th style="width: 110px;">Status</th>
                            <th style="width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            @php
                                $encodedId = Helper::encoded($row->id);
                                $statusUrl = $controllerRoute . '/change-status/';
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row->unit_name }}</td>
                                <td class="fw-bold">{{ $row->name }}</td>
                                <td>
                                    @if($row->subjectLinks && $row->subjectLinks->count() > 0)
                                        <div class="subject-chip-list">
                                            @foreach($row->subjectLinks as $subjectLink)
                                                @if($subjectLink->subject)
                                                    <span class="subject-chip">{{ $subjectLink->subject->name }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">No subjects mapped</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-chip {{ $row->status ? 'active' : 'blocked' }}">{{ $row->status ? 'Active' : 'Blocked' }}</span>
                                </td>
                                <td>
                                    <a href="{{ url($controllerRoute . '/edit/' . $encodedId) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                    @if($row->status)
                                        <a href="javascript:void(0);" onclick="showConfirmBox('{{ $encodedId }}', '{{ $statusUrl }}', 'Are you sure you want to deactivate this record?')" class="btn btn-outline-warning btn-sm">Block</a>
                                    @else
                                        <a href="javascript:void(0);" onclick="showConfirmBox('{{ $encodedId }}', '{{ $statusUrl }}', 'Are you sure you want to activate this record?')" class="btn btn-outline-success btn-sm">Activate</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-danger text-center">No records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
