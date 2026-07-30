@extends('front.layouts.afterlogin')
@section('content')
<?php
use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
$formatCount = function ($value) {
    $formatted = number_format((float) $value, 2, '.', '');

    return rtrim(rtrim($formatted, '0'), '.');
};
?>
<style>
    .leave-allotment-space {
        --ink: #173145;
        --muted: #63798d;
        --line: #dbe5ee;
        --brand: #245b68;
        --soft: #e9f4f6;
    }

    .leave-allotment-header,
    .leave-allotment-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(21, 42, 61, .08);
    }

    .leave-allotment-header {
        padding: 18px 20px;
        margin-bottom: 18px;
    }

    .leave-allotment-title {
        color: var(--ink);
        font-size: 1.45rem;
        font-weight: 700;
        margin: 0;
    }

    .leave-allotment-subtitle {
        color: var(--muted);
        font-size: .9rem;
        margin: 5px 0 0;
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

    .leave-allotment-card .card-body {
        padding: 18px;
    }

    .leave-allotment-table thead th {
        background: var(--soft);
        color: var(--ink);
        font-size: .76rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .leave-allotment-table td {
        vertical-align: middle;
        font-size: .88rem;
    }

    .count-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 4px 8px;
        min-width: 180px;
    }

    .count-chip {
        color: var(--ink);
        background: #f6f9fb;
        border: 1px solid #edf2f6;
        border-radius: 6px;
        padding: 4px 7px;
        white-space: nowrap;
    }

    .status-pill {
        display: inline-flex;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        padding: 5px 10px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .status-active {
        background: #e5f7ed;
        color: #167648;
    }

    .status-inactive {
        background: #fff0df;
        color: #a05b10;
    }

    .leave-action {
        font-weight: 600;
        margin-right: 8px;
        white-space: nowrap;
    }

    .assign-button {
        align-items: center;
        background: var(--brand);
        border: 1px solid var(--brand);
        border-radius: 6px;
        color: #fff !important;
        display: inline-flex;
        font-size: .8rem;
        gap: 5px;
        justify-content: center;
        margin-top: 6px;
        padding: 6px 10px;
        text-decoration: none;
    }

    .assign-button:hover {
        background: #1a4651;
        border-color: #1a4651;
        color: #fff !important;
        text-decoration: none;
    }
</style>

<div class="leave-allotment-space">
    <div class="leave-allotment-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="leave-allotment-title">Leave Allotments</h2>
                <p class="leave-allotment-subtitle">Create category-wise leave allotments and assign leave balance to employees.</p>
            </div>
            <a href="{{ url($controllerRoute . '/add') }}" class="btn btn-leave-primary px-3">
                <i class="fa-solid fa-plus me-1"></i> Add Leave Allotment
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

    <div class="card leave-allotment-card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-bordered table-hover leave-allotment-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tenure</th>
                            <th>Leave Type</th>
                            <th>Category Count</th>
                            <th>Carry Forward</th>
                            <th>Assigned</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($rows && count($rows) > 0)
                            @php $sl = 1; @endphp
                            @foreach($rows as $row)
                                @php
                                    $encodedId = Helper::encoded($row->id);
                                    $statusUrl = $controllerRoute . '/change-status/';
                                    $assignUrl = $controllerRoute . '/assign/';
                                @endphp
                                <tr>
                                    <td>{{ $sl++ }}</td>
                                    <td>
                                        {{ optional($row->leave_tenure_from)->format('d-m-Y') }}
                                        <br>
                                        <span class="text-muted">to {{ optional($row->leave_tenure_to)->format('d-m-Y') }}</span>
                                    </td>
                                    <td>{{ $row->leaveType->name ?? '--' }}</td>
                                    <td>
                                        <div class="count-grid">
                                            <span class="count-chip">FD: {{ $formatCount($row->front_desk_leave_count) }}</span>
                                            <span class="count-chip">GD: {{ $formatCount($row->group_d_leave_count) }}</span>
                                            <span class="count-chip">TSA: Not applicable</span>
                                            <span class="count-chip">VHS: {{ $formatCount($row->vhs_teacher_leave_count) }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $row->is_carry_forward ? 'Yes' : 'No' }}</td>
                                    <td>{{ (int) $row->assigned_employee_count }}</td>
                                    <td>
                                        @if((int) $row->status === 1)
                                            <span class="status-pill status-active">Active</span>
                                        @else
                                            <span class="status-pill status-inactive">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url($controllerRoute . '/edit/' . Helper::encoded($row->id)) }}" class="leave-action text-primary" title="Edit Leave Allotment">Edit</a>
                                        @if((int) $row->status === 1)
                                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Are you sure you want to deactivate this leave allotment?')" class="leave-action text-success" title="Deactivate Leave Allotment">Active</a>
                                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $assignUrl ?>', 'Assign this leave allotment to employees now?')" class="leave-action assign-button" title="Assigned to employee">
                                                <i class="fa-solid fa-user-check"></i>
                                                <span>Assigned to employee</span>
                                            </a>
                                        @else
                                            <a href="javascript:void(0);" onclick="showConfirmBox('<?= $encodedId ?>', '<?= $statusUrl ?>', 'Are you sure you want to activate this leave allotment?')" class="leave-action text-warning" title="Activate Leave Allotment">Inactive</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
