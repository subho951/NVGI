@extends('front.layouts.afterlogin')
@section('content')
<?php
use App\Helpers\Helper;

$controllerRoute = $module['controller_route'];
$formatCount = function ($value) {
    $formatted = number_format((float) $value, 2, '.', '');

    return rtrim(rtrim($formatted, '0'), '.');
};
$statusClass = function ($status) {
    if ((int) $status === 1) {
        return 'approved';
    }

    if ((int) $status === 2) {
        return 'rejected';
    }

    return 'pending';
};
?>
<style>
    .leave-application-space {
        --ink: #173145;
        --muted: #63798d;
        --line: #dbe5ee;
        --brand: #245b68;
        --soft: #e9f4f6;
        --success: #146c43;
        --danger: #b02a37;
        --warning: #9a6a00;
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

    .leave-application-card .card-body {
        padding: 18px;
    }

    .leave-application-table thead th {
        background: var(--soft);
        color: var(--ink);
        font-size: .76rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .leave-application-table td {
        vertical-align: middle;
        font-size: .88rem;
    }

    .employee-code {
        color: var(--muted);
        font-size: .78rem;
        font-weight: 700;
    }

    .application-status {
        border-radius: 999px;
        display: inline-flex;
        font-size: .76rem;
        font-weight: 800;
        padding: 5px 10px;
        white-space: nowrap;
    }

    .application-status.pending {
        background: rgba(255, 193, 7, .14);
        color: var(--warning);
    }

    .application-status.approved {
        background: rgba(20, 108, 67, .12);
        color: var(--success);
    }

    .application-status.rejected {
        background: rgba(176, 42, 55, .12);
        color: var(--danger);
    }

    .leave-action {
        align-items: center;
        border-radius: 6px;
        display: inline-flex;
        height: 30px;
        justify-content: center;
        margin-right: 4px;
        text-decoration: none;
        width: 30px;
    }

    .leave-action.with-label {
        gap: 6px;
        min-width: 88px;
        padding: 0 10px;
        width: auto;
        font-size: .78rem;
        font-weight: 800;
    }

    .leave-action.edit {
        background: rgba(29, 95, 139, .1);
        color: #1d5f8b;
    }

    .leave-action.approve {
        background: rgba(20, 108, 67, .12);
        border: 0;
        color: var(--success);
    }

    .leave-action.reject {
        background: rgba(176, 42, 55, .12);
        border: 0;
        color: var(--danger);
    }

    .leave-action:hover {
        opacity: .86;
    }
</style>

<div class="leave-application-space">
    <div class="leave-application-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="leave-application-title">Leave Applications</h2>
                <p class="leave-application-subtitle">Review CL leave applications and approve or reject pending requests.</p>
            </div>
            @if($canCreateApplication)
                <a href="{{ url($controllerRoute . '/add') }}" class="btn btn-leave-primary px-3">
                    <i class="fa-solid fa-plus me-1"></i> Add Leave Application
                </a>
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

    <div class="card leave-application-card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="example" class="table table-bordered table-hover leave-application-table mb-0">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Employee</th>
                            <th>Category</th>
                            <th>Leave Type</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>No Of Days</th>
                            <th>Apply Date</th>
                            <th>Remarks</th>
                            <th>Status</th>
                            <th>Approved Date</th>
                            <th>Rejected Date & Reason</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($rows && count($rows) > 0)
                            @php $sl = 1; @endphp
                            @foreach($rows as $row)
                                @php
                                    $isPending = ((int) $row->application_status === 0);
                                    $statusText = $statusLabels[(int) $row->application_status] ?? 'Pending';
                                @endphp
                                <tr>
                                    <td>{{ $sl++ }}</td>
                                    <td>
                                        <div class="employee-code">{{ $row->employee_no ?: '--' }}</div>
                                        <div>{{ $row->employee_name ?: '--' }}</div>
                                    </td>
                                    <td>{{ $row->employee_category ?: '--' }}</td>
                                    <td>{{ $row->leave_type_name ?: '--' }}</td>
                                    <td>{{ $row->leave_from_date ? $row->leave_from_date->format('d-m-Y') : '--' }}</td>
                                    <td>{{ $row->leave_to_date ? $row->leave_to_date->format('d-m-Y') : '--' }}</td>
                                    <td>{{ $formatCount($row->no_of_days) }}</td>
                                    <td>{{ $row->apply_date ? $row->apply_date->format('d-m-Y') : '--' }}</td>
                                    <td>{{ $row->remarks ?: '--' }}</td>
                                    <td>
                                        <span class="application-status {{ $statusClass($row->application_status) }}">{{ $statusText }}</span>
                                    </td>
                                    <td>{{ $row->approved_at ? $row->approved_at->format('d-m-Y') : '--' }}</td>
                                    <td>
                                        @if($row->rejected_at)
                                            <div>{{ $row->rejected_at->format('d-m-Y') }}</div>
                                            <div class="text-muted">{{ $row->reject_reason ?: '--' }}</div>
                                        @else
                                            --
                                        @endif
                                    </td>
                                    <td>
                                        @if($isPending && $canCreateApplication)
                                            <a href="{{ url($controllerRoute . '/edit/' . Helper::encoded($row->id)) }}" class="leave-action edit" title="Edit Leave Application">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endif

                                        @if($isPending && $canApproveApplication)
                                            <form method="POST" action="{{ url($controllerRoute . '/approve/' . Helper::encoded($row->id)) }}" class="d-inline" onsubmit="return confirm('Approve this leave application?');">
                                                @csrf
                                                <button type="submit" class="leave-action approve with-label" title="Approve Leave Application">
                                                    <i class="fa-solid fa-check"></i>
                                                    <span>Approve</span>
                                                </button>
                                            </form>
                                            <button type="button"
                                                    class="leave-action reject with-label"
                                                    title="Reject Leave Application"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectLeaveApplicationModal"
                                                    data-action="{{ url($controllerRoute . '/reject/' . Helper::encoded($row->id)) }}"
                                                    data-employee="{{ $row->employee_no }} - {{ $row->employee_name }}">
                                                <i class="fa-solid fa-xmark"></i>
                                                <span>Reject</span>
                                            </button>
                                        @endif

                                        @if(! $isPending || (! $canCreateApplication && ! $canApproveApplication))
                                            <span class="text-muted">--</span>
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

@if($canApproveApplication)
    <div class="modal fade" id="rejectLeaveApplicationModal" tabindex="-1" aria-labelledby="rejectLeaveApplicationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="" id="rejectLeaveApplicationForm" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectLeaveApplicationModalLabel">Reject Leave Application</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <input type="text" id="rejectLeaveEmployee" class="form-control" readonly>
                    </div>
                    <div>
                        <label for="reject_reason" class="form-label">Reject Reason <span class="text-danger">*</span></label>
                        <textarea name="reject_reason" id="reject_reason" rows="4" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

@section('scripts')
@if($canApproveApplication)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rejectModal = document.getElementById('rejectLeaveApplicationModal');
        const rejectForm = document.getElementById('rejectLeaveApplicationForm');
        const employeeInput = document.getElementById('rejectLeaveEmployee');
        const reasonInput = document.getElementById('reject_reason');

        rejectModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            rejectForm.action = button.getAttribute('data-action');
            employeeInput.value = button.getAttribute('data-employee') || '';
            reasonInput.value = '';
        });
    });
</script>
@endif
@endsection
