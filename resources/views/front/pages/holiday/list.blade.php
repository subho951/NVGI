@extends('front.layouts.afterlogin')
@section('content')
<?php

use App\Helpers\Helper;
use Carbon\Carbon;

$controllerRoute = $module['controller_route'];
$month = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
$previousMonth = $month->copy()->subMonth()->format('Y-m');
$nextMonth = $month->copy()->addMonth()->format('Y-m');
?>
<style>
    .holiday-space {
        --ink: #173145;
        --muted: #63798d;
        --line: #dbe5ee;
        --brand: #245b68;
        --soft: #e9f4f6;
    }

    .holiday-header,
    .holiday-card,
    .holiday-filter {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 10px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .holiday-header {
        background: linear-gradient(135deg, #173145 0%, #245b68 100%);
        color: #fff;
        margin-bottom: 16px;
        padding: 20px;
    }

    .holiday-header h2 {
        font-size: 1.45rem;
        font-weight: 800;
        margin: 0;
    }

    .holiday-header p {
        margin: 5px 0 0;
        opacity: .88;
    }

    .holiday-filter {
        margin-bottom: 16px;
        padding: 16px;
    }

    .holiday-card .card-body {
        padding: 18px;
    }

    .holiday-table thead th {
        background: var(--soft);
        color: var(--ink);
        font-size: .75rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .holiday-table td {
        color: var(--ink);
        font-size: .87rem;
        vertical-align: middle;
    }

    .holiday-btn {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        font-weight: 700;
    }

    .holiday-btn:hover {
        background: #183f49;
        border-color: #183f49;
        color: #fff;
    }

    .scope-chip,
    .status-chip {
        border-radius: 999px;
        display: inline-flex;
        font-size: .72rem;
        font-weight: 800;
        padding: 4px 9px;
        white-space: nowrap;
    }

    .scope-chip {
        background: #eef4ff;
        color: #275a9f;
        margin: 2px;
    }

    .status-chip.active {
        background: #e9f8ef;
        color: #1f7a3d;
    }

    .status-chip.inactive {
        background: #fff4e5;
        color: #98620d;
    }

    .holiday-summary {
        color: var(--muted);
        font-size: .82rem;
        font-weight: 700;
    }
</style>

<div class="holiday-space">
    <div class="holiday-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2>Holiday Management</h2>
                <p>Maintain month-wise holidays used by employee rosters, attendance and salary generation.</p>
            </div>
            <a href="{{ url($controllerRoute . '/add') }}?month={{ $selectedMonth }}" class="btn btn-light fw-bold">
                <i class="fa-solid fa-plus me-1"></i> Add Holiday
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

    <div class="holiday-filter">
        <form method="GET" action="{{ url($controllerRoute . '/list') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label for="month" class="form-label fw-bold">Holiday Month</label>
                <input type="month" class="form-control" name="month" id="month" value="{{ $selectedMonth }}" required>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn holiday-btn px-4">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> Show Month
                </button>
            </div>
            <div class="col-md-auto">
                <a href="{{ url($controllerRoute . '/list') }}?month={{ $previousMonth }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-angle-left"></i> Previous
                </a>
            </div>
            <div class="col-md-auto">
                <a href="{{ url($controllerRoute . '/list') }}?month={{ $nextMonth }}" class="btn btn-outline-secondary">
                    Next <i class="fa-solid fa-angle-right"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="card holiday-card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="mb-0 fw-bold">{{ $selectedMonthLabel }}</h5>
                <div class="holiday-summary">
                    {{ $rows->count() }} record(s), {{ $activeHolidayCount }} active
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover holiday-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Holiday</th>
                            <th>Branch Scope</th>
                            <th>Category Scope</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $index => $row)
                            @php
                                $encodedId = Helper::encoded($row->id);
                                $statusUrl = $controllerRoute . '/change-status/';
                                $deleteUrl = $controllerRoute . '/delete/';
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold">{{ $row->holiday_date->format('d-m-Y') }}</td>
                                <td>{{ $row->holiday_date->format('l') }}</td>
                                <td>{{ $row->name }}</td>
                                <td>
                                    <span class="scope-chip">{{ $row->branch_name ?: 'All Branches' }}</span>
                                </td>
                                <td>
                                    <span class="scope-chip">{{ $row->category ?: 'All Categories' }}</span>
                                </td>
                                <td>
                                    @if((int) $row->status === 1)
                                        <span class="status-chip active">Active</span>
                                    @else
                                        <span class="status-chip inactive">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    <a href="{{ url($controllerRoute . '/edit/' . $encodedId) }}" class="text-primary">Edit</a>
                                    |
                                    @if((int) $row->status === 1)
                                        <a href="javascript:void(0);"
                                           onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Deactivate this holiday? Attendance will be recalculated for the date.')"
                                           class="text-success">Active</a>
                                    @else
                                        <a href="javascript:void(0);"
                                           onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Activate this holiday? Attendance will be recalculated for the date.')"
                                           class="text-warning">Inactive</a>
                                    @endif
                                    |
                                    <a href="javascript:void(0);"
                                       onclick="showConfirmBox('<?= $encodedId ?>', '<?= $deleteUrl ?>', 'Delete this holiday? Attendance will be recalculated for the date.')"
                                       class="text-danger">Delete</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No holiday configured for {{ $selectedMonthLabel }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
